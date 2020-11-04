<?php

use Restserver\Libraries\REST_Controller;
require(APPPATH.'/libraries/REST_Controller.php');

class getTiket extends REST_Controller{

	public function index_post(){
		$nopend 	= $this->post('nopend');
		$status 	= $this->post('status');
		$offset 	= $this->post('offset');
		$type 		= $this->post('typeReport');
		$this->_queryMasuk($nopend, $status, $type);

		$this->db->limit(15, $offset);
		$sql = $this->db->get();

		$this->response($sql->result_array(), 200);
	}

	private function _queryMasuk($nopend, $status, $type){
		$this->db->select('a.no_tiket,c.name AS channel_aduan ,b.name_requester as pelanggan, a.tgl_exp, now() as current');
		$this->db->select("GROUP_CONCAT(a.tujuan_pengaduan,' - ', (select name from office where code = a.tujuan_pengaduan)) as tujuan_pengaduan , d.name as status");
		$this->db->select("(select CONCAT(code ,' - ', name) from office where code = a.asal_pengaduan) as asal_pengaduan");
		$this->db->select('e.name AS statusRead, a.tgl_tambah, TIMESTAMPDIFF(HOUR, a.tgl_tambah, a.tgl_exp) as hours, a.awb');
		$this->db->select('f.tgl_selesai');
		$this->db->from('tiket a');
		$this->db->join('pelanggan b', 'a.cust_id = b.cust_id');
		$this->db->join('channel_info c', 'b.type_request = c.auto_id');
		$this->db->join('ticket_status d', 'a.status = d.id ');
		$this->db->join('statusRead e', 'a.status_baca = e.id');
		$this->db->join('ticket_selesai f', 'a.no_tiket = f.no_ticket', 'LEFT', null);
		
		if ($this->post('search')) {
			$search = $this->post('search');
			$this->db->where('a.no_tiket', $search);
			$this->db->or_where('a.awb', $search);
		}

		if ($type === 'activeKeluar' || $type === 'activeKeluarDone' || $type === 'activeClose') {
			if ($nopend == '40005') {
				$this->db->where_in('a.asal_pengaduan', array('00001', '00002','40005'));
			}else{
				$this->db->where('a.asal_pengaduan', $nopend);	
			}
		}else if($type === 'activeLastupdate'){
			$this->db->where('a.asal_pengaduan', $nopend);	
		}else if($type === 'activeLastupdateMasuk'){
			$this->db->where('a.tujuan_pengaduan', $nopend);	
		}else{
			$this->db->where('a.tujuan_pengaduan', $nopend);
		}
		
		$this->db->where_in('a.status', $status);
		$this->db->group_by(array('a.no_tiket','c.name','b.name_requester', 'a.asal_pengaduan' ,'d.name', 'e.name', 'a.tgl_tambah', 'a.tgl_exp', 'a.awb', 'a.lastupdate', 'f.tgl_selesai'));
		$this->db->order_by('a.lastupdate', 'DESC');
	}

	public function totalTiket_post(){
		$nopend = $this->post('nopend');
		$keluar = $this->getTotalKeluar($nopend); //output {"done": 12, "active": 24}
		$masuk 	= $this->getTotalMasuk($nopend);
		$close 	= $this->getRequestClose($nopend);
		$lastupdate = $this->getTotalLastupdate($nopend, 1);
		$lastupdateMasuk = $this->getTotalLastupdate($nopend, 2);

		$response = array(
			'done' => array(
				'masuk' => $masuk['done'], 
				'keluar' => $keluar['done']
			),
			'active' => array(
				'masuk' => $masuk['active'], 
				'keluar' => $keluar['active']
			),
			'close' => $close,
			'lastupdate' => $lastupdate,
			'lastupdateMasuk' => $lastupdateMasuk
		);


		$this->response($response, 200); 
	}

	private function getTotalKeluar($nopend){
		$this->db->select('count(DISTINCT no_tiket) as jumlah, status');
	 	$this->db->from('tiket');
	 	if ($nopend == '40005') {
	 		$this->db->where_in('asal_pengaduan', array('00001', '00002','40005'));
	 	}else{
			$this->db->where('asal_pengaduan', $nopend);
	 	}

		$this->db->group_by('status');
		$response 	= array();
		$query 		= $this->db->get();

		//data must not empty array
		if ($query->num_rows() > 0) {
			$data 		= $query->result_array();
			$totalActive 	= 0;
			$totalDone 	 	= 0;
			
			foreach($data as $key){
				if ($key['status'] === '99') {
					$totalDone += (int)$key['jumlah'];
				}else{
					$totalActive += (int)$key['jumlah'];
				}
			}

			$response = array(
				'done' => $totalDone,
				'active' => $totalActive
			);
		}else{
			$response = array(
				'done' => 0,
				'active' => 0
			);
		}

		return $response;

	}

	private function getTotalMasuk($nopend){
		$this->db->select('count(DISTINCT no_tiket) as jumlah, status');
		$this->db->from('tiket');
		$this->db->where('tujuan_pengaduan', $nopend);
		$this->db->where('status <> 18');
		$this->db->group_by('status');
		$response 	= array();
		$query 		= $this->db->get();

		//data must not empty array
		if ($query->num_rows() > 0) {
			$data 		= $query->result_array();
			$totalActive 	= 0;
			$totalDone 	 	= 0;
			
			foreach($data as $key){
				if ($key['status'] === '99') {
					$totalDone += (int)$key['jumlah'];
				}else{
					$totalActive += (int)$key['jumlah'];
				}
			}

			$response = array(
				'done' => $totalDone,
				'active' => $totalActive
			);
		}else{
			$response = array(
				'done' => 0,
				'active' => 0
			);
		}

		return $response;
	}

	private function getRequestClose($nopend){
		$this->db->select('count(DISTINCT no_tiket) as jumlah');
		$this->db->from('tiket');
		$this->db->where('status', '18');
		$this->db->where('asal_pengaduan', $nopend);
		$query = $this->db->get()->row_array();

		return (int)$query['jumlah'];
	}

	private function getTotalLastupdate($nopend, $type){
		$this->db->select('count(DISTINCT no_tiket) as jumlah');
		$this->db->from('tiket');
		if ($type == 1) {
			$this->db->where('asal_pengaduan', $nopend);	
			$this->db->where_in('status', array('12','18'));
		}else{
			$this->db->where('tujuan_pengaduan', $nopend);
			$this->db->where_in('status', array('17', '18'));
		}
		// $this->db->where("DATE_FORMAT(lastupdate, '%Y-%m-%d') = DATE_FORMAT(now(), '%Y-%m-%d')");
		$query = $this->db->get()->row_array();

		return (int)$query['jumlah'];
	}

}
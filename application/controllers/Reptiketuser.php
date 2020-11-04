<?php

use Restserver\Libraries\REST_Controller;
require(APPPATH.'/libraries/REST_Controller.php');

class reptiketuser extends REST_Controller{
	
	public function index_post(){
		$reg 		= $this->post('regional');
		$kprk 		= $this->post('kprk');
		$email 		= $this->post('email');
		$startdate 	= $this->post('startdate');
		$enddate 	= $this->post('enddate');

		if ($reg == '00') {
			$data = $this->_pusat($email, $startdate, $enddate);
		}else if($reg == '02'){
			$data = $this->_allreg($email, $startdate, $enddate);
		}else if($reg == '01'){
			$data = $this->_pusatOmni($kprk, $email, $startdate, $enddate);
		} else{
			if ($kprk == '00') {
				$data = $this->_regional($reg, $email, $startdate, $enddate);
			} else {
				$data = $this->_kprk($kprk, $email, $startdate, $enddate);
			}
		}

		$data = $this->db->get()->result_array();
		$this->response($data, 200);
	}

	private function _pusat($email, $startdate, $enddate){
		$this->db->SELECT("e.regional, a.title, a.email, CONCAT(a.kantor_pos,' - ', e.name) as kantor_pos");
		$this->db->select('COUNT(DISTINCT c.no_tiket) as jmlselesai, COUNT(DISTINCT d.no_tiket) as jmlterbuka');
		$this->db->FROM('sys_user a');
		$this->db->JOIN("(
			SELECT DISTINCT no_tiket, user_cch 
			FROM tiket 
			WHERE status = '99' AND date_format(tgl_tambah, '%Y-%m-%d') BETWEEN '".$startdate."' AND '".$enddate."'
		) c","a.email = c.user_cch", "LEFT", null);
		$this->db->JOIN("(
			SELECT DISTINCT no_tiket, user_cch 
			FROM tiket 
			WHERE status <> '99' AND date_format(tgl_tambah, '%Y-%m-%d') BETWEEN '".$startdate."' AND '".$enddate."'
		) d","a.email = d.user_cch", "LEFT", null);
		$this->db->JOIN('office e','a.kantor_pos = e.code', 'LEFT', null);

		if ($email == '00') {
			$this->db->WHERE(" a.role_id = '2'");
		} else {
			$this->db->WHERE(" a.role_id = '2'");
			$this->db->WHERE("a.email = '". $email ."'");
		}
		
		$this->db->GROUP_BY('e.regional, a.kantor_pos ,a.title , e.kd_wilayah, a.email, e.name');
		$this->db->ORDER_BY('e.kd_wilayah ');
	}

	private function _allreg($email, $startdate, $enddate){
		$this->db->SELECT("e.regional, a.title, a.email, CONCAT(a.kantor_pos,' - ', e.name) as kantor_pos");
		$this->db->select('COUNT(DISTINCT c.no_tiket) as jmlselesai, COUNT(DISTINCT d.no_tiket) as jmlterbuka');
		$this->db->FROM('sys_user a');
		$this->db->JOIN("(
			SELECT DISTINCT no_tiket, user_cch 
			FROM tiket 
			WHERE status = '99' AND date_format(tgl_tambah, '%Y-%m-%d') BETWEEN '".$startdate."' AND '".$enddate."'
		) c","a.email = c.user_cch", "LEFT", null);
		$this->db->JOIN("(
			SELECT DISTINCT no_tiket, user_cch 
			FROM tiket 
			WHERE status <> '99' AND date_format(tgl_tambah, '%Y-%m-%d') BETWEEN '".$startdate."' AND '".$enddate."'
		) d","a.email = d.user_cch", "LEFT", null);
		$this->db->JOIN('office e','a.kantor_pos = e.code', 'LEFT', null);

		if ($email == '00') {
			$this->db->WHERE(" a.role_id = '2'");
			$this->db->where("e.regional <> 'KANTORPUSAT'");
		} else {
			$this->db->WHERE(" a.role_id = '2'");
			$this->db->WHERE("a.email = '". $email ."'");
			$this->db->where("e.regional <> 'KANTORPUSAT'");
		}
		
		$this->db->GROUP_BY('e.regional, a.kantor_pos ,a.title , e.kd_wilayah, a.email, e.name');
		$this->db->ORDER_BY('e.kd_wilayah ');
	}
	
	private function _pusatOmni($kprk, $email, $startdate, $enddate){
		$this->db->SELECT("e.regional, a.title, a.email, CONCAT(a.kantor_pos,' - ', e.name) as kantor_pos");
		$this->db->select('COUNT(DISTINCT c.no_tiket) as jmlselesai, COUNT(DISTINCT d.no_tiket) as jmlterbuka');
		$this->db->FROM('sys_user a');
		$this->db->JOIN("(
			SELECT DISTINCT no_tiket, user_cch 
			FROM tiket 
			WHERE status = '99' AND date_format(tgl_tambah, '%Y-%m-%d') BETWEEN '".$startdate."' AND '".$enddate."'
		) c","a.email = c.user_cch", "LEFT", null);
		$this->db->JOIN("(
			SELECT DISTINCT no_tiket, user_cch 
			FROM tiket 
			WHERE status <> '99' AND date_format(tgl_tambah, '%Y-%m-%d') BETWEEN '".$startdate."' AND '".$enddate."'
		) d","a.email = d.user_cch", "LEFT", null);
		$this->db->JOIN('office e','a.kantor_pos = e.code', 'LEFT', null);

		if ($email == '00') {
			$this->db->WHERE(" a.role_id = '2'");
		} else {
			$this->db->WHERE(" a.role_id = '2'");
			$this->db->WHERE("a.email = '". $email ."'");
		}

		if ($kprk == '00') {
			$this->db->where_in('a.kantor_pos', array('00001', '00002','40005'));
		}else{
			$this->db->where('a.kantor_pos', $kprk);
		}
		
		$this->db->GROUP_BY('e.regional, a.kantor_pos ,a.title , e.kd_wilayah, a.email, e.name');
		$this->db->ORDER_BY('e.kd_wilayah ');
	}

	private function _regional($reg, $email, $startdate, $enddate){
		$this->db->SELECT("e.regional, a.title, a.email, CONCAT(a.kantor_pos,' - ', e.name) as kantor_pos");
		$this->db->select('COUNT(DISTINCT c.no_tiket) as jmlselesai, COUNT(DISTINCT d.no_tiket) as jmlterbuka');
		$this->db->FROM('sys_user a');
		$this->db->JOIN("(
			SELECT DISTINCT no_tiket, user_cch 
			FROM tiket 
			WHERE status = '99' AND date_format(tgl_tambah, '%Y-%m-%d') BETWEEN '".$startdate."' AND '".$enddate."'
		) c","a.email = c.user_cch", "LEFT", null);
		$this->db->JOIN("(
			SELECT DISTINCT no_tiket, user_cch 
			FROM tiket 
			WHERE status <> '99' AND date_format(tgl_tambah, '%Y-%m-%d') BETWEEN '".$startdate."' AND '".$enddate."'
		) d","a.email = d.user_cch", "LEFT", null);
		$this->db->JOIN('office e','a.kantor_pos = e.code', 'LEFT', null);
		$this->db->WHERE("a.role_id = '2'");

		if ($email == '00') {
			$this->db->WHERE("e.regional = '". $reg."'");
		} else {
			$this->db->WHERE("a.email = '". $email ."'");
		}

		$this->db->GROUP_BY('e.regional, a.kantor_pos ,a.title , e.kd_wilayah, a.email, e.name');
		$this->db->ORDER_BY('e.kd_wilayah ');
	}

	private function _kprk($kprk, $email, $startdate, $enddate){
		$this->db->SELECT("e.regional, a.title, a.email, CONCAT(a.kantor_pos,' - ', e.name) as kantor_pos");
		$this->db->select('COUNT(DISTINCT c.no_tiket) as jmlselesai, COUNT(DISTINCT d.no_tiket) as jmlterbuka');
		$this->db->FROM('sys_user a');
		$this->db->JOIN("(
			SELECT DISTINCT no_tiket, user_cch 
			FROM tiket 
			WHERE status = '99' AND date_format(tgl_tambah, '%Y-%m-%d') BETWEEN '".$startdate."' AND '".$enddate."'
		) c","a.email = c.user_cch", "LEFT", null);
		$this->db->JOIN("(
			SELECT DISTINCT no_tiket, user_cch 
			FROM tiket 
			WHERE status <> '99' AND date_format(tgl_tambah, '%Y-%m-%d') BETWEEN '".$startdate."' AND '".$enddate."'
		) d","a.email = d.user_cch", "LEFT", null);
		$this->db->JOIN('office e','a.kantor_pos = e.code', 'LEFT', null);
		$this->db->WHERE("a.role_id = '2'");

		if ($email == '00') {
			$this->db->WHERE("a.kantor_pos = '". $kprk."'");
		} else {
			$this->db->WHERE("a.email = '". $email."'");
		}
		
		$this->db->GROUP_BY('e.regional, a.kantor_pos ,a.title , e.kd_wilayah, a.email, e.name');
		$this->db->ORDER_BY('e.kd_wilayah ');
		// $query = $this->db->get()->result_array();
		// return $query;
	}

	public function detail_post(){
		$email = $this->post('email');
		$startdate = $this->post('startdate');
		$enddate = $this->post('enddate');
		
		$this->db->select('a.no_tiket , a.awb, a.jenis_layanan, (SELECT name FROM office WHERE code = a.asal_pengaduan) as asal_pengaduan');
		$this->db->select('(SELECT name FROM office WHERE code = a.tujuan_pengaduan) as tujuan_pengaduan');
		$this->db->select('e.nama_aduan,b.name as channel,a.tgl_tambah , a.tgl_exp ,c.name as status ');
		$this->db->select("IFNULL(d.tgl_selesai, now()) as tgl_done");
		$this->db->from('tiket a');
		$this->db->join('channel_info b ','a.channel_aduan = b.id', 'LEFT', null);
		$this->db->join('ticket_status c ','a.status = c.id', 'LEFT', null);
		$this->db->join("ticket_selesai d", "a.no_tiket= d.no_ticket", "LEFT", null);
		$this->db->join("ref_aduan e", "a.jenis_aduan= e.id", "LEFT", null);
		$this->db->where("a.user_cch = '". $email ."'");
		$this->db->where("DATE_FORMAT(a.tgl_tambah, '%Y-%m-%d') between '". $startdate ."' and '".  $enddate ."'");
		$query = $this->db->get();
		
		$data = $this->group_assoc($query->result_array());
		$this->response($data, 200);
	}

	private function group_assoc($tiket_list) {
		$new_tiket_list = array();
		for ($first_key = 0; $first_key < count($tiket_list); $first_key++) {
		    $tiket = $tiket_list[$first_key];
		    $current_notiket 	= $tiket['no_tiket']; // string
		    $current_tujuan 	= $tiket['tujuan_pengaduan']; // string

		    // looping untuk cek apakah ada notiket yang sama di $new_tiket_list
		    $same_tiket_found = 0;
		    for ($second_key = 0; $second_key < count($new_tiket_list); $second_key++) {
		        $new_tiket = $new_tiket_list[$second_key];
		        $new_notiket = $new_tiket['no_tiket']; // string
		        $new_tujuan = $new_tiket['tujuan_pengaduan']; // array

		        if ($new_notiket == $current_notiket) {
		            $same_tiket_found = 1;
		            array_push($new_tiket_list[$second_key]['tujuan_pengaduan'], $current_tujuan); // assign ke yang sudah ada
		        }
		    }

		    if ($same_tiket_found == 0) {
		        $new_tiket = [
		            'no_tiket' => $current_notiket,
		            'awb' => $tiket['awb'],
		            'asal_pengaduan' => $tiket['asal_pengaduan'],
		            'tujuan_pengaduan' => [$current_tujuan],
					'jenis_layanan' => $tiket['jenis_layanan'], 
					'jenis_aduan' => $tiket['nama_aduan'],
		            'channel' => $tiket['channel'],
		            'tgl_tambah' => $tiket['tgl_tambah'],
		            'tgl_done' => $tiket['tgl_done'],
		            'tgl_exp' => $tiket['tgl_exp'],
		            'status' => $tiket['status']
		        ];

		        array_push($new_tiket_list, $new_tiket);
		    }
		}

	    return $new_tiket_list;

	}

}
<?php

use Restserver\Libraries\REST_Controller;
require(APPPATH.'/libraries/REST_Controller.php');

class detailreport extends REST_Controller{
	
	public function index_post(){
		$reportType = $this->post('reportType');
		$type 		= $this->post('type');
		$office		= $this->post('kantor');
		$startdate	= $this->post('startdate');
		$enddate	= $this->post('enddate');

		$new_tiket_list = array();
		$data 			= array();
		$respon;

		if ($reportType == '00') {//tiket masuk
			if ($type == 'reg') {
				$respon = $this->_byregional_masuk($office, $startdate, $enddate);
				} else {
					$respon = $this->_kprk_masuk($office, $startdate, $enddate);
				}
		} else {//tiket keluar
			if ($type == 'reg') {
			$respon = $this->_byregional($office, $startdate, $enddate);
			} else {
				$respon = $this->_kprk($office, $startdate, $enddate);
			}
		}

		for ($first_key = 0; $first_key < count($respon); $first_key++) {
		    $tiket = $respon[$first_key];
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
		            'channel' => $tiket['channel'],
		            'asal_pengaduan' => $tiket['asal_pengaduan'],
		            'tujuan_pengaduan' => [$current_tujuan],
		            'status' => $tiket['status'],
		            'awb' => $tiket['awb'],
		            'waktu_selesai' => $tiket['waktu_selesai'],
		            'orderby' => $tiket['orderby'],
		            'tgl_exp' => $tiket['tgl_exp'],
		            'tgl_selesai' => $tiket['tgl_done'],
		            'tgl_tambah' => $tiket['tgl_tambah']
		        ];

		        array_push($new_tiket_list, $new_tiket);
		    }
		}

		$this->response($new_tiket_list, 200);
	}

	private function _byregional($office, $startdate, $enddate){
		$this->db->select("t.no_tiket, d.name as channel, t.awb, e.name as status, t.tgl_tambah, t.tgl_exp");
		$this->db->select("IFNULL(f.tgl_selesai, now()) as tgl_done");
		$this->db->select("IFNULL(timestampdiff(DAY, t.tgl_exp, f.tgl_selesai),'-') as waktu_selesai");
		$this->db->select("IFNULL(timestampdiff(DAY, t.tgl_exp, f.tgl_selesai),0) as orderby");
		$this->db->select("CONCAT(t.asal_pengaduan,' - ', b.name) as asal_pengaduan");
		$this->db->select("(select CONCAT(code, ' - ', name) from office where code = t.tujuan_pengaduan) as tujuan_pengaduan");

		$this->db->from('tiket t');
		$this->db->join('office b','t.asal_pengaduan = b.code');
		$this->db->join('ref_wilayah c', 'c.id = b.kd_wilayah');
		$this->db->join('channel_info d', 't.channel_aduan = d.id');
		$this->db->join('ticket_status e', 'e.id = t.status');
		$this->db->join('ticket_selesai f', 't.no_tiket= f.no_ticket', 'LEFT', null);
		$this->db->where('c.wilayah', $office);
		$this->db->where("DATE_FORMAT(t.tgl_tambah, '%Y-%m-%d') >=", $startdate);
		$this->db->where("DATE_FORMAT(t.tgl_tambah, '%Y-%m-%d') <=", $enddate);
		$this->db->order_by('orderby', 'ASC');
		$this->db->order_by('t.status', 'ASC');
		
		$query = $this->db->get()->result_array();
		return $query;		
	}

	private function _kprk($office, $startdate, $enddate){
		// $this->db->select("a.no_tiket, a.channel, a.name_requester , a.regional, CONCAT(a.asal_pengaduan,' - ',c.name) as asal_pengaduan , a.tujuan_pengaduan , a.status , a.awb, IFNULL(timestampdiff(DAY, a.tgl_exp, b.tgl_selesai),'-') as waktu_selesai, IFNULL(timestampdiff(DAY, a.tgl_exp, b.tgl_selesai),0) as orderby");
		// $this->db->select('now() as curdate, a.tgl_exp, IFNULL(b.tgl_selesai, now()) as tgl_done, a.tgl_tambah');
		// $this->db->from('v_pengaduan_detail_keluar a');
		// $this->db->join('ticket_selesai b', 'a.no_tiket = b.no_ticket ','LEFT', null);
		// $this->db->join('office c', 'a.asal_pengaduan = c.code ','LEFT', null);
		// $this->db->where("a.asal_pengaduan = '". $office."' AND a.periode between '". $startdate ."' and '". $enddate."'");
		// $this->db->order_by('orderby', 'ASC');
		// $this->db->order_by('a.status', 'ASC');
		// $query = $this->db->get()->result_array();
		// return $query;		

		$this->db->select("t.no_tiket, d.name as channel, t.awb, e.name as status, t.tgl_tambah, t.tgl_exp");
		$this->db->select("IFNULL(f.tgl_selesai, now()) as tgl_done");
		$this->db->select("IFNULL(timestampdiff(DAY, t.tgl_exp, f.tgl_selesai),'-') as waktu_selesai");
		$this->db->select("IFNULL(timestampdiff(DAY, t.tgl_exp, f.tgl_selesai),0) as orderby");
		$this->db->select("CONCAT(t.asal_pengaduan,' - ', b.name) as asal_pengaduan");
		$this->db->select("(select CONCAT(code, ' - ', name) from office where code = t.tujuan_pengaduan) as tujuan_pengaduan");

		$this->db->from('tiket t');
		$this->db->join('office b','t.asal_pengaduan = b.code');
		$this->db->join('ref_wilayah c', 'c.id = b.kd_wilayah');
		$this->db->join('channel_info d', 't.channel_aduan = d.id');
		$this->db->join('ticket_status e', 'e.id = t.status');
		$this->db->join('ticket_selesai f', 't.no_tiket= f.no_ticket', 'LEFT', null);
		$this->db->where('t.asal_pengaduan', $office);
		$this->db->where("DATE_FORMAT(t.tgl_tambah, '%Y-%m-%d') >=", $startdate);
		$this->db->where("DATE_FORMAT(t.tgl_tambah, '%Y-%m-%d') <=", $enddate);
		$this->db->order_by('orderby', 'ASC');
		$this->db->order_by('t.status', 'ASC');
		
		$query = $this->db->get()->result_array();
		return $query;		
	}

	private function _byregional_masuk($office, $startdate, $enddate){
		$this->db->select("t.no_tiket, d.name as channel, t.awb, e.name as status, t.tgl_tambah, t.tgl_exp");
		$this->db->select("IFNULL(f.tgl_selesai, now()) as tgl_done");
		$this->db->select("IFNULL(timestampdiff(DAY, t.tgl_exp, f.tgl_selesai),'-') as waktu_selesai");
		$this->db->select("IFNULL(timestampdiff(DAY, t.tgl_exp, f.tgl_selesai),0) as orderby");
		$this->db->select("CONCAT(t.tujuan_pengaduan,' - ', b.name) as tujuan_pengaduan");
		$this->db->select("(select CONCAT(code, ' - ', name) from office where code = t.asal_pengaduan) as asal_pengaduan");

		$this->db->from('tiket t');
		$this->db->join('office b','t.tujuan_pengaduan = b.code');
		$this->db->join('ref_wilayah c', 'c.id = b.kd_wilayah');
		$this->db->join('channel_info d', 't.channel_aduan = d.id');
		$this->db->join('ticket_status e', 'e.id = t.status');
		$this->db->join('ticket_selesai f', 't.no_tiket= f.no_ticket', 'LEFT', null);
		$this->db->where('c.wilayah', $office);
		$this->db->where("DATE_FORMAT(t.tgl_tambah, '%Y-%m-%d') >=", $startdate);
		$this->db->where("DATE_FORMAT(t.tgl_tambah, '%Y-%m-%d') <=", $enddate);
		$this->db->order_by('orderby', 'ASC');
		$this->db->order_by('t.status', 'ASC');
		
		$query = $this->db->get()->result_array();
		return $query;		
	}

	private function _kprk_masuk($office, $startdate, $enddate){
		$this->db->select("t.no_tiket, d.name as channel, t.awb, e.name as status, t.tgl_tambah, t.tgl_exp");
		$this->db->select("IFNULL(f.tgl_selesai, now()) as tgl_done");
		$this->db->select("IFNULL(timestampdiff(DAY, t.tgl_exp, f.tgl_selesai),'-') as waktu_selesai");
		$this->db->select("IFNULL(timestampdiff(DAY, t.tgl_exp, f.tgl_selesai),0) as orderby");
		$this->db->select("CONCAT(t.tujuan_pengaduan,' - ', b.name) as tujuan_pengaduan");
		$this->db->select("(select CONCAT(code, ' - ', name) from office where code = t.asal_pengaduan) as asal_pengaduan");

		$this->db->from('tiket t');
		$this->db->join('office b','t.tujuan_pengaduan = b.code');
		$this->db->join('ref_wilayah c', 'c.id = b.kd_wilayah');
		$this->db->join('channel_info d', 't.channel_aduan = d.id');
		$this->db->join('ticket_status e', 'e.id = t.status');
		$this->db->join('ticket_selesai f', 't.no_tiket= f.no_ticket', 'LEFT', null);
		$this->db->where('t.tujuan_pengaduan', $office);
		// 
		$this->db->where("DATE_FORMAT(t.tgl_tambah, '%Y-%m-%d') >=", $startdate);
		$this->db->where("DATE_FORMAT(t.tgl_tambah, '%Y-%m-%d') <=", $enddate);
		$this->db->order_by('orderby', 'ASC');
		$this->db->order_by('t.status', 'ASC');
		
		$query = $this->db->get()->result_array();
		return $query;		
	}
		
}
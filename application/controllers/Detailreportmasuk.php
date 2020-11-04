<?php

use Restserver\Libraries\REST_Controller;
require(APPPATH.'/libraries/REST_Controller.php');

class detailreportkeluar extends REST_Controller{
	
	public function index_post(){
		$type 		= $this->post('type');
		$office		= $this->post('office');
		$periode	= $this->post('periode');

		if ($type == null) {
			$respon = $this->semua_pengaduan_keluar($office, $periode);
			$this->response($respon, 200);
		} else if ($type == '1') {
			$respon = $this->keluar_satu_hari($office, $periode);
			$this->response($respon, 200);
		} else if ($type == '2') {
			$respon = $this->keluar_dua_hari($office, $periode);
			$this->response($respon, 200);
		} else if ($type == '3') {
			$respon = $this->keluar_tiga_hari($office, $periode);
			$this->response($respon, 200);
		} else {
			$respon = $this->keluar_empat_hari($office, $periode);
			$this->response($respon, 200);
		}
		
		
	}

	private function semua_pengaduan_keluar($office, $periode){
		$this->db->distinct();
		$this->db->select('no_tiket, channel, name_requester , asal_pengaduan , tujuan_pengaduan , status , awb');
		$this->db->from('v_pengaduan_detail');
		$this->db->where("regional = '". $office."' AND periode = '". $periode."'");
		$query = $this->db->get()->result_array();
		return $query;		
	}

	private function keluar_satu_hari($office, $periode){
		$this->db->distinct();
		$this->db->select('a.no_tiket, a.channel, a.name_requester, a.asal_pengaduan , a.tujuan_pengaduan , a.status , a.awb');
		$this->db->from('v_pengaduan_detail a');
		$this->db->join('v_rekap_detail b', 'a.no_tiket = b.no_tiket ');
		$this->db->where('b.day_in <= 1');
		$this->db->where("a.regional = '". $office."'");
		$this->db->where("b.periode = '". $periode."'");
		$query = $this->db->get()->result_array();
		return $query;		
	}

	private function keluar_dua_hari($office, $periode){
		$this->db->distinct();
		$this->db->select('a.no_tiket, a.channel, a.name_requester, a.asal_pengaduan , a.tujuan_pengaduan , a.status , a.awb');
		$this->db->from('v_pengaduan_detail a');
		$this->db->join('v_rekap_detail b', 'a.no_tiket = b.no_tiket ');
		$this->db->where('b.day_in > 1 AND b.day_in <= 2');
		$this->db->where("a.regional = '". $office."'");
		$this->db->where("b.periode = '". $periode."'");
		$query = $this->db->get()->result_array();
		return $query;		
	}

	private function keluar_tiga_hari($office, $periode){
		$this->db->distinct();
		$this->db->select('a.no_tiket, a.channel, a.name_requester, a.asal_pengaduan , a.tujuan_pengaduan , a.status , a.awb');
		$this->db->from('v_pengaduan_detail a');
		$this->db->join('v_rekap_detail b', 'a.no_tiket = b.no_tiket ');
		$this->db->where('b.day_in > 2 AND b.day_in <= 3');
		$this->db->where("a.regional = '". $office."'");
		$this->db->where("b.periode = '". $periode."'");
		$query = $this->db->get()->result_array();
		return $query;		
	}

	private function keluar_empat_hari($office, $periode){
		$this->db->distinct();
		$this->db->select('a.no_tiket, a.channel, a.name_requester, a.asal_pengaduan , a.tujuan_pengaduan , a.status , a.awb');
		$this->db->from('v_pengaduan_detail a');
		$this->db->join('v_rekap_detail b', 'a.no_tiket = b.no_tiket ');
		$this->db->where('b.day_in > 4');
		$this->db->where("a.regional = '". $office."'");
		$this->db->where("b.periode = '". $periode."'");
		$query = $this->db->get()->result_array();
		return $query;		
	}


}
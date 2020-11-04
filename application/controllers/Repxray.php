<?php

use Restserver\Libraries\REST_Controller;
require(APPPATH.'/libraries/REST_Controller.php');

class repxray extends REST_Controller{
	public function index_post(){
		$type 	  = $this->post('type');
		$startdate= $this->post('startdate');
		$enddate  = $this->post('enddate');
		$reg 	  = $this->post('regional');	
		if ($type == '1') {
			$data = $this->kantor_asal($startdate, $enddate, $reg);
			$this->response($data, 200);	
		} elseif ($type == '2') {
			$data = $this->kantor_tujuan($startdate, $enddate, $reg);
			$this->response($data, 200);	
		} else {
			$this->response(array('ERR' => 'Tipe/Data tidak tersedia'), 400);	
		}
	}

	private function kantor_asal($startdate, $enddate, $reg){
		$this->db->select('a.regional , COUNT(b.kode_kantor_asal) as total_xray');
		$this->db->from('office a');
		$this->db->join("(SELECT kode_kantor_asal FROM t_xray WHERE DATE_FORMAT(tgl_input,'%Y-%m-%d') BETWEEN '". $startdate ."' AND  '". $enddate ."') as b",
			'a.code = b.kode_kantor_asal', 'LEFT', null);
		if ($reg == 'KANTORPUSAT') {
			
		} else {
			$this->db->where('a.regional', $reg);
		}
		
		$this->db->group_by('a.kd_wilayah ,a.regional');
		$this->db->order_by('a.kd_wilayah', 'ASC');
		$query = $this->db->get()->result_array();
		return $query;
	}

	private function kantor_tujuan($startdate, $enddate, $reg){
		$this->db->select('a.regional , COUNT(b.kode_kantor_tujuan) as total_xray');
		$this->db->from('office a');
		$this->db->join("(SELECT kode_kantor_tujuan FROM t_xray WHERE DATE_FORMAT(tgl_input,'%Y-%m-%d') BETWEEN '". $startdate ."' AND  '". $enddate ."') as b",
			'a.code = b.kode_kantor_tujuan', 'LEFT', null);
			if ($reg == 'KANTORPUSAT') {
			
			} else {
				$this->db->where('a.regional', $reg);
			}
		$this->db->group_by('a.kd_wilayah ,a.regional');
		$this->db->order_by('a.kd_wilayah', 'ASC');
		$query = $this->db->get()->result_array();
		return $query;
	}

	public function detail_post(){
		$type 	  = $this->post('type');
		$reg  	  = $this->post('reg');
		$startdate= $this->post('startdate');
		$enddate  = $this->post('enddate');
		if ($type == '1') {
			$data = $this->detail_asal($reg, $startdate, $enddate);
			$this->response($data, 200);	
		} elseif ($type == '2') {
			$data = $this->detail_tujuan($reg, $startdate, $enddate);
			$this->response($data, 200);	
		} else {
			$this->response(array('ERR' => 'Tipe/Data tidak tersedia'), 400);	
		}
	}

	private function detail_asal($reg, $startdate, $enddate){
		$this->db->select('b.kd_wilayah,b.regional , a.id_kiriman , a.kode_kantor_asal , a.kode_kantor_tujuan ');
		$this->db->select('a.isi_kiriman, a.berat ,a.kantong_lama , a.kantong_baru , a.keterangan ');
		$this->db->FROM('t_xray a');
		$this->db->join('office b', 'a.kode_kantor_asal = b.code', 'LEFT', null);
		$this->db->where('b.regional', $reg);
		$this->db->where("DATE_FORMAT(tgl_input,'%Y-%m-%d') BETWEEN '". $startdate ."' AND  '". $enddate ."'");
		$this->db->order_by('b.kd_wilayah', 'ASC');
		$query = $this->db->get()->result_array();
		return $query;	
	}

	private function detail_tujuan($reg, $startdate, $enddate){
		$this->db->select('b.kd_wilayah,b.regional , a.id_kiriman , a.kode_kantor_asal , a.kode_kantor_tujuan ');
		$this->db->select('a.isi_kiriman, a.berat ,a.kantong_lama , a.kantong_baru , a.keterangan ');
		$this->db->FROM('t_xray a');
		$this->db->join('office b', 'a.kode_kantor_tujuan = b.code', 'LEFT', null);
		$this->db->where('b.regional', $reg);
		$this->db->where("DATE_FORMAT(tgl_input,'%Y-%m-%d') BETWEEN '". $startdate ."' AND  '". $enddate ."'");
		$this->db->order_by('b.kd_wilayah', 'ASC');
		$query = $this->db->get()->result_array();
		return $query;	
	}
}
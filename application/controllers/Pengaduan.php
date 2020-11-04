<?php

use Restserver\Libraries\REST_Controller;
require(APPPATH.'/libraries/REST_Controller.php');

class pengaduan extends REST_Controller{
	
	public function index_post(){
		$custid = $this->post('custid');
		$jenis  = $this->post('jenisChannel');
		$desk   = $this->post('deskripsi');

		$data 	= array(
			'cust_id' => $custid,
			'jenis_pengaduan' => $jenis,
			'deskripsi' => $desk
		);

		$this->db->insert('informasi_data', $data);
		if ($this->db->affected_rows() > 0) {
			$this->response('SUKSES TAMBAH DATA', 200);
		} else {
			$this->response('GAGAL TAMBAH DATA', 400);
		}
	}



}
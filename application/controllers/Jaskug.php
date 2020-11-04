<?php

use Restserver\Libraries\REST_Controller;
require(APPPATH.'/libraries/REST_Controller.php');

class jaskug extends REST_Controller{
	public function index_get(){
		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, 'http://10.33.41.116/index.php?param=hasilresi');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, "KantorAsalResi=60186A0&Loket=02&TahunResi=20&NomorUrutResi=000989");

		$headers = array();
		$headers[] = 'Content-Type: application/x-www-form-urlencoded';
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

		$result = curl_exec($ch);
		$data	= explode("<tr><td><img src='img/hasilresi.gif'></td></tr><tr><td class=putih valign='top'>", $result);
		$data2	= explode("<script src='java/valid.js' type='text/javascript'></script>", $data[1]);
		$data3  = explode('<br>', $data2[0]) ;

		$tgl_setor 	= explode(' :', $data3[1]);
		$no_resi	= explode('<b>', $data3[2]);
		$no_resi2	= explode('</b>', $no_resi[1]);
		$jenis		= explode('<b>', $data3[4]);
		$jenis2		= explode('</b>', $jenis[1]);
		$bsu 		= explode(' :', $data3[5]);
		$kantor_setor 	= explode(' :', $data3[6]);
		$kantor_bayar 	= explode(' :', $data3[7]);
		$status		= explode('<b>', $data3[8]);
		$status2		= explode('</b>', $status[1]);

		$realdata = array(
			'tgl_setor' => trim($tgl_setor[1]),
			'no_resi' => trim($no_resi2[0]),
			'jenis' => trim($jenis2[0]),
			'bsu' => trim($bsu[1]),
			'kantor_setor' => trim($kantor_setor[1]),
			'kantor_bayar' => trim($kantor_bayar[1]),
			'status' => trim($status2[0]),
			'nama_pengirim' => trim($data3[11]),
			'alamat_pengirim' => trim($data3[12]),
			'nama_penerima' => trim($data3[15]),
			'alamat_penerima' => trim($data3[16]),
			);
		$json = json_encode($realdata, FALSE);
		print_r($json);
	}

}
<?php

use Restserver\Libraries\REST_Controller;
require(APPPATH.'/libraries/REST_Controller.php');

/**
 * 
 */
class address extends REST_Controller{
	
	public function index_post(){
        $kota    = $this->post('kodepos');

        $sql        = "SELECT'-' as kelurahan, KEC as kecamatan , kota as kabupaten , 
						PROPINSI as provinsi, MAX(KODEPOS_5) as  kodepos from KODEPOS_KPC
						WHERE (KODEPOS_5 = '". $kota ."')OR (KEC LIKE '%". $kota ."%') 
						OR (KOTA LIKE '%". $kota ."%') OR (PROPINSI LIKE '%". $kota ."%')
						GROUP BY kec, kota , PROPINSI";
        $query      = $this->db->query($sql, array($kota));
        if($query->num_rows() > 0){
            $list = $query->result_array();
            $this->response(array("result" => $list), 200);
        }else{
            $this->response(array('errors'=> array('global' => 'tidak ditemukan')), 400);
        }
    }
}
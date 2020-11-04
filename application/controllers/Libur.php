<?php


use Restserver\Libraries\REST_Controller;
require(APPPATH.'/libraries/REST_Controller.php');

class libur extends REST_Controller{
    public function index_post(){
        $tgl    = $this->post('periode');
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, "http://10.32.41.95/simsdm/public/referensi_sdm/libnas?tgl=".$tgl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $result = curl_exec($ch);
        $result = json_decode($result, TRUE);
        $final  = $result['data'];
        // print_r($final);

        if (count($final) > 0) {
            foreach ($final as $key) {
                $data[] = array(
                    'date_start' => $key['tgl'],
                    'description' => $key['keterangan'],
                    'username' => 'administrator',
                    'office' => $key['nopend']
                );
            }
            $insert = $data;
            $this->db->insert_batch('holiday', $insert);
            if ($this->db->affected_rows() > 0) {
                $this->response(array('status' => 200, 'result' => 'SUKSES GENERATE HARI LIBUR' ), 200);
            }else{
                $this->response(array('errors' => array('global' => 'GAGAL GENERATE HARI LIBUR')), 400);
            }
            // $this->response($data,200);
        }else{
            $this->response(array('status' => 'false', 'keterangan' => 'TIDAK ADA HARI LIBUR' ),200);
        }
    }
}

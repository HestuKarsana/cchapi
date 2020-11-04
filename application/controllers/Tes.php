<?php

use Restserver\Libraries\REST_Controller;
require(APPPATH.'/libraries/REST_Controller.php');

class tes extends REST_Controller{

    public function index_post(){
        $secretKey  = "5bd6ab2e-b5e0-39e8-99f9-e879763abe86";
        $url        = 'https://api.posindonesia.co.id:8245/utility/1.0.0/getFeeLn';


        $customerid = $this->post('customerid');
        $desttypeid = $this->post('desttypeid');
        $itemtypeid = $this->post('itemtypeid');
        $shipperzipcode = $this->post('shipperzipcode');
        $receiverzipcode = $this->post('receiverzipcode');
        $weight = $this->post('weight');
        $length = $this->post('length');
        $width = $this->post('width');
        $height = $this->post('height');
        $diameter = $this->post('diameter');
        $valuegoods = $this->post('valuegoods');

        $postData   = array(
            'customerid' => $customerid,
            'desttypeid' => $desttypeid,
            'itemtypeid' => $itemtypeid,
            'shipperzipcode' => $shipperzipcode,
            'receiverzipcode' => $receiverzipcode,
            'weight' => $weight,
            'length' => $length,
            'width' => $width,
            'height' => $height,
            'diameter' => $diameter,
            'valuegoods' => $valuegoods
        );

        $string     = json_encode($postData);

        $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST"); 
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $string);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
             'Authorization: Bearer '.$secretKey,
             'Content-Type: application/json',
             'Accept : application/json',
             'X-POS-USER : b2b_pusat',
             'X-POS-PASSWORD : b2b?03$4+x4mpr3T'
        ));

        $responsenya    = json_decode(curl_exec($ch), TRUE);
        $data           = $responsenya['response'];
        if (empty($data)) {
            $res = array(
                'errors' => array('global' => "TARIF TIDAK DITEMUKAN")
            );  
            $this->response($res, 400);
        }else{
            if (empty($data['kodepos'])){ //data banyak
                $res = array(
                    'result' => $data
                );      
            }else{
                $res = array(
                    'result' => [$data]
                );      
            }      
            $this->response($res, 200);
        }
    }
}


<?php
use Restserver\Libraries\REST_Controller;
require(APPPATH.'/libraries/REST_Controller.php');

class Download extends REST_Controller{
	public function pelanggan_post(){
		$kprk       = $this->post('kprk');
        $regional   = $this->post('regional');
        $channel    = $this->post('channel');

        $this->db->select('a.kantorDaftar as nopend , b.regional ,b.fullname as kantorPos, a.cust_id as customerId, a.id_ktp as idktp, a.type_request');
        $this->db->select('a.name_requester as namaLengkap, a.address as alamat, a.phone');
        $this->db->select('a.email , a.sosmed , c.name as JenisSosmed, a.detail_address');
        $this->db->from('pelanggan a');
        $this->db->join('office b', 'a.kantorDaftar = b.code', 'LEFT', NULL);
        $this->db->join('channel_info c', 'a.type_request = c.auto_id', 'LEFT', NULL);

        if ($regional != '00') {
            if($regional == '01'){ //pusat
                if ($kprk == '00') { // all office pusat
                    $this->db->where_in('a.kantorDaftar', array('00002','40005','00001'));
                }else{
                    $this->db->where('a.kantorDaftar', $kprk);
                }
            }else{
                if ($kprk == '00') { // current regional
                    $this->db->where('b.regional', $regional);
                }else{
                    $this->db->where('a.kantorDaftar', $kprk);
                }
            }
        }

        if ($channel != '00') $this->db->where('c.id', $channel);

        $this->db->order_by('a.created_date', 'DESC');

        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            $this->response($query->result_array(), 200);
        }else{
            $this->response(array("errors" => 'GAGAL AMBIL DATA'), 400);
        }
	}
}

?>
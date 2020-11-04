<?php

use Restserver\Libraries\REST_Controller;
require(APPPATH.'/libraries/REST_Controller.php');

class service extends REST_Controller{
//TIKET function
    public function addTicket_post(){
        $this->load->helper('string');
        $nama           = $this->post('requestName');
        $phoneNumber    = $this->post('nohp');
        $category       = $this->post('jenisChannel');
        $jenisCostumer  = $this->post('jenisCostumer');
        $noResi         = $this->post('noresi');
        $channel        = $this->post('channel');
        $user           = $this->post('user');
        $bussinessType  = $this->post('jenisbisnis');
        $asalPengaduan  = $this->post('kantorKirim');
        $kantorPengaduan= $this->post('kantorPengaduan');
        $kantorTujuan   = $this->post('kantorTujuan');
        $tujuanPengaduan= $this->post('tujuanPengaduan');
        $notes          = $this->post('catatan');
        $layanan        = $this->post('layanan');
        $jenisPos       = $this->post('channelpos');
        $address        = $this->post('alamat');
        $email          = $this->post('email');
        $fb             = $this->post('fb');
        $instagram      = $this->post('instagram');
        $twitter        = $this->post('twitter');
        $nik            = $this->post('nik');
        $custId         = $this->getIdPelanggan();
        $substr			= substr($kantorPengaduan,-5, 3);
        $endNoTicket	= $this->getIdTicket();
        $noTicket       = "".$substr."".$endNoTicket."";

        $ticket = array(
            'no_ticket'         => $noTicket,
            'phone_number'      => $phoneNumber,
            'category'          => $category,
            'priority'          => '5',
            'date'              => $this->getCurdate(),
            'last_update'       => $this->getCurdate(),
            'status'            => '1',
            'awb'               => $noResi,
            'channel'           => $channel,
            'user_cch'          => $user,
            'kantor_pengaduan'  => $kantorPengaduan,
            'asal_pengaduan'    => $asalPengaduan,
            'kantoTujuan'       => $kantorTujuan,
            'tujuan_pengaduan'  => $tujuanPengaduan,
            'notes'             => $notes,
            'jenis_layanan'     => $layanan,
            'jenis_pos'         => $jenisPos,
            'jenis_customer'    => 'ritel',
            'jenis_bisnis'      => $bussinessType,
            'cust_id'           => $custId,
            'status_read'       => '1'
        );

        $ticketRes = array(
        	'ticket_id'			=> $noTicket,
        	'response'			=> $notes,
        	'username'			=> $user,
        	'ticket_status'		=> 1,
        	'date'				=> $this->getCurdate(),
        	'update_office'		=> $tujuanPengaduan
        );

        $ccare  = array(
            'created_date'      => $this->getCurdate(),
            'cust_id'           => $custId,
            'name_requester'    => $nama,
            'address'           => $address, 
            'phone'             => $phoneNumber,
            'email'             => $email,
            'facebook'          => $fb,
            'instagram'         => $instagram,
            'twitter'           => $twitter,
            'type_request'      => $category,
            'user_ccare'        => $user, 
            'status'            => '1',
            'id_ktp'            => $nik,
            'kantorDaftar'      => $nopend
        );

        $config['upload_path']          = './assets/';
        $config['allowed_types']        = '*';
        $config['encrypt_name']         = TRUE;

        $this->load->library('upload', $config);
        $this->upload->initialize($config);

        $sql    = "SELECT cust_id, name_requester FROM ccare WHERE name_requester = ?";
        $query  = $this->db->query($sql, array($nama));
        if ($query->num_rows() > 0) { //hanya insert t_tiket
            $dataCustomer = $query->row_array();
            $ticket['cust_id'] = $dataCustomer['cust_id'];
            if ($this->upload->do_upload('file')) {
                $ticketRes['file_name'] = $this->upload->data('file_name');
                $this->db->insert('ticket', $ticket);
                $this->db->insert('ticket_response', $ticketRes);
                if ($this->db->affected_rows() > 0) {
                    $this->response(array('status' => 200, 'msg' => 'INSERT DATA SUCCES','file_name' => $ticketRes['file_name']), 200);
                }else{
                    $error = $this->upload->display_errors();
                    $this->response(array('status' => 400, 'msg' => $error), 400);
                }
            } else {
                $this->db->insert('ticket', $ticket);
                $this->db->insert('ticket_response', $ticketRes);
                if ($this->db->affected_rows() > 0) {
                    $this->response(array('status' => 200, 'msg' => 'INSERT DATA SUCCES', 'file_name' => 'tidak upload'), 200);
                }else{
                    $this->response(array('status' => 400, 'msg' => 'FAILED INSERT DATA'), 400);
                }        
            }
        } else {
            if ($this->upload->do_upload('file')) {
                $ticketRes['file_name'] = $this->upload->data('file_name');
                $this->db->insert('ticket', $ticket);
                $this->db->insert('ccare', $ccare);
                $this->db->insert('ticket_response', $ticketRes);
                if ($this->db->affected_rows() > 0) {
                    $this->response(array('status' => 200, 'msg' => 'INSERT DATA SUCCES','file_name' => $ticketRes['file_name']), 200);
                }else{
                    $error = $this->upload->display_errors();
                    $this->response(array('status' => 400, 'msg' => $error), 400);
                }
            } else {
    	        $this->db->insert('ticket', $ticket);
                $this->db->insert('ccare', $ccare);
                $this->db->insert('ticket_response', $ticketRes);
                if ($this->db->affected_rows() > 0) {
                    $this->response(array('status' => 200, 'msg' => 'INSERT DATA SUCCES','file_name' => ''), 200);
                }else{
                    $this->response(array('status' => 400, 'msg' => 'FAILED INSERT DATA'), 400);
                }
            }
        }
    }

    public function getTicket_post(){
        $email      = $this->post('email');
        $kantor_pos = $this->post('kantor_pos');
        $sql2       = "SELECT a.no_ticket, CONCAT(e.name ,' : ', d.name_requester ) as pelanggan, a.kantor_pengaduan as asal_pengaduan , a.tujuan_pengaduan,
                        c.name as jenisTicket, a.`date`, b.name , f.name AS statusRead from ticket a 
                        INNER JOIN ticket_status b ON a.status = b.id 
                        INNER JOIN category c ON a.category = c.auto_id
                        INNER JOIN ccare d ON a.cust_id  = d.cust_id 
                        INNER JOIN channel_info e ON a.channel  = e.auto_id 
                        INNER JOIN statusRead f ON a.status_read = f.id
                        WHERE a.user_cch = ? ORDER BY a.date DESC";
        $sql3       = "SELECT a.no_ticket, CONCAT(e.name ,' : ', d.name_requester ) as pelanggan, a.kantor_pengaduan as asal_pengaduan , a.tujuan_pengaduan ,
                        c.name as jenisTicket, a.`date`, b.name , f.name AS statusRead from ticket a 
                        INNER JOIN ticket_status b ON a.status = b.id 
                        INNER JOIN category c ON a.category = c.auto_id
                        INNER JOIN ccare d ON a.cust_id  = d.cust_id 
                        INNER JOIN channel_info e ON a.channel  = e.auto_id 
                        INNER JOIN statusRead f ON a.status_read = f.id
                        WHERE a.tujuan_pengaduan like '%$kantor_pos%' ORDER BY a.date DESC";
        // $query      = $this->db->query($sql, array($email));
        $query2     = $this->db->query($sql2, array($email));
        $query3     = $this->db->query($sql3);
        if ($query2->num_rows() <= 0 && $query3->num_rows() <= 0) {
            $this->response(array("errors" => 'FAILED GET LIST TICKET'), 400);    
        }else{
            $ticketKeluar   = $query2->result_array();
            $ticketmasuk    = $query3->result_array();

            $res = array(
                'ticketKeluar' => $ticketKeluar,
                'ticketMasuk' => $ticketmasuk
            );

            $this->response($res, 200);   
        }        
    }

    public function ticketSelesai_post(){
        $noTicket       = $this->post('noTicket');
        $jenisAduan     = $this->post('jenisAduan');
        $lokusMasalah   = $this->post('lokusMasalah');

        $ticketSelesai      = array(
            'no_ticket'     => $noTicket,
            'jenis_aduan'   => $jenisAduan,
            'lokus_masalah' => $lokusMasalah
        );

        $updateTicket   = array(
            'status'    => '99'
        );

        $sql    = 'SELECT * FROM ticket_selesai WHERE no_ticket = ?';
        $query  = $this->db->query($sql, array($noTicket));
        if ($query->num_rows() > 0) {
            $this->response(array('status' => 400, 'msg' => 'TIKET SUDAH TERTUTUP'), 400);
        } else {
            $this->db->insert('ticket_selesai', $ticketSelesai);
            $this->db->where('no_ticket', $noTicket);
            $this->db->update('ticket', $updateTicket);
            if ($this->db->affected_rows() > 0) {
                $this->response(array('status' => 200, 'msg' => 'SUKSES TUTUP TIKET'), 200);
            }else{
                $this->response(array('status' => 400, 'msg' => 'GAGAL TUTUP TIKET'), 400);
            }
        }
    }

    public function detailTicket_post(){
    	$noTicket 	= $this->post('noTicket');
    	$sql 		="SELECT a.no_ticket, b.name_requester, b.address,
						b.phone , a.awb, a.jenis_layanan, a.asal_pengaduan, c.name AS status,a.user_cch as pembuatanTicket,
						a.tujuan_pengaduan, a.`date`
						FROM ticket a 
						INNER JOIN ccare b ON b.cust_id = a.cust_id  
                        INNER JOIN ticket_status c ON a.status = c.id 
						WHERE a.no_ticket = ?";
		$sql2 		= "SELECT a.response , a.date , a.username , b.name AS status, a.file_name, c.file_name as photoProfile
                        FROM ticket_response a
                        INNER JOIN ticket_status b ON a.ticket_status = b.id 
                        LEFT JOIN sys_user c ON a.username = c.email
                        WHERE a.ticket_id = ? 
                        ORDER BY `date` DESC";
		$query 		= $this->db->query($sql, array($noTicket));
		$query2		= $this->db->query($sql2, array($noTicket));

		$updateRead = array(
			'status_read' => '2'
		);
			$this->db->where('no_ticket', $noTicket);
			$this->db->update('ticket', $updateRead);
		if ($query->num_rows() > 0 ) {
			$detailTicket = $query->row_array();
			if ($query2->num_rows() > 0) {
				$resTicket = $query2->result_array();
				if ($this->db->affected_rows() > 0) {
					$this->response(array('detailTicket' => $detailTicket,'notes' => $resTicket), 200);	
				} else {
					$this->response(array('detailTicket' => $detailTicket,'notes' => $resTicket), 200);	
				}
			} else {
				$this->response(array("errors" => 'GAGAL MEMUAT DATA DETAIL TIKET'), 400);
			}
		} else {
			$this->response(array("errors" => 'TIKET TIDAK DITEMUKAN '), 400);
		}
    }

    public function realtimeResponse_post(){
        $noTicket   = $this->post('noTicket');
        $sql        = "SELECT a.response , a.date , a.username , b.name AS status, a.file_name, c.file_name as photoProfile
                        FROM ticket_response a$ccare  
                        INNER JOIN ticket_status b ON a.ticket_status = b.id 
                        LEFT JOIN sys_user c ON a.username = c.email
                        WHERE a.ticket_id = ? 
                        ORDER BY `date` DESC";
        $query      = $this->db->query($sql, array($noTicket));
        if ($query->num_rows() > 0 ) {
            $detailTicket = $query->result_array();
            $this->response(array('notes' => $detailTicket), 200);
        } else {
            $this->response(array("errors" => 'TIKET TIDAK DITEMUKAN '), 400);
        }
    }

    public function responseTicket_post(){
        $noTicket       = $this->post('noTicket');
        $username       = $this->post('user');
        $response       = $this->post('response');
        $updateOffice   = $this->post('tujuanPengaduan');
        $date           = $this->getCurdate();

        $upload      = array(
            'ticket_id'         => $noTicket,
            'response'          => $response,
            'username'          => $username,
            'ticket_status'     => '12',
            'date'              => $date,
            'update_office'     => $updateOffice
        );

        $updateRead = array(
            'status_read' => '1',
            'status'    => '12'
        );

        $config['upload_path']          = './assets/';
        $config['allowed_types']        = '*';
        $config['encrypt_name']         = TRUE;

        $this->load->library('upload', $config);
        $this->upload->initialize($config);

        if (!$this->upload->do_upload('file')) {
            $this->db->insert('ticket_response', $upload);
            if ($this->db->affected_rows() > 0) {
                $this->db->where('no_tiket', $noTicket);
                $this->db->update('tiket', $updateRead);
                if ($this->db->affected_rows() > 0) {
                    $this->response(array('status' => 200, 'msg' => 'INSERT DATA SUCCES', 'curdate' => $date, 'file_name' => ''), 200);
                } else {
                    $this->response(array('status' => 200, 'msg' => 'INSERT DATA SUCCES', 'curdate' => $date, 'file_name' => ''), 200);
                }
            }else{
                $this->response(array('status' => 400, 'msg' => 'FAILED INSERT DATA'), 400);
            } 
        }else{
            $upload['file_name'] = $this->upload->data('file_name');
            $this->db->insert('ticket_response', $upload);
            if ($this->db->affected_rows() > 0) {
                    $this->db->where('no_tiket', $noTicket);
                    $this->db->update('tiket', $updateRead);
                    $this->response(array('status' => 200, 'msg' => 'SUKSES UPLOAD FILE','file_name' => $upload['file_name']), 200);
                } else {
                    $error = $this->upload->display_errors();
                    $this->response(array('status' => 400, 'msg' => $error), 400);
            } 
        }
    }

    public function addNotes_post(){
        $this->load->helper('string');
        $notesId        = random_string('numeric',10);
        $custId         = $this->getIdPelanggan();
        $notes          = $this->post('notes');
        $nama           = $this->post('requestName');
        $phoneNumber    = $this->post('nohp');
        $address        = $this->post('alamat');
        $email          = $this->post('email');
        $fb             = $this->post('fb');
        $instagram      = $this->post('instagram');
        $twitter        = $this->post('twitter');
        $nik            = $this->post('nik');
        $user           = $this->post('user');
        $category       = $this->post('jenisChannel');

        $addNotes = array(
            'note_id'   => $notesId,
            'cust_id '  => $custId,
            'notes'     => $notes,
            'date'      => $this->getCurdate()

        );

        $ccare  = array(
            'created_date'      => $this->getCurdate(),
            'cust_id'           => $custId,
            'name_requester'    => $nama,
            'address'           => $address, 
            'phone'             => $phoneNumber,
            'email'             => $email,
            'facebook'          => $fb,
            'instagram'         => $instagram,
            'twitter'           => $twitter,
            'type_request'      => $category,
            'user_ccare'        => $user, 
            'status'            => '1',
            'id_ktp'            => $nik
        );

        $sql    = "SELECT cust_id, name_requester FROM ccare WHERE name_requester = ?";
        $query  = $this->db->query($sql, array($nama));
        if ($query->num_rows() > 0) {
            $dataCustomer = $query->row_array();
            $notes['cust_id'] = $dataCustomer['cust_id'];
            $this->db->insert('notes', $addNotes);
            if ($this->db->affected_rows() > 0) {
                $this->response(array('status' => '200', 'msg' => 'SUKSES INSERT DATA'),200);
            } else {
                $this->response(array('status' => '200', 'msg' => 'GAGAL INSERT DATA'),400);
            }
        } else {
            $this->db->insert('notes', $addNotes);
            $this->db->insert('ccare', $ccare);
            if ($this->db->affected_rows() > 0) {
                $this->response(array('status' => 200, 'msg' => 'INSERT DATA SUCCES'), 200);
            }else{
                $this->response(array('status' => 400, 'msg' => 'FAILED INSERT DATA'), 400);
            }
        }
    }

//PELANGGAN function
    // public function addPelanggan2_post(){
    //     $custId         = $this->getIdPelanggan();
    //     $nama           = $this->post('requestName');
    //     $address        = $this->post('alamat');
    //     $phoneNumber    = $this->post('nohp');
    //     $email          = $this->post('email');
    //     $fb             = $this->post('fb');
    //     $instagram      = $this->post('instagram');
    //     $twitter        = $this->post('twitter');
    //     $user           = $this->post('user');
    //     $nik            = $this->post('nik');
    //     $nopend			= $this->post('nopend');
    //     $category       = $this->post('jenisChannel');

    //     $ccare  = array(
    //         'created_date'      => $this->getCurdate(),
    //         'cust_id'           => $custId,
    //         'name_requester'    => $nama,
    //         'address'           => $address, 
    //         'phone'             => $phoneNumber,
    //         'email'             => $email,
    //         'facebook'          => $fb,
    //         'instagram'         => $instagram,
    //         'twitter'           => $twitter,
    //         'type_request'      => $category,
    //         'user_ccare'        => $user, 
    //         'status'            => '1',
    //         'id_ktp'            => $nik,
    //         'kantorDaftar'      => $nopend
    //     );

    //     $sql    = "SELECT cust_id, name_requester FROM ccare WHERE name_requester = ? and kantorDaftar = ?";
    //     $query  = $this->db->query($sql, array($nama, $nopend));
    //     if ($query->num_rows() > 0) { 
    //         $dataCustomer       = $query->row_array();
    //         $this->response(array('status' => 201, 'msg' => 'DATA SUDAH ADA'), 200);
    //     } else {
    //         $this->db->insert('ccare', $ccare);
    //         if ($this->db->affected_rows() > 0) {
    //             $this->response(array('status' => 200, 'msg' => 'INSERT DATA SUCCES'), 200);
    //         } else {
    //             $this->response(array('status' => 400, 'msg' => 'FAILED INSERT DATA'), 400);
    //         }                
    //     }
    // }

    public function addPelanggan_post(){
        $custId         = $this->getIdPelanggan2();
        $nama           = $this->post('requestName');
        $address        = $this->post('alamat');
        $phoneNumber    = $this->post('nohp');
        $email          = $this->post('email');
        $sosmed         = $this->post('sosmed');
        $user           = $this->post('user');
        $nik            = $this->post('nik');
        $nopend         = $this->post('nopend');
        $category       = $this->post('jenisChannel');
        $detailAddress  = $this->post('detailAlamat');

        $ccare  = array(
            'created_date'      => $this->getCurdate(),
            'cust_id'           => $custId,
            'name_requester'    => $nama,
            'address'           => $address, 
            'phone'             => $phoneNumber,
            'email'             => $email,
            'sosmed'            => $sosmed,
            'type_request'      => $category,
            'user_ccare'        => $user, 
            'status'            => '1',
            'id_ktp'            => $nik,
            'kantorDaftar'      => $nopend,
            'detail_address' => $detailAddress
        );

        $sql    = "SELECT cust_id, name_requester FROM pelanggan WHERE name_requester = ? AND kantorDaftar = ?";
        $query  = $this->db->query($sql, array($nama, $nopend));
        if ($query->num_rows() > 0) { //hanya insert t_tiket
            $dataCustomer       = $query->row_array();
            $this->response(array('status' => 201, 'msg' => 'DATA SUDAH ADA', 'custid' => $dataCustomer['cust_id']), 200);
        } else {
            $this->db->insert('pelanggan', $ccare);
            if ($this->db->affected_rows() > 0) {
                $this->response(array('status' => 200, 'msg' => 'BERHASIL MENAMBAHKAN DATA PELANGGAN', 'custid' => $custId), 200);
            } else {
                $this->response(array('status' => 400, 'msg' => 'GAGAL MENAMBAHKAN DATA PELANGGAN'), 400);
            }
        }
    }

    public function editPelanggan_post(){
        $custid         = $this->post('id');
        $nama           = $this->post('namaLengkap');
        $address        = $this->post('alamat');
        $phoneNumber    = $this->post('phone');
        $email          = $this->post('email');
        $sosmed 		= $this->post('sosmed');
        $detail         = $this->post('detail_address');
        // $kab         = $this->post('alamat');
        //$user           = $this->post('user');
        //$nik            = $this->post('nik');

        $ccare  = array(
            'name_requester'    => $nama,
            'address'           => $address, 
            'phone'             => $phoneNumber,
            'email'             => $email,
            'sosmed'	 		=> $sosmed,
            'detail_address'    => $detail,
            // 'address' => $kab
            // 'user_ccare'        => $user, 
            //'id_ktp'            => $nik
        );

        $this->db->where('cust_id',$custid);
        $this->db->update('pelanggan', $ccare);
        if($this->db->affected_rows() > 0){ 
            $this->db->select('a.kantorDaftar as nopend , b.regional ,b.fullname as kantorPos, a.cust_id as customerId, a.id_ktp as idktp, a.type_request');
            $this->db->select('a.name_requester as namaLengkap, a.address as alamat, a.phone');
            $this->db->select('a.email , a.sosmed , c.name as JenisSosmed, a.detail_address');
            $this->db->from('pelanggan a');
            $this->db->join('office b', 'a.kantorDaftar = b.code', 'LEFT', NULL);
            $this->db->join('channel_info c', 'a.type_request = c.auto_id', 'LEFT', NULL);
            $this->db->WHERE('a.cust_id', $custid);
            $query = $this->db->get()->row_array();
            $this->response(array("result" => 'BERHASIl UBAH DATA PELANGGAN', "data" => $query ), 200); 
        }else{
            $this->response(array("errors" => array("global" => "GAGAL UBAH DATA PELANGGAN")), 400);    
        }
    }

    public function getPelanggan_post(){
        $nama   = $this->post('requestName');
        $nopend = $this->post('nopend');
        $this->db->select('cust_id, name_requester, address, phone, sosmed, created_date, detail_address, email');
        $this->db->select('type_request');
        $this->db->from('pelanggan');
        $this->db->where('kantorDaftar', $nopend);
        
        $this->db->group_start();
        $this->db->or_like('sosmed', $nama);
        $this->db->or_like('phone', $nama);
        $this->db->group_end();

        //$sql    = "SELECT cust_id, name_requester, address, phone, sosmed, created_date FROM pelanggan WHERE sosmed LIKE '%$nama%' and kantorDaftar = $nopend ";
        //$query  = $this->db->query($sql)->result_array();
        $query  = $this->db->get();

        $this->response($query->result_array(), 200);
    }

    public function countPelanggan_post(){
        $kprk       = $this->post('kprk');
        $regional   = $this->post('regional');
        
        $this->db->select('count(a.cust_id) as jmlPelanggan');
        $this->db->from('pelanggan a');
        $this->db->join('channel_info c', 'a.type_request = c.id');
        
        if($regional == '01'){ //pusat
            if ($kprk == '00') {
                $this->db->where_in('a.kantorDaftar', array('00002','40005','00001'));
            }else{
                $this->db->where('a.kantorDaftar', $kprk);
            }
        }else if($regional == '02'){
            $this->db->join('office b', 'a.kantorDaftar = b.code');
            $this->db->where("b.regional <> 'KANTORPUSAT'");
        }else if($regional != '00'){ //not all regional
            if ($kprk === '00') { //all kprk
                $this->db->join('office b', 'a.kantorDaftar = b.code');
                $this->db->where('b.regional', $regional);
            }else{ //curent kprk
                $this->db->where('a.kantorDaftar', $kprk);
            }
        }

        if ($this->post('channel')) {
            $channel    = $this->post('channel');
            if ($channel != '00') $this->db->where('c.id', $channel);
        }

        $query = $this->db->get()->row_array();
        $this->response($query, 200);
    }

    public function getPelangganByKprk_post(){
        $kprk       = $this->post('kprk');
        $offset     = $this->post('offset');
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
            }else if($regional == '02'){ //al except pusar
                $this->db->where("b.regional <> 'KANTORPUSAT'");
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
        $this->db->limit(11, $offset);

        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            $this->response($query->result_array(), 200);
        }else{
            $this->response(array("errors" => 'GAGAL AMBIL DATA'), 400);
        }

    }

//USER function
    public function authLogin_post(){
        $username   = $this->post('username');
        $password   = md5($this->post('password'));
        
        $sql        = "SELECT a.username, a.title , a.email , a.phone ,a.kantor_pos,b.fullname, b.regional, b.name , a.utype, c.name as jabatan, 
                        a.file_name AS img, CONCAT(c.name, ' ', a.utype) as level,
                        a.last_login FROM sys_user a  
                        LEFT JOIN office b ON a.kantor_pos  = b.code 
                        LEFT JOIN sys_role c ON a.role_id  = c.id
                        WHERE a.username = '$username' AND a.password = '$password'";
        $query      = $this->db->query($sql);
        if ($query->num_rows() > 0) {
            $data = $query->row_array();
            $login      = array('is_online' => 1, 'last_login' => $this->getCurdate() );
            $this->db->where('username',$username);
            $this->db->update('sys_user', $login);
            if($this->db->affected_rows() > 0){ 
                $data['token'] = $this->signed_token($data);
                $data['isLogin'] = true;
                $this->response(array("result" => $data), 200); 
            }else{
                $this->response(array("errors" => array("global" => "GAGAL LOGIN 1")), 400);    
            }
        }else{
            $this->response(array("errors" => array("global" => "GAGAL LOGIN 2")), 400);
        }
    }

    public function addUser_post(){
        $namaLengkap    = $this->post('namaLengkap');
        $nip            = $this->post('nip');
        // $username       = $this->post('username');
        $email          = $this->post('email');
        $role           = $this->post('jabatan');
        $kdkantor       = $this->post('kdkantor');
        $jenisKantor    = $this->post('jenisKantor');
        $phone          = $this->post('phone');
        $jabatanKantor  = $this->post('jabatanKantor');
        
        $addUser = array(
            'id'            => $this->getId(),
            'title'         => $namaLengkap,
            'username'      => $nip,
            'password'      => md5($nip),
            'user_hash'     => md5($nip),
            'email'         => $email,
            'role_id'       => $role,
            'kantor_pos'    => $kdkantor,
            'utype'         => $jenisKantor,
            'phone'         => $phone,
            'status'        => 1,
            'jabatan'		=> $jabatanKantor 

        );

        $this->db->select('username, email');
        $this->db->from('sys_user');
        $this->db->where('username', $nip);
        $this->db->or_where('email', $email);

        $query  = $this->db->get();
        if ($query->num_rows() > 0) { //hanya insert t_tiket
            $dataUser       = $query->row_array();
            $addUser['username']  = $dataUser['username'];
            $this->response(array('status' => 201, 'msg' => 'USERNAME ATAU EMAIL SUDAH TERDAFTAR'), 400);
        } else {
            $this->db->insert('sys_user', $addUser);
            if ($this->db->affected_rows() > 0) {
                $this->response(array('status' => 200, 'msg' => 'USER BERHASIL DIDAFTARKAN'), 200);
            } else {
                $this->response(array('status' => 400, 'msg' => 'FAILED INSERT DATA'), 400);
            }                
        }
    }

    public function getUser_post(){
        $limit  = $this->post('limit');
        $offset  = $this->post('offset');
        $regional  = $this->post('regional');
        $kprk   = $this->post('kprk');
        //all
        $this->db->select('a.title as NamaLengkap, a.username , a.email , a.kantor_pos as kprk');
        $this->db->select('b.fullname as kantorPos, b.regional, c.name as jabatan, a.status, b.kd_wilayah, a.phone');
        $this->db->from('sys_user a');
        $this->db->join('office b', 'a.kantor_pos = b.code', 'LEFT', NULL);
        $this->db->join('sys_role c', 'a.role_id = c.id', 'LEFT', NULL);
        // $this->db->where_not_in('a.role_id', array('1'));

        if ($regional != '00') {
            if($regional == 'KANTORPUSAT'){
                if ($kprk == '00') {
                    $this->db->where_in('a.kantor_pos', array('00002','40005','00001'));
                }else{
                    $this->db->where('a.kantor_pos', $kprk);
                }
            }else if($regional === '02'){
                $this->db->where("b.regional <> 'KANTORPUSAT'");

            }else{
                if ($kprk == '00') { //current regional
                    $this->db->where('b.regional', $regional);
                }else{
                    $this->db->where('a.kantor_pos', $kprk);
                }
            }
        }

        if ($this->post('status')) {
            $status = $this->post('status');
            $this->db->where('a.status', $status);
        }

        $this->db->order_by('b.kd_wilayah', 'ASC');
        $this->db->order_by('a.status', 'ASC');
        $this->db->limit($limit, $offset);
        $query = $this->db->get();
        $this->response($query->result_array(), 200);
        // if ($query->num_rows() > 0) {
        // }else{
        //     $this->response(array("errors" => 'GAGAL MEMUAT DATA'), 400);
        // }
    }

    public function countUser_post(){
        $kprk       = $this->post('kprk');
        $regional   = $this->post('regional');

        $this->db->select('count(a.id) as jmlUser');
        $this->db->from('sys_user a');
        if ($regional == '00') { //all regional
            $this->db->where_not_in('a.role_id', array('1'));
        }elseif($regional == '02'){
            $this->db->join('office b', 'a.kantor_pos = b.code');
            $this->db->where("b.regional <> 'KANTORPUSAT'");
        }else if($regional == 'KANTORPUSAT'){
            if ($kprk == '00') {
                $this->db->where_in('a.kantor_pos', array('00002','40005','00001'));
            }else{
                $this->db->where('a.kantor_pos', $kprk);
            }
        }else{
            if ($kprk == '00') { //all kprk
                $this->db->join('office b', 'a.kantor_pos = b.code');
                $this->db->where('b.regional', $regional);
            }else{ //current office
                $this->db->where('a.kantor_pos', $kprk);
            }
        }

        if ($this->post('status')) {
            $status = $this->post('status');
            $this->db->where('a.status', $status);
        }

        $query = $this->db->get()->row_array();
        $this->response($query, 200);
    }

    public function changePassword_post(){
        $user   = $this->post('username');
        $pwd    = $this->post('password');
        $date   = $this->getCurdate();

        $updatePwd = array(
            'password'  => md5($pwd)
        );

        $this->db->where('username', $user);
        $this->db->update('sys_user', $updatePwd);
        if ($this->db->affected_rows() > 0) {
            $this->response(array('status' => 200, 'msg' => 'BERHASIL MERUBAH PASSWORD', 'curdate' => $date), 200);
        } else {
            $this->response(array('status' => 400, 'msg' => 'GAGAL MERUBAH PASSWORD', 'curdate' => $date), 400);
        }
    }

    public function editUser_post(){
        $user   = $this->post('username');
        $status = $this->post('status');
        $date   = $this->getCurdate();

        $updatePwd = array(
            'status'  => $status
        );

        $this->db->where('username', $user);
        $this->db->update('sys_user', $updatePwd);
        if ($this->db->affected_rows() > 0) {
            $this->response(array('status' => 200, 'msg' => 'PENGGUNA TELAH DINONAKTIFKAN', 'curdate' => $date), 200);
        } else {
            $this->response(array('status' => 400, 'msg' => 'GAGAL MENONAKTIFKAN PENGGUNA', 'curdate' => $date), 400);
        }
    }

    public function getProfile_post(){
        $user   = $this->post('user');

        $sql    = "SELECT a.username, a.title , a.email , a.phone ,a.kantor_pos,b.fullname, b.regional, b.name , a.utype, c.name as jabatan, a.file_name AS img,
                    a.last_login FROM sys_user a  
                    LEFT JOIN office b ON a.kantor_pos  = b.code 
                    LEFT JOIN sys_role c ON a.role_id  = c.id
                    WHERE a.email = ?";
        $query  = $this->db->query($sql, array($user));
        if ($query->num_rows() > 0) {
            $profile = $query->row_array();
            $this->response($profile, 200);
        } else {
            $this->response(array('status' => 400, 'msg' => 'FAILES GET PROFILE'), 400);
        }
    }

    public function uploadImg_post(){
        $user   = $this->post('user');
        $config['upload_path']          = './assets/profile/';
        $config['allowed_types']        = '*';
        $config['encrypt_name']         = TRUE;

        $this->load->library('upload', $config);
        $this->upload->initialize($config);

        if ($this->upload->do_upload('file')) {
            $upload['file_name'] = $this->upload->data('file_name');
            $this->db->where('email', $user);
            $this->db->update('sys_user', $upload);
            if ($this->db->affected_rows() > 0) {
                    $this->response(array('status' => 200, 'msg' => 'SUKSES UPLOAD FILE','file_name' => $upload['file_name']), 200);
                } else {
                    $error = $this->upload->display_errors();
                    $this->response(array('status' => 400, 'msg' => $error), 400);
            } 
        } else {
            $error = $this->upload->display_errors();
            $this->response(array('status' => 400, 'msg' => $error), 400);
        }
    }
//DASHBORD function
    public function dashboard_post(){
        $kprk       = $this->post('kprk');
        $reg   = $this->post('regional');

        $this->response(array(
            'pencapaian' => array(
                'selesaiLbh24' => $this->getPencapaianLbh24($kprk,$reg),
                'selesaiKrg24' => $this->getPencapaianKrg24($kprk, $reg)
            ),
            'pengaduanHariIni' => array(
                'lainnyaHariIni' => '',
                'TiketHariIni' => ''
            ),
            'statistik' => array(
                'semuaTicket' => '',
                'ticketSelesai' => '',
                'ticketTerbuka' => ''
          )), 200);   
    }

    private function getPencapaianLbh24($kprk, $reg){

        $this->db->select('COUNT(a.no_tiket) AS selesaiLbh24');
        $this->db->from('tiket a');
        $this->db->join('ticket_selesai b', 'a.no_tiket = b.no_ticket');
        if ($reg == '00') {
	        $this->db->where("TIMESTAMPDIFF(HOUR , a.tgl_exp, b.tgl_selesai  ) > 24 AND a.status = '99'", null, false);
        }else{
            if ($kprk == '00') {
		        $this->db->join('office c', 'a.tujuan_pengaduan = c.code');
                $this->db->where("TIMESTAMPDIFF(HOUR , a.tgl_exp, b.tgl_selesai  ) > 24 AND a.status = '99' AND c.regional = '".$reg."'", null, false);
            }else{
                $this->db->where("TIMESTAMPDIFF(HOUR , a.tgl_exp, b.tgl_selesai  ) > 24 AND a.status = '99' AND a.tujuan_pengaduan = '".$kprk."'", null, false);
            }
        }

        $query = $this->db->get()->row_array();
        return $query['selesaiLbh24'];
    }

    private function getPencapaianKrg24($kprk, $reg){
      	$this->db->select('COUNT(a.no_tiket) AS selesaiKrg24');
        $this->db->from('tiket a');
        $this->db->join('ticket_selesai b', 'a.no_tiket = b.no_ticket');
        if ($reg == '00') {
	        $this->db->where("TIMESTAMPDIFF(HOUR , a.tgl_exp, b.tgl_selesai  ) < 24 AND a.status = '99'", null, false);
        }else{
            if ($kprk == '00') {
		        $this->db->join('office c', 'a.tujuan_pengaduan = c.code');
                $this->db->where("TIMESTAMPDIFF(HOUR , a.tgl_exp, b.tgl_selesai  ) < 24 AND a.status = '99' AND c.regional = '".$reg."'", null, false);
            }else{
                $this->db->where("TIMESTAMPDIFF(HOUR , a.tgl_exp, b.tgl_selesai  ) < 24 AND a.status = '99' AND a.tujuan_pengaduan = '".$kprk."'", null, false);
            }
        }

        $query = $this->db->get()->row_array();
        return $query["selesaiKrg24"];
    }

  
//API PCI function
    public function tnt_post(){
        $type           = $this->post('type');
        $barcode        = $this->post('barcode');
        if ($type == '1') {
            $res = $this->tntdn($barcode);
            $this->response($res, 200);
        } elseif ($type == '2') {
            $res = $this->tntln($barcode);
            $this->response($res, 200);
        }
    }

    private function tntdn($barcode){
        $secretKey  = "5bd6ab2e-b5e0-39e8-99f9-e879763abe86";

        $url        = 'https://api.posindonesia.co.id:8245/utility/1.0.0/getTrackAndTrace';

        $postData   = array('barcode' => $barcode);
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
        $data           = $responsenya['response']['data'];
        if (empty($data)) {
            $res = array(
                'errors' => array('global' => "Data dengan barcode tersebut tidak ditemukan")
            );  
            return $res;
        }else{
            if (empty($data['barcode'])){ //data banyak
                $res = array(
                    'result' => $data
                );      
            }else{
                $res = array(
                    'result' => [$data]
                );      
            }
            return $res;
        }
    }

    private function tntln($barcode){
        $secretKey  = "5bd6ab2e-b5e0-39e8-99f9-e879763abe86";

        $url        = 'https://api.posindonesia.co.id:8245/utility/1.0.0/getTrackAndTraceLn';

        if (substr($barcode ,0,2) == 'EE') {
            $layanan = '310';
        } elseif (substr($barcode,0,2) == 'LP') {
            $layanan = '3LP';
        } elseif (substr($barcode,0,2) == 'CC') {
            $layanan = '3PE';
        }else{
            $layanan = 'NOT FOUNd';
        }
               

        $postData   = array('barcode' => $barcode);
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
        $data           = $responsenya['response']['data'];
        foreach ($data as $key) {
            $res[] = array(
                'barcode' => $barcode,
                'officeCode'=> $key['senderOfficeCode'],
                'officeName'=> $key['senderOfficeName'],
                'eventName'=> $key['description'],
                'eventDate'=> $key['eventDate']. ' ' .$key['eventTime'],
                'description'=> 'LAYANAN :'.$layanan.';;;;;;;;;;;'.$key['receiverOfficeCode'].';',
                // 'description'=> $key['description']. ';notes : ' .$key['notes'],
            );
        }
        $data = array('result' => $res);   
        return $data;
        // print_r($data);
    }

    private function produk($barcode){
        $this->db2 = $this->load->database('sqlsrv', TRUE);
        $this->db2->select('kode_layanan');
        $this->db2->from('transipos');
        $this->db2->WHERE('no_resi', $barcode);
        $query = $this->db2->get()->row_array();
        return $query;
    }

    public function kantorPos_post(){
        $city       = $this->post('city');
        $address    = $this->post('address');

        $postData   = array(
            'city'      => $city,
            'address'   => $address,
        );

        $secretKey  = "5bd6ab2e-b5e0-39e8-99f9-e879763abe86";
        $url        = 'https://api.posindonesia.co.id:8245/utilitas/1.0.1/getPostOffice';

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
        $data           = $responsenya['responses'];
        if (empty($data)) {
            $res = array(
                'errors' => array('global' => "Data dengan barcode tersebut tidak ditemukan")
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

    public function kantorPosBaru_post(){
        $city       = $this->post('city');
        $kota       = str_replace("KOTA ","",$city);
        // print_r($kota);
        $address    = $this->post('address'); 
        $sql        = "SELECT KDKANTOR as office_id, KETKANTOR as office_name,
                       JENIS as type, ALAMAT as address, KABUPATEN as city, KELURAHAN as sub_sub_district,
                       KECAMATAN as sub_district, KODEPOS as zipcode, PROVINSI as country, 'HALOPOS 161' as phone,
                       '-' as schedule FROM R_KANTOR
                       WHERE KABUPATEN like '%".$kota."%' AND KECAMATAN like '%".$address."%' AND status_aktif = 'Y'";
        $query  = $this->db->query($sql);
        if ($query->num_rows() > 0) {
            $kantor = $query->result_array();
            $this->response(array('result' =>  $kantor), 200);
        } else {
            $this->response(array("errors" => 'KANTOR POS TIDAK ADA'), 400);
        }
    }

    public function kantorPos2_post(){
        $address    = $this->post('kodepos');

        $postData   = array(
            'receiverzipcode' => $address
        );

        $secretKey  = "5bd6ab2e-b5e0-39e8-99f9-e879763abe86";
        $url        = 'https://api.posindonesia.co.id:8245/utilitas/1.0.1/getOffice';

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
                'errors' => array('global' => "Data dengan barcode tersebut tidak ditemukan")
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


    public function getFee_post(){
        $secretKey  = "5bd6ab2e-b5e0-39e8-99f9-e879763abe86";
        $url        = 'https://api.posindonesia.co.id:8245/utility/1.0.0/getFee';

        $customerid = $this->post('customerid');
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
            'desttypeid' => '1',
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
        
        if (empty($data['data']['serviceCode'])) { //data banyak
            $res = array('result' => $data);    
            $this->response($res, 200);
        } else { //data 1
            if ($data['data']['serviceCode'] == 999) { //internasional dengan data 1
                $postDataLn   = array(
                    'customerid' => $customerid,
                    'desttypeid' => '0',
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
                $string     = json_encode($postDataLn);

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
                $dataLn         = $responsenya['response'];
                $res = array('result' => $dataLn);    
                $this->response($res, 200);
            } else { //nasional dengan data 1 --> to arr response
                $res = array('result' => $data);    
                $this->response($res, 200);
            }
        }
    }

    public function getPostalCode_post(){
        $secretKey  = "5bd6ab2e-b5e0-39e8-99f9-e879763abe86";
        $url        = 'https://api.posindonesia.co.id:8245/utilitas/1.0.1/getPostalCode';

        // $city       = $this->post('city');
        $address    = $this->post('kodepos');

        $postData   = array(
            'city'      => '',
            'address'   => $address,
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
        $data           = $responsenya['rs_postcode']['r_postcode'];
        if (empty($data)) {
            $res = array(
                'errors' => array('global' => "DATA TIDAK DITEMUKAN")
            );  
            $this->response($res, 400);
        }else{
            if (empty($data['posCode'])){ //data banyak
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
//OPTIONAL function
    public function listStatus_post(){
        $sql    =" SELECT id, name FROM ticket_status";
        $query  = $this->db->query($sql);
        if ($query->num_rows() > 0) {
            $listStatus = $query->result_array();
            $this->response($listStatus, 200);
        } else {
            $this->response(array("errors" => 'FAILED GET LIST STATUS'), 400);
        }
    }

    public function listCs_post(){
        $kprk   = $this->post('kprk');
        
        if ($kprk == '') {
            $this->db->select('*');
            $this->db->FROM('sys_user');
            $this->db->where("role_id = '2'");
        } else {
            $this->db->select('*');
            $this->db->FROM('sys_user');
            $this->db->where("role_id = '2'");
            $this->db->where("kantor_pos = '". $kprk ."'");
        }

        $query = $this->db->get()->result_array();
        $this->response($query, 200);

    }

    public function listChannel_post(){
        $sql    ="SELECT id, name as channel FROM channel_info ORDER BY id ASC";
        $query  = $this->db->query($sql);
        if ($query->num_rows() > 0) {
            $listChannel = $query->result_array();
            $this->response($listChannel, 200);
        } else {
            $this->response(array("errors" => 'FAILED GET LIST CHANNEL'), 400);
        }
    }

    public function listRoleUser_post(){
        $sql    =" SELECT id, name FROM sys_role WHERE status = '1' AND id NOT IN ('1', '7')";
        $query  = $this->db->query($sql);
        if ($query->num_rows() > 0) {
            $listRoleUser = $query->result_array();
            $this->response($listRoleUser, 200);
        } else {
            $this->response(array("errors" => 'FAILED GET LIST ROLE USER'), 400);
        }
    }

    public function listOffice_post(){
        $param  = $this->post('param');
        $this->db->select('code as nopend, name as NamaKtr, type');
        $this->db->from('office');
        if ($this->post('type')) {
            
        }else{
            $this->db->where('code <> kdvill');
        }

        $query = $this->db->get();

        if ($query->num_rows() > 0) {
            $listOffice = $query->result_array();
            $this->response($listOffice, 200);
        } else {
            $this->response(array("errors" => 'FAILED GET LIST ROLE USER'), 400);
        }
    }

    public function getProdukLainnya_post(){
        $sql        = "SELECT * FROM ref_layanan where tipe_layanan = '3' ORDER BY nama_layanan ASC";
        $query  = $this->db->query($sql); 
        if ($query->num_rows() > 0) {
            $listProdukjk = $query->result_array();
            $this->response($listProdukjk, 200);
        } else {
            $this->response(array("errors" => 'FAILED GET LIST KPRK'), 400);
        }
    }

    public function getKprk_post(){
        $regional   = $this->post('regional');
        $sql        = "SELECT fullname  as kprk, code, name FROM office WHERE regional = ? AND name NOT LIKE '%regional%' ORDER BY code";
        $query  = $this->db->query($sql, array($regional)); 
        if ($query->num_rows() > 0) {
            $listKprk = $query->result_array();
            $this->response($listKprk, 200);
        } else {
            $this->response(array("errors" => 'FAILED GET LIST KPRK'), 400);
        }
    }

    public function getAduan_post(){
        $sql        = "SELECT * FROM ref_aduan";
        $query  = $this->db->query($sql); 
        if ($query->num_rows() > 0) {
            $listAduan = $query->result_array();
            $this->response($listAduan, 200);
        } else {
            $this->response(array("errors" => 'FAILED GET LIST KPRK'), 400);
        }
    }

    public function getProdukjaskug_post(){
        $sql        = "SELECT * FROM ref_layanan where tipe_layanan = '2' ORDER BY nama_layanan ASC";
        $query  = $this->db->query($sql); 
        if ($query->num_rows() > 0) {
            $listProdukjk = $query->result_array();
            $this->response($listProdukjk, 200);
        } else {
            $this->response(array("errors" => 'FAILED GET LIST KPRK'), 400);
        }
    }


//PRIVATE function 
    private function getCurdate(){
        $sql    = "SELECT now() as sekarang";
        $now    = $this->db->query($sql)->row_array();
        $now    = $now['sekarang'];
        return $now;
    }

    private function validate($param = null){
        $error = [];
        if (empty($param)) $error["param"] = "Param is requird";
        return $error;
    }

    private function getId(){
        $sql    = $this->db->query("SELECT id from sys_user ORDER BY id  DESC LIMIT 1")->row_array();
        $id     = (int)$sql['id'] + 1;
        $newId  = $id;
        return $newId;
    }

    private function signed_token($data) {
    // Create token header as a JSON string
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
        $payload = json_encode($data);
        $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
        $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));
        $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, 'abC123!', true);
        $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));

        $jwt = $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;

        return $jwt;
    }

    private function getIdPelanggan(){
        $now = $this->db->query("SELECT  DATE_FORMAT(now(), '%m%Y') as curdate")->row_array();
        $sql = "SELECT RIGHT(cust_id, 3) as auto, DATE_FORMAT(now(), '%m%Y') as cudate
                FROM  ccare 
                where LEFT(cust_id, 6) = DATE_FORMAT(now(), '%m%Y')
                ORDER BY auto DESC 
                limit 1";
        $query = $this->db->query($sql)->row_array();
        $no     = $query['auto'] + 1;
        $no     = str_pad($no, 3, "0", STR_PAD_LEFT);
        $id     = "".$now['curdate']."".$no."";

        return $id;
    }

    private function getIdPelanggan2(){
        $this->db->select("RIGHT(cust_id, 3) as auto, DATE_FORMAT(now(), '%m%Y') as curdate");
        $this->db->from('pelanggan');
        $this->db->where("LEFT(cust_id, 6) = DATE_FORMAT(now(), '%m%Y')");
        $this->db->order_by('auto', 'DESC');
        $this->db->limit(1);
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
        	$data 	= $query->row_array();
        	$no     = $data['auto'] + 1;
	        $no     = str_pad($no, 3, "0", STR_PAD_LEFT);
	        $id     = "".$data['curdate']."".$no."";
	        return $id;
        }else{
        	$curdate = $this->db->select("DATE_FORMAT(now(), '%m%Y') as curdate")->get()->row_array();
        	$no     = '001';
	        $no     = str_pad($no, 3, "0", STR_PAD_LEFT);
	        $id     = "".$curdate['curdate']."".$no."";
	        return $id;
        }
    }

    private function getIdTicket(){
        $now = $this->db->query("SELECT  DATE_FORMAT(now(), '%m%y') as curdate")->row_array();
        $sql = "SELECT RIGHT(no_ticket, 3) as auto, DATE_FORMAT(now(), '%m%y') as cudate
				FROM  ticket 
				WHERE SUBSTRING(no_ticket, 4, 4) = DATE_FORMAT(now(), '%m%y')
				ORDER BY auto DESC 
				limit 1";
        $query = $this->db->query($sql)->row_array();
        $no     = $query['auto'] + 1;
        $no     = str_pad($no, 3, "0", STR_PAD_LEFT);
        $id     = "".$now['curdate']."".$no."";

        return $id;
    }

    public function updateUser_post(){
        $username   = $this->post('username');
        $email      = $this->post('email');
        $nama       = $this->post('nama');
        $phone      = $this->post('phone');
        $defaultEmail = $this->post('defaultEmail');

        if ($defaultEmail != $email) { //validate only user change email
            $this->db->select('*');
            $this->db->from('sys_user');
            $this->db->where('email', $email);
            $isValidEmail = $this->db->get()->num_rows();
            if ($isValidEmail > 0) {
                $this->response(array('errors' => array('email' => 'Email sudah terdaftar')), 400);
            }else{
                $dataUpdate = array(
                    'email' => $email,
                    'title' => $nama,
                    'phone' => $phone
                );
                $this->db->where('username', $username);
                $this->db->update('sys_user', $dataUpdate);
                if ($this->db->affected_rows() > 0) {
                    $this->response(200);
                }else{
                    $this->response(array('errors' => array('global' => 'Terdapat kesalahan, silahkan cobalagi')), 400);
                }
            }   
        }else{
            $dataUpdate = array(
                'email' => $email,
                'title' => $nama,
                'phone' => $phone
            );
            $this->db->where('username', $username);
            $this->db->update('sys_user', $dataUpdate);
            if ($this->db->affected_rows() > 0) {
                $this->response(200);
            }else{
                $this->response(array('errors' => array('global' => 'Terdapat kesalahan, silahkan cobalagi')), 400);
            }
        }
    }
 
    #end of private function

    public function resetPassword_post(){
        $username = $this->post('username');
        $dataUpdate = array(
            'password' => md5($username),
            'user_hash' => md5($username)
        );

        $this->db->where('username', $username);
        $this->db->update('sys_user', $dataUpdate);

        if ($this->db->affected_rows() >= 0) {
            $this->response(200);
        }else{
            $this->response(array('msg' => 'gagal update'), 400);
        }
    }
}
?>
    	

<?php

use Restserver\Libraries\REST_Controller;
require(APPPATH.'/libraries/REST_Controller.php');

class tiket extends REST_Controller{

	public function getNoTiket_post(){
		$nopend		= $this->post('nopend');
		$substr		= substr($nopend,-5, 3);
		$endNoTiket	= $this->getIdTicket();
        $noTiket       = "".$substr."".$endNoTiket."";

        $tmpDate 	= $this->getCurdate();
		$holidays 	= $this->getLibur($nopend);

		$i = 1;
		$nextBusinessDay = date('Y-m-d', strtotime($tmpDate . ' +' . $i . ' Days'));
		$newDayOfWeek = date('w', strtotime($nextBusinessDay));

		while ( $newDayOfWeek > 0 && $newDayOfWeek < 6 &&  in_array($nextBusinessDay, $holidays)) {
		    $i++;
		    $nextBusinessDay = date('Y-m-d', strtotime($tmpDate . ' +' . $i . ' Days'));
		}

		$time = $this->getTime();
		$newDate = $nextBusinessDay.' '.$time;

		$data = array(
			'noTiket' =>  $noTiket,
			'tglExp' =>  $newDate, );

        if ($nopend == '') {
        	$this->response(array('status' => 400, 'msg' => 'KODE DIRIAN TIDAK BOLEH KOSONG' ), 400);
        } else {
        	$this->response(array('status' => 200, 'result' => $data ), 200);
        }
    }

    public function addTiket_post(){

    	$tiket 			= json_decode($this->post('tiket'));
    	$response_tiket = json_decode($this->post('response_tiket'));

        $config['upload_path']          = './assets/';
        $config['allowed_types']        = '*';
        $config['encrypt_name']         = TRUE;

        $this->load->library('upload', $config);
        $this->upload->initialize($config);
        $do_upload = $this->upload->do_upload('file');

    	$this->db->insert_batch('tiket', $tiket); 
    	if ($this->db->affected_rows() > 0) {
            if (!$do_upload) {
        		$this->db->insert('ticket_response', $response_tiket); 
    			$this->response(array('status' => 200, 'result' => 'BERHASIL MENGAJUKAN PENGADUAN BARU' ), 200);
            }else{
                $insertValue = array(
                    'response' => $response_tiket->response,
                    'file_name' => $this->upload->data('file_name'),
                    'lacak_value' => $response_tiket->lacak_value,
                    'user_cch' => $response_tiket->user_cch,
                    'ticket_id' => $response_tiket->ticket_id,
                    'no_resi' => $response_tiket->no_resi
                );

                $this->db->insert('ticket_response', $insertValue); 
                $this->response(array('status' => 200, 'result' => 'BERHASIL MENGAJUKAN PENGADUAN BARU'), 200);
            }
        }else{
            $this->response(array('errors' => array('global' => 'GAGAL MENGAJUKAN PENGADUAN')), 400);
        }
    
    }

    public function getTiket_post(){
        $nopend     = $this->post('nopend');
        $sql2       = "SELECT a.no_tiket,c.name AS channel_aduan ,b.name_requester as pelanggan, a.asal_pengaduan ,
						COUNT(a.tujuan_pengaduan) as tujuan_pengaduan , d.name as status , e.name AS statusRead, a.tgl_tambah, TIMESTAMPDIFF(HOUR, a.tgl_tambah, a.tgl_exp) as hours
						FROM tiket a
						LEFT JOIN pelanggan b ON a.cust_id = b.cust_id
						LEFT JOIN channel_info c ON b.type_request = c.auto_id
						LEFT JOIN ticket_status d ON a.status = d.id 
						LEFT JOIN statusRead e ON a.status_baca = e.id 
						WHERE a.asal_pengaduan = ? 
						GROUP BY a.no_tiket,c.name,b.name_requester, a.asal_pengaduan ,d.name, e.name, a.tgl_tambah, a.tgl_exp
						ORDER BY a.tgl_tambah DESC ";
        $sql3       = "SELECT a.no_tiket,c.name AS channel_aduan ,b.name_requester as pelanggan, a.asal_pengaduan ,
						COUNT(a.tujuan_pengaduan) as tujuan_pengaduan , d.name as status , e.name AS statusRead, a.tgl_tambah, TIMESTAMPDIFF(HOUR, a.tgl_tambah, a.tgl_exp) as hours
						FROM tiket a
						LEFT JOIN pelanggan b ON a.cust_id = b.cust_id
						LEFT JOIN channel_info c ON b.type_request = c.auto_id
						LEFT JOIN ticket_status d ON a.status = d.id 
						LEFT JOIN statusRead e ON a.status_baca = e.id 
						WHERE a.tujuan_pengaduan = ? 
						GROUP BY a.no_tiket,c.name,b.name_requester, a.asal_pengaduan ,d.name, e.name, a.tgl_tambah, a.tgl_exp
						ORDER BY a.tgl_tambah DESC ";
        $query2     = $this->db->query($sql2, array($nopend));
        $query3     = $this->db->query($sql3, array($nopend));
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

    private function getnosi($noTicket){
        $this->db->select('no_resi');
        $this->db->from('ticket_response');
        $this->db->where('ticket_id', $noTicket);
        $this->db->limit('1');
        $query = $this->db->get()->row_array();
        return $query['no_resi'];
    }

    public function detailTiket_post(){
    	$noTicket 	= $this->post('noTicket');
    	$noresi 	= $this->getnosi($noTicket);
    	$sql 		="SELECT  a.no_tiket, b.name_requester , b.address , b.phone , a.awb , a.jenis_layanan , c.name as jenis_kiriman,
						d.name as status , e.email  as pembuatanTicket,e.title as fullname, z.nama_aduan,
                        (select CONCAT(code,' - ', name) from office where code = a.asal_pengaduan) as asal_pengaduan_name, a.asal_pengaduan
						FROM tiket a
                        INNER JOIN ref_aduan z ON a.jenis_aduan = z.id
						LEFT JOIN pelanggan b ON a.cust_id = b.cust_id 
						LEFT JOIN ref_kiriman c ON a.jenis_kiriman = c.id 
						LEFT JOIN ticket_status d ON a.status = d.id 
						LEFT JOIN sys_user e ON a.user_cch = e.email 
						WHERE a.no_tiket = '$noTicket'";
		$sql2 		= "SELECT a.response, a.date, a.user_cch as username,b.title as fullname, a.file_name, b.file_name as photoProfile, 
                        (SELECT fullname FROM office WHERE code = b.kantor_pos) as kantor_pos, 
                        a.lacak_value, c.name as status_tiket
						FROM ticket_response a
						LEFT JOIN sys_user b ON a.user_cch = b.email
                        INNER JOIN ticket_status c on a.status = c.id
						WHERE a.no_resi = '$noresi' 
						ORDER BY `date` DESC";
        $sql3       = "SELECT (select CONCAT(code,' - ', name) from office where code = a.tujuan_pengaduan) as tujuan_pengaduan FROM tiket a WHERE a.no_tiket = ?";
		$query 		= $this->db->query($sql, array($noTicket));
        $query2     = $this->db->query($sql2, array($noTicket));
		$query3		= $this->db->query($sql3, array($noTicket));

		// $arrayName = array();
		// $updateRead = array(
		// 	'status_read' => '2'
		// );
		// 	$this->db->where('no_ticket', $noTicket);
		// 	$this->db->update('ticket', $updateRead);
		if ($query->num_rows() > 0 ) {
			$detailTicket = $query->row_array();
			if ($query2->num_rows() > 0) {
				$resTicket = $query2->result_array();
                if ($query3->num_rows() > 0) {
                    $tujuan     = $query3->result_array();
                    $detailnya  = [];
                    if ($this->db->affected_rows() > 0) {
                        $detailnya = $detailTicket;
                        $detailnya['tujuan'] = $tujuan;
                       $this->response(array('detailTicket' => $detailnya, 'notes' => $resTicket), 200);     
                    } else {
                        $this->response(array('detailTicket' => $detailTicket,'notes' => $resTicket, 'tujuan_pengaduan' => $tujuan), 200);  
                    }
                }else{
					$this->response(array('detailTicket' => $detailTicket,'notes' => $resTicket, 'tujuan_pengaduan' => $tujuan), 200);  
				}
			} else {
				$this->response(array("errors" => 'GAGAL MEMUAT DATA DETAIL TIKET'), 400);
			}
		} else {
			$this->response(array("errors" => 'TIKET TIDAK DITEMUKAN '), 400);
		}
    }

    public function responseTiket_post(){
        $noTicket       = $this->post('noTicket');
        $username       = $this->post('user');
        $response       = $this->post('response');
        $status         = $this->post('status');
        $noresi         = $this->post('no_resi');
        $date           = $this->getCurdate();

        $upload      = array(
            'ticket_id'         => $noTicket,
            'response'          => $response,
            'user_cch'          => $username,
            'date'              => $date,
            'status'            => $status,
            'no_resi'           => $noresi
        );

        $updateRead = array(
            'lastupdate'=> $this->getCurdate(),
            'status'    => $status,
            'status_balas' => '2'
        );


        $config['upload_path']          = './assets/';
        $config['allowed_types']        = '*';
        $config['encrypt_name']         = TRUE;

        $this->load->library('upload', $config);
        $this->upload->initialize($config);
        $do_upload = $this->upload->do_upload('file');


        if (!$do_upload) {
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
        } else {
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

    public function realtimeResponse_post(){
        $noTicket   = $this->post('noTicket');
        $noresi     = $this->getnosi($noTicket);
        $sql        = "SELECT a.response, a.date, a.user_cch as username, b.title as fullname , a.file_name, b.file_name as photoProfile, 
                        (select fullname from office where code = b.kantor_pos ) as kantor_pos, a.lacak_value,
                        (select name from ticket_status where id = a.status) as status_tiket
						FROM ticket_response a
						LEFT JOIN sys_user b ON a.user_cch = b.email
						WHERE a.no_resi = ? 
						ORDER BY `date` DESC";
        $query      = $this->db->query($sql, array($noresi));
        if ($query->num_rows() > 0 ) {
            $detailTicket = $query->result_array();
            $this->response(array('notes' => $detailTicket), 200);
        } else {
            $this->response(array("errors" => 'TIKET TIDAK DITEMUKAN '), 400);
        }
    }


    public function cektiket_post(){
        $awb = $this->post('awb');
        $this->db->select('a.no_tiket, b.name as status');
        $this->db->FROM('tiket a');
        $this->db->join('ticket_status b', 'a.status = b.id');
        $this->db->WHERE('awb', $awb);
        $query = $this->db->get();
        if ($query->num_rows() > 0 ) {
            $data = $query->row_array();
            $this->response(array(
                'no_tiket'      => $data['no_tiket'],
                'status'        => $data['status'],
                'keterangan'    => 'No resi sudah ada dengan nomor tiket '. $data['no_tiket'] . '('.$data['status'].')')
            , 200);    
        } else {
            $this->response(array(
                'keterangan'    => '')
            , 400);
        }
    }

//PRIVATE FUNCTION
    private function getCurdate(){
        $sql    = "SELECT now() as sekarang";
        $now    = $this->db->query($sql)->row_array();
        $now    = $now['sekarang'];
        return $now;
    }

    private function getTime(){
        $sql    = "SELECT CURTIME() as sekarang";
        $now    = $this->db->query($sql)->row_array();
        $now    = $now['sekarang'];
        return $now;
    }

    private function getLibur($nopend){
    	$sql    = "SELECT date_start FROM holiday where office in (?, '00000')";
        $date   = $this->db->query($sql, array($nopend))->result_array();
        $result = array();
        
        if (count($date) > 0) {
            foreach ( $date as $key => $val ){
                $temp = array_values($val);
                $result[] = $temp[0];
            }
        } else {
            $sql    = "SELECT date_start FROM holiday where office = '00000' ";
            $date   = $this->db->query($sql)->result_array();
            $result = array();

            foreach ( $date as $key => $val ){
                $temp = array_values($val);
                $result[] = $temp[0];
            }
        }
        
        return $result;
    }

    //3 nopend awal-mm-yy-3 digit no urut
    //medan --> 200-10-20-0001
    private function getIdTicket(){
        $this->db->select("RIGHT(no_tiket, 3) as auto, DATE_FORMAT(now(), '%m%y') as curdate");
        $this->db->from('tiket');
        $this->db->where("SUBSTRING(no_tiket, 4, 4) = DATE_FORMAT(now(), '%m%y')");
        $this->db->order_by('auto', 'DESC');
        $this->db->limit(1);
        $query = $this->db->get();

        if ($query->num_rows() > 0) {
            $data   = $query->row_array();
            $no     = $data['auto'] + 1;
            $no     = str_pad($no, 3, "0", STR_PAD_LEFT);
            $id     = "".$data['curdate']."".$no."";
            return $id;
        }else{
            $curdate = $this->db->select("DATE_FORMAT(now(), '%m%y') as curdate")->get()->row_array();
            $no     = '001';
            $no     = str_pad($no, 3, "0", STR_PAD_LEFT);
            $id     = "".$curdate['curdate']."".$no."";
            return $id;
        }
    }


    public function closeTiket_post(){
        $notiket    = $this->post('notiket');
        $masalah    = $this->post('lokusMasalah');
        $username   = $this->post('user');
        $status     = $this->post('status');
        $noresi     = $this->post('no_resi');
        $date           = $this->getCurdate();

        $this->db->where('no_tiket', $notiket);
        $this->db->update('tiket', array('status' => '99'));
        if ($this->db->affected_rows() > 0) {
            $insert = array(
                'no_ticket'     => $notiket,
                'lokus_masalah' => $masalah
            );

            $upload      = array(
                'ticket_id'         => $notiket,
                'response'          => 'Tiket selesai dengan lokus masalah :('.$masalah.')',
                'user_cch'          => $username,
                'date'              => $date,
                'status'            => $status,
                'no_resi'           => $noresi
            );

            $this->db->insert('ticket_selesai', $insert);
            if ($this->db->affected_rows() > 0) {
                $this->db->insert('ticket_response', $upload);
                $this->response(array('status' => 200, 'msg' => 'SUKSES TUTUP TIKET'), 200);
            }else{
                $this->response(array('status' => 400, 'msg' => 'GAGAL INSERT STATUS'), 400);
            }
        }else{
            $this->response(array('status' => 400, 'msg' => 'TIKET TIDAK DITEMUKAN'), 400);
        }
    }

    public function updateStatusRead_post(){
        $tiket = $this->post('notiket');
        $this->db->where('no_tiket', $tiket);
        $this->db->update('tiket', array('status_baca' => '3'));
        if ($this->db->affected_rows() > 0) {
            $this->response(array('status' => 'Oke'), 200);
        }else{
            $this->response(array('status' => 'Not oke'), 400);
        }
    }


}


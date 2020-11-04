<?php

use Restserver\Libraries\REST_Controller;
require(APPPATH.'/libraries/REST_Controller.php');

/**
 * 
 */
class xray extends REST_Controller{

	public function index_post(){
		$sql  = "SELECT * FROM xray ORDER BY tgl_input DESC";
		$query  = $this->db->query($sql);
        if ($query->num_rows() > 0) {
            $listLibur = $query->result_array();
            $this->response($listLibur, 200);
        } else {
            $this->response(array("errors" => 'FAILED GET LIST STATUS'), 400);
        }
	}
	
	public function uploadXray_post(){
		$upload      = array();

		$config['upload_path']          = './assets/excel/';
        $config['allowed_types']        = 'xlsx';

        $this->load->library('upload', $config);
        $this->upload->initialize($config);

        if (!$this->upload->do_upload('file')) {
        	 $error = $this->upload->display_errors();
            $this->response(array('status' => 400, 'msg' => $error), 400);
        }else{
        	$file_data = $this->upload->data();
            $data['file_name'] = $this->upload->data('file_name');
            $this->db->insert('upload', $data);
            if ($this->db->affected_rows() > 0) {
            	include APPPATH.'third_party/PHPExcel/PHPExcel.php';
		
				$excelreader = new PHPExcel_Reader_Excel2007();
				$loadexcel = $excelreader->load('assets/excel/'. $data['file_name']); 
				$sheet = $loadexcel->getActiveSheet()->toArray(null, true, true ,true);
				$data = array();
				
				$numrow = 1;
				foreach($sheet as $row){
					if($numrow > 1){
						array_push($data, array(
							'kode_kantor_aduan' =>$row['A'],
							'kantor_aduan' =>$row['B'],
							'tgl_input' =>$row['C'],
							'kode_kantor_asal' =>$row['D'],
							'kantor_asal' =>$row['E'],
							'kode_kantor_tujuan' =>$row['F'],
							'kantor_tujuan' =>$row['G'],
							'id_kiriman' =>$row['H'],
							'isi_kiriman' =>$row['I'],
							'kantong_lama' =>$row['J'],
							'kantong_baru' =>$row['K'],
							'berat' =>$row['L'],
							'keterangan' =>$row['M'],
							'user_cch' =>$row['N'],

						));
					}
					
					$numrow++; // Tambah 1 setiap kali looping
				}
				$this->db->insert_batch('xray', $data); 
		    	if ($this->db->affected_rows() > 0) {
					$this->response(array('status' => 200, 'result' => 'SUKSES TAMBAH HARI LIBUR' ), 200);
		        }else{
		            $this->response(array('errors' => array('global' => 'GAGAL TAMBAH HARI LIBUR')), 400);
		        }
            } 
        }
	}

	public function insertXray_post(){
		$value 	= $this->post('value'); //array
		$total = 0; 
		foreach ($value as $key) { //insert looping
			$validate = $this->validateXray($key['id_kiriman']);
			if ($validate) {
				$data = array(
					'kode_kantor_aduan' => $key['kode_kantor_aduan'],
					'kode_kantor_asal' => $key['kode_kantor_asal'],
					'kode_kantor_tujuan' => $key['kode_kantor_tujuan'],
					'id_kiriman' => $key['id_kiriman'],
					'isi_kiriman' => $key['isi_kiriman'],
					'kantong_lama' => $key['kantong_lama'],
					'kantong_baru' => $key['kantong_baru'],
					'berat' => $key['berat'],
					'keterangan' => $key['keterangan'],
					'user_cch' => $key['user_cch'],
					'tgl_input' => $this->getCurdate()
				);

				$this->db->insert('t_xray', $data);
				if ($this->db->affected_rows() > 0) {
					$total++;		
				}
			}
		}

		$this->response(array('result' => 'SUKSES UPLOAD', 'jumlah' => $total), 200);
	}

	public function postXray_post(){
		$berat 	= $this->post('berat');
		$kLama 	= $this->post('kLama');
		$id 	= $this->post('id');
		$isi 	= $this->post('isi');
		$kBaru 	= $this->post('kBaru');
		$aduan 	= $this->post('kantoraduan');
		$asal 	= $this->post('kantorasal');
		$tujuan = $this->post('kantortujuan');
		$user 	= $this->post('email');
		$desc 	= $this->post('description');

		$validate = $this->validateXray($id);
		if ($validate) {
			$this->db->insert('t_xray', array(
				'kode_kantor_aduan' => $aduan,
				'kode_kantor_asal' => $asal,
				'kode_kantor_tujuan' => $tujuan,
				'id_kiriman' => $id,
				'isi_kiriman' => $isi,
				'kantong_lama' => $kLama,
				'kantong_baru' => $kBaru,
				'berat' => $berat,
				'keterangan' => $desc,
				'user_cch' => $user,
				'tgl_input' => $this->getCurdate()
			));

			if ($this->db->affected_rows() > 0) {
				$this->response('oke', 200);
			}else{
				$this->response('Not oke', 400);
			}	
		}else{
			$this->response('Id kiriman sudah terdaftar', 400);
		}
	}

	private function validateXray($id){
		$this->db->select('id_kiriman');
		$this->db->from('t_xray');
		$this->db->where('id_kiriman', $id);
		$query = $this->db->get();
		if ($query->num_rows() > 0) {
			return false;
		}else{
			return true;
		}
	}

	private function getCurdate(){
		$sql    = "SELECT now() as sekarang";
        $now    = $this->db->query($sql)->row_array();
        $now    = $now['sekarang'];
        return $now;
	}

	public function getData_post(){
		$this->db->select('(SELECT name FROM office where code = a.kode_kantor_aduan) as kantor_aduan, a.kode_kantor_aduan');
		$this->db->select('(SELECT name FROM office where code = a.kode_kantor_tujuan) as kantor_tujuan, a.kode_kantor_tujuan');
		$this->db->select('(SELECT name FROM office where code = a.kode_kantor_asal) as kantor_asal, a.kode_kantor_asal');
		$this->db->select('a.id_kiriman, a.isi_kiriman, a.kantong_baru, a.kantong_lama, a.keterangan, a.berat');
		$this->db->select('DATE_FORMAT(a.tgl_input,"%Y-%m-%d") as tgl_input');
		$this->db->from('t_xray as a');
		
		if ($this->post('extid')) {
			$param = $this->post('extid');
            $this->db->group_start();
            $this->db->or_like('a.id_kiriman', $param);
            $this->db->group_end();
		}

		$this->db->order_by('a.tgl_input', 'DESC');

		$query = $this->db->get();
		if ($query->num_rows() > 0) {
			$this->response(array('status' => 200, 'result' => $query->result_array()), 200);
		}else{
			$this->response(array('status' => 404), 400);
		}
	}

	public function totalDetail_post(){
		$this->db->select('count(*) as jumlah');
		$this->db->from('t_xray');
		if ($this->post('query')) {
			$search = $this->post('query');
			$this->db->group_start();
		        $this->db->or_where('id_kiriman', $search);
		        $this->db->or_where('isi_kiriman', $search);
            $this->db->group_end();
		}
		$query = $this->db->get();

		$this->response($query->row_array(), 200);
	}


	public function fetchData_post(){
		$offset = $this->post('offset');
		$limit 	= $this->post('limit');

		$this->db->select("(SELECT CONCAT(code, ' - ', name) FROM office where code = a.kode_kantor_aduan) as kantor_aduan, a.kode_kantor_aduan");
		$this->db->select("(SELECT CONCAT(code, ' - ', name) FROM office where code = a.kode_kantor_tujuan) as kantor_tujuan, a.kode_kantor_tujuan");
		$this->db->select("(SELECT CONCAT(code, ' - ', name) FROM office where code = a.kode_kantor_asal) as kantor_asal, a.kode_kantor_asal");
		$this->db->select('a.id_kiriman, a.isi_kiriman, a.kantong_baru, a.kantong_lama, a.keterangan, a.berat');
		$this->db->select('DATE_FORMAT(a.tgl_input,"%Y-%m-%d") as tgl_input');
		$this->db->from('t_xray as a');

		if ($this->post('query')) {
			$search = $this->post('query');
			$this->db->group_start();
            $this->db->or_where('a.id_kiriman', $search);
            $this->db->or_where('a.isi_kiriman', $search);
            $this->db->group_end();
		}

		$this->db->order_by('a.tgl_input', 'DESC');
		$this->db->limit($limit, $offset);
		$query = $this->db->get();

		$this->response($query->result_array(), 200);
	}

	public function getDetailReg_post(){
		$reg 		= $this->post('reg');
		$startdate 	= $this->post('startdate');
		$type 		= $this->post('type');
		$enddate 	= $this->post('enddate');

		$this->db->select("(SELECT CONCAT(code, ' - ', name) FROM office where code = a.kode_kantor_aduan) as kantor_aduan, a.kode_kantor_aduan");
		$this->db->select("(SELECT CONCAT(code, ' - ', name) FROM office where code = a.kode_kantor_tujuan) as kantor_tujuan, a.kode_kantor_tujuan");
		$this->db->select("(SELECT CONCAT(code, ' - ', name) FROM office where code = a.kode_kantor_asal) as kantor_asal, a.kode_kantor_asal");
		$this->db->select('a.id_kiriman, a.isi_kiriman, a.kantong_baru, a.kantong_lama, a.keterangan, a.berat');
		$this->db->select('DATE_FORMAT(a.tgl_input,"%Y-%m-%d") as tgl_input');
		$this->db->from('t_xray as a');
		if ($type === '1') { //asal kiriman
			$this->db->join('office b', 'a.kode_kantor_asal = b.code');	
		}else{ //tujuan kiriman
			$this->db->join('office b', 'a.kode_kantor_tujuan = b.code');	
		}
		$this->db->join('ref_wilayah c', 'b.kd_wilayah = c.id');
		$this->db->where('c.wilayah', $reg);
		$this->db->where("DATE_FORMAT(a.tgl_input, '%Y-%m-%d') >=", $startdate);
		$this->db->where("DATE_FORMAT(a.tgl_input, '%Y-%m-%d') <=", $enddate);

		$this->db->order_by('a.tgl_input', 'DESC');
		$query = $this->db->get()->result_array();
		$this->response($query, 200);
	}

	public function getAllowedOffice_post(){
		$this->db->select('code');
		$this->db->from('office');
		$this->db->where('xray_allowed', 1);

		$query = $this->db->get()->result_array();
		$this->response($query, 200);
	}

	public function getListOffice_post(){
		$this->db->select('code, name, regional, xray_allowed');
		$this->db->from('office');
		$this->db->where("code not in ('00001','00002')");
		$this->db->order_by('regional', 'ASC');
		$this->db->order_by('code', 'ASC');
		$query = $this->db->get()->result_array();
		$this->response($query, 200);
	}

	public function addNewOffice_post(){
		$code = $this->post('code');
		$status = $this->post('allowed');
		$this->db->where('code', $code);
		$this->db->update('office', array('xray_allowed' => $status));
		if ($this->db->affected_rows() > 0) {
			$this->response('oke', 200);
		}else{
			$this->response('Not oke', 400);
		}
	}
}
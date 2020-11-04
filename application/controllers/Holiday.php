<?php

use Restserver\Libraries\REST_Controller;
require(APPPATH.'/libraries/REST_Controller.php');
/**
 * 
 */
class holiday extends REST_Controller{

	public function index_post(){
		$value = $this->post('value');
		$this->db->insert_batch('holiday', $value); 
    	if ($this->db->affected_rows() > 0) {
			$this->response(array('status' => 200, 'result' => 'SUKSES TAMBAH HARI LIBUR' ), 200);
        }else{
            $this->response(array('errors' => array('global' => 'GAGAL TAMBAH HARI LIBUR')), 400);
        }
	}

	public function uploadLibur_post(){
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
							'date_start' =>$row['A'],
							'description' =>$row['B'],
							'username' =>$row['C']

						));
					}
					
					$numrow++; // Tambah 1 setiap kali looping
				}
				$this->db->insert_batch('holiday', $data); 
		    	if ($this->db->affected_rows() > 0) {
					$this->response(array('status' => 200, 'result' => 'SUKSES TAMBAH HARI LIBUR' ), 200);
		        }else{
		            $this->response(array('errors' => array('global' => 'GAGAL TAMBAH HARI LIBUR')), 400);
		        }
            } 
        }
	}

	public function getData_post(){
		$offset = $this->post('offset');
		$this->db->select('date_start, username, description');
		$this->db->select("case when office = '00000' then 'Nasional' else (select fullname from office where code = office) END as nopend");
		$this->db->from('holiday');
		$this->db->limit(15, $offset);
		$this->db->order_by('date_start', 'DESC');
		$query = $this->db->get()->result_array();
		$this->response($query, 200);
	}

	public function getTotal_post(){
		$this->db->select('COUNT(*) as total');
		$this->db->from('holiday');
		$query = $this->db->get()->row_array();
		$this->response($query, 200);
	}

	public function getTotal(){
		$this->db->select('COUNT(*) as total');
		$this->db->from('holiday');
		$query = $this->db->get()->row_array();
		return $query['total'];
	}

	private function getInserted(){
		$this->db->select('date_start, username, description');
		$this->db->select("case when office = '00000' then 'Nasional' else (select fullname from office where code = office) END as nopend");
		$this->db->from('holiday');
		$this->db->limit(15, 0);
		$this->db->order_by('date_start', 'DESC');
		return $this->db->get()->result_array();	
	}

	 public function generate_post(){
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
            	$inserted = $this->getInserted();
            	$this->response(array(
            		'inserted' => $inserted,
            		'totalAll' => $this->getTotal()
            	), 200);
            }else{
                $this->response(array('errors' => array('global' => 'GAGAL GENERATE HARI LIBUR')), 400);
            }
        }else{
            $this->response(array('status' => 'false', 'keterangan' => 'TIDAK ADA HARI LIBUR' ),200);
        }
    }
}
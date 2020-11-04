<?php

use Restserver\Libraries\REST_Controller;
require(APPPATH.'/libraries/REST_Controller.php');

/**
 * 
 */
class Prod extends REST_Controller{

    public function index_post(){
        $this->db->select('*');
        $this->db->from('prod_knowledge');
        if ($this->post('query')) {
            $param = $this->post('query');
            $this->db->group_start();
            $this->db->or_like('title', $param);
            $this->db->group_end();
        }
        $this->db->order_by('insert_date', 'desc');
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            $listLibur = $query->result_array();
            $this->response($listLibur, 200);
        } else {
            $this->response(array("errors" => 'FAILED GET LIST STATUS'), 400);
        }
    }
    
    public function uploadKnowledge_post(){
        $upload      = array();

        $config['upload_path']          = './assets/knowledge/';
        $config['allowed_types']        = '*';
        $config['encrypt_name']         = TRUE;
        $config['remove_spaces']        = TRUE;

        $this->load->library('upload', $config);
        $this->upload->initialize($config);
        $description = $this->post('description');
        $title   = $this->post('title');

        if (!$this->upload->do_upload('file')) {
             $error = $this->upload->display_errors();
            $this->response(array('status' => 400, 'msg' => $error), 400);
        }else{
            $file_data  = $this->upload->data();
            // $dataUpload = $this->upload->data('file_name');
            $data = array(
                'description' => $description,
                'title' => $title,
                'file_name' => $file_data['file_name']
            );
            $this->db->insert('prod_knowledge', $data); 
            if ($this->db->affected_rows() > 0) {
                $this->db->select('*');
                $this->db->from('prod_knowledge');
                $this->db->where('file_name', $file_data['file_name']);
                $inserted = $this->db->get();
                $this->response(array('status' => 200, 'result' =>  $inserted->row_array()), 200);
            }else{
                $this->response(array('errors' => array('global' => 'GAGAL')), 400);
            }
        }
    }

    public function deleteFile_post(){
        $file = $this->post('file');
        $this->db->where('file_name', $file);
        $this->db->delete('prod_knowledge');
        if ($this->db->affected_rows() > 0) {
            $this->response('oke', 200);
        }else{
            $this->response('not oke', 400);
        }
    }
}
<?php

use Restserver\Libraries\REST_Controller;
require(APPPATH.'/libraries/REST_Controller.php');

class faq extends REST_Controller{
	
	public function index_post(){
		$title = $this->post('title');
		$question = $this->post('question');
		$answer = $this->post('answer');

		$data = array(
			'answer' => $answer,
			'title' => $title,
			'question' => $question
		);

		$this->db->insert('faq', $data);
		if ($this->db->affected_rows() > 0) {
			$inserted = $this->getInserted($title);
			$this->response($inserted, 200);
        }else{
            $this->response(array('errors' => array('global' => 'GAGAL MENAMBAKAN FAQ')), 400);
        }
	}

	public function getData_post(){
		$this->db->select('question, answer, id, title');
		$this->db->from('faq');
		$this->db->where('status', '1');

		$query = $this->db->get()->result_array();

		$this->response($query, 200);
	}

	private function getInserted($title){
		$this->db->select('question, answer, id, title');
		$this->db->from('faq');
		$this->db->where('status', '1');
		$this->db->where('title', $title);

		return $this->db->get()->row_array();
	}

	private function getInsertedId($id){
		$this->db->select('question, answer, id, title');
		$this->db->from('faq');
		$this->db->where('id', $id);

		return $this->db->get()->row_array();
	}

	public function update_post(){
		$title = $this->post('title');
		$question = $this->post('question');
		$answer = $this->post('answer');
		$id = $this->post('id');

		$this->db->where('id', $id);
		$this->db->update('faq', array(
			'question' => $question,
			'answer' => $answer,
			'title' => $title
		));

		if ($this->db->affected_rows() > 0) {
			$inserted = $this->getInsertedId($id);
			$this->response($inserted, 200);
		}else{
			$this->response(array('errors' => array('global' => 'GAGAL UPDATE FAQ')), 400);			
		}
	}
}
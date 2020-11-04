<?php

use Restserver\Libraries\REST_Controller;
require(APPPATH.'/libraries/REST_Controller.php');

class Testing extends REST_Controller{
	function __construct(){
		parent::__construct();
		$this->load->helper("url");
        $this->db2 = $this->load->database('history', TRUE);
    
	}

	public function getOrder_post(){
		$this->db2->select('*');
		$this->db2->from('preorder2016_order');
		$this->db2->limit(10);

		$query = $this->db2->get();

		$this->response(array('result' => $query->result_array()), 200);
	}
}

?>
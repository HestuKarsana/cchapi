<?php

use Restserver\Libraries\REST_Controller;
require(APPPATH.'/libraries/REST_Controller.php');

class cekhari extends REST_Controller{

	public function index_post(){
		$tmpDate 	= $this->getCurdate();
		$tgl 		= date('Y-m-d', strtotime($tmpDate));
		$holidays 	= $this->getLibur();

		$i = 1;
		$nextBusinessDay = date('Y-m-d', strtotime($tmpDate . ' +' . $i . ' Weekday'));
		$newDayOfWeek = date('w', strtotime($nextBusinessDay));

		while ( $newDayOfWeek > 0 && in_array($nextBusinessDay, $holidays)) {
		    $i++;
		    $nextBusinessDay = date('Y-m-d', strtotime($tmpDate . ' +' . $i . ' Weekday'));
		}

		$time = $this->getTime();
		$newDate = $nextBusinessDay.' '.$time;

		$this->response(array('status' => 200, 'tgl_exp' => $newDate ), 200);
	}

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

    private function getLibur(){
    	$sql    = "SELECT date_start FROM holiday";
        $date   = $this->db->query($sql)->result_array();
        $result = array();

        foreach ( $date as $key => $val ){
		    $temp = array_values($val);
		    $result[] = $temp[0];
		}
        return $result;
    }
}


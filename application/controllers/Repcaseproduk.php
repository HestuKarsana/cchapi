<?php 

use Restserver\Libraries\REST_Controller;
require(APPPATH.'/libraries/REST_Controller.php');

class repcaseproduk extends REST_Controller{
	
	public function index_post(){
		$reg 		= $this->post('regional');
		$kprk 		= $this->post('kprk');
		$startdate 	= $this->post('startdate');
		$enddate 	= $this->post('enddate'); 

		$produk 	= $this->produk($startdate, $enddate, $reg, $kprk);
		$masalah 	= $this->masalah($startdate, $enddate, $reg, $kprk);
		$lokus 		= $this->lokus($startdate, $enddate, $reg, $kprk);

		$this->response(array(
			'produk' => $produk, 
			'aduan' => $lokus,
			'masalah' => $masalah
		), 200);
	}

	private function produk($startdate, $enddate, $reg, $kprk){
		$this->db->select('a.kd_layanan ,a.nama_layanan , COUNT(DISTINCT b.no_tiket) as jml');
		$this->db->from('ref_layanan a');
		$this->db->join('tiket b', 'a.kd_layanan = b.jenis_layanan', 'left', null);
		$this->db->join('office c', 'b.asal_pengaduan = c.code', 'left', null);
	
		if ($reg == '00') { //all
			
		}else if($reg == '02'){ //all regional
			$this->db->where("c.regional <> 'KANTORPUSAT'", null, false);
		}else if($reg == '01'){ //pusat
			if ($kprk == '00') {
				$this->db->where_in('c.code', array('00001','00002', '40005'));
			}else{
				$this->db->where('c.code', $kprk);
			}
		}else{
			if ($kprk == '00') {
				$this->db->where('c.regional', $reg);
			}else{
				$this->db->where('c.code', $kprk);
			}
		}

		$this->db->where("DATE_FORMAT(b.tgl_tambah, '%Y-%m-%d') >= '$startdate'");
		$this->db->where("DATE_FORMAT(b.tgl_tambah, '%Y-%m-%d') <= '$enddate' OR (a.status = '1' AND b.no_tiket is null)", null, false);
		
		$this->db->GROUP_BY('a.kd_layanan , a.nama_layanan ');
		$this->db->ORDER_BY('a.kd_layanan','ASC');
		$query = $this->db->get()->result_array();
		return $query;
	}

	private function lokus($startdate, $enddate, $reg, $kprk){
		$this->db->select('a.id, a.nama_aduan, count(b.no_tiket) as jml');
		$this->db->from('ref_aduan a');
	
		if ($reg == '00') { //all
			$this->db->join("(
				select no_tiket, asal_pengaduan, jenis_aduan
				FROM tiket t 
				where DATE_FORMAT(tgl_tambah, '%Y-%m-%d') BETWEEN '$startdate' AND '$enddate'
				group by no_tiket, asal_pengaduan, jenis_aduan
			) b", "b.jenis_aduan = a.id", "LEFT", null);
		}else if($reg == '02'){ //all regional
			$this->db->join("(
				select no_tiket, asal_pengaduan, jenis_aduan
				FROM tiket t, office b 
				where t.asal_pengaduan = b.code 
				AND DATE_FORMAT(tgl_tambah, '%Y-%m-%d') BETWEEN '$startdate' AND '$enddate'
				AND b.regional <> 'KANTORPUSAT'
				group by no_tiket, asal_pengaduan, jenis_aduan
			) b", "b.jenis_aduan = a.id", "LEFT", null);
		}else if($reg == '01'){ //pusat
			if ($kprk == '00') {
				$this->db->join("(
					select no_tiket, asal_pengaduan, jenis_aduan
					FROM tiket t, office b 
					where t.asal_pengaduan = b.code 
					AND DATE_FORMAT(tgl_tambah, '%Y-%m-%d') BETWEEN '$startdate' AND '$enddate'
					AND b.code in ('00001','00002', '40005')
					group by no_tiket, asal_pengaduan, jenis_aduan
				) b", "b.jenis_aduan = a.id", "LEFT", null);
			}else{
				$this->db->join("(
					select no_tiket, asal_pengaduan, jenis_aduan
					FROM tiket t, office b 
					where t.asal_pengaduan = b.code 
					AND DATE_FORMAT(tgl_tambah, '%Y-%m-%d') BETWEEN '$startdate' AND '$enddate'
					AND b.code = '$kprk'
					group by no_tiket, asal_pengaduan, jenis_aduan
				) b", "b.jenis_aduan = a.id", "LEFT", null);
			}
		}else{
			if ($kprk == '00') {
				$this->db->join("(
					select no_tiket, asal_pengaduan, jenis_aduan
					FROM tiket t, office b 
					where t.asal_pengaduan = b.code 
					AND DATE_FORMAT(tgl_tambah, '%Y-%m-%d') BETWEEN '$startdate' AND '$enddate'
					AND b.regional = '$reg'
					group by no_tiket, asal_pengaduan, jenis_aduan
				) b", "b.jenis_aduan = a.id", "LEFT", null);
			}else{
				$this->db->join("(
					select no_tiket, asal_pengaduan, jenis_aduan
					FROM tiket t, office b 
					where t.asal_pengaduan = b.code 
					AND DATE_FORMAT(tgl_tambah, '%Y-%m-%d') BETWEEN '$startdate' AND '$enddate'
					AND b.code = '$kprk'
					group by no_tiket, asal_pengaduan, jenis_aduan
				) b", "b.jenis_aduan = a.id", "LEFT", null);
			}
		}

		$this->db->GROUP_BY('a.id , a.nama_aduan');
		$this->db->ORDER_BY('a.id','ASC');
		$query = $this->db->get()->result_array();
		return $query;
	}

	private function masalah($startdate, $enddate, $reg, $kprk){
		$this->db->select("a.nama_lokus_masalah, COUNT(DISTINCT b.no_tiket) as jumlah");
		$this->db->from("ref_lokus a");

		if ($reg == '00') { //all
			$this->db->join("(
					select a.no_tiket, b.lokus_masalah, c.code
					from tiket a
					left join ticket_selesai b on a.no_tiket = b.no_ticket
					inner join office c on a.asal_pengaduan = c.code
					where DATE_FORMAT(a.tgl_tambah, '%Y-%m-%d') BETWEEN '".$startdate."' AND '".$enddate."'
				) b", "a.nama_lokus_masalah = b.lokus_masalah", "left", null);
		}else if($reg == '02'){ //all regional
			$this->db->join("(
					select a.no_tiket, b.lokus_masalah, c.code
					from tiket a
					left join ticket_selesai b on a.no_tiket = b.no_ticket
					inner join office c on a.asal_pengaduan = c.code
					where c.regional <> 'KANTORPUSAT' AND DATE_FORMAT(a.tgl_tambah, '%Y-%m-%d') BETWEEN '".$startdate."' AND '".$enddate."'
				) b", "a.nama_lokus_masalah = b.lokus_masalah", "left", null);
		}else if($reg == '01'){ //pusat
			if ($kprk == '00') {
				$this->db->join("(
					select a.no_tiket, b.lokus_masalah, c.code
					from tiket a
					left join ticket_selesai b on a.no_tiket = b.no_ticket
					inner join office c on a.asal_pengaduan = c.code
					where c.code IN ('00001','00002', '40005') AND DATE_FORMAT(a.tgl_tambah, '%Y-%m-%d') BETWEEN '".$startdate."' AND '".$enddate."'
				) b", "a.nama_lokus_masalah = b.lokus_masalah", "left", null);
			}else{
				$this->db->join("(
					select a.no_tiket, b.lokus_masalah, c.code
					from tiket a
					left join ticket_selesai b on a.no_tiket = b.no_ticket
					inner join office c on a.asal_pengaduan = c.code
					where c.code = '$kprk' AND DATE_FORMAT(a.tgl_tambah, '%Y-%m-%d') BETWEEN '".$startdate."' AND '".$enddate."'
				) b", "a.nama_lokus_masalah = b.lokus_masalah", "left", null);
			}
		}else{
			if ($kprk == '00') {
				$this->db->join("(
					select a.no_tiket, b.lokus_masalah, c.code
					from tiket a
					left join ticket_selesai b on a.no_tiket = b.no_ticket
					inner join office c on a.asal_pengaduan = c.code
					where c.regional = '".$reg."' AND DATE_FORMAT(a.tgl_tambah, '%Y-%m-%d') BETWEEN '".$startdate."' AND '".$enddate."'
				) b", "a.nama_lokus_masalah = b.lokus_masalah", "left", null);
			}else{
				$this->db->join("(
					select a.no_tiket, b.lokus_masalah, c.code
					from tiket a
					left join ticket_selesai b on a.no_tiket = b.no_ticket
					inner join office c on a.asal_pengaduan = c.code
					where c.code = '$kprk' AND DATE_FORMAT(a.tgl_tambah, '%Y-%m-%d') BETWEEN '".$startdate."' AND '".$enddate."'
				) b", "a.nama_lokus_masalah = b.lokus_masalah", "left", null);
			}
		}

		$this->db->GROUP_BY("a.nama_lokus_masalah");
		$query = $this->db->get()->result_array();
		return $query;
	}

	//masuk or keluar?
	public function getDetail_post(){
		$regional 	= $this->post('regional');
		$kprk 		= $this->post('kprk');
		$tipe 		= $this->post('tipe');
		$startdate 	= $this->post('startdate');
		$enddate 	= $this->post('enddate');

		$this->db->select("t.no_tiket, d.name as channel, c.wilayah, t.awb, e.name as status, t.tgl_tambah, t.tgl_exp, g.nama_layanan");
		$this->db->select("t.tujuan_pengaduan, t.asal_pengaduan");
		$this->db->select("IFNULL(f.tgl_selesai, now()) as tgl_done");
		$this->db->select("IFNULL(timestampdiff(DAY, t.tgl_exp, f.tgl_selesai),'-') as waktu_selesai");
		$this->db->select("IFNULL(timestampdiff(DAY, t.tgl_exp, f.tgl_selesai),0) as orderby");

		if ($tipe == 1) {
			$this->_queryDetailProduk($startdate, $enddate, $regional, $kprk);
		}else if ($tipe == 2) { //laporan aduan
			$this->_queryDetailAduan($startdate, $enddate, $regional, $kprk);
		}else if ($tipe == 3) {
			$this->_queryDetailLokus($startdate, $enddate, $regional, $kprk);
		}

		$this->db->order_by('orderby', 'ASC');
		$this->db->order_by('t.status', 'ASC');

		$query = $this->db->get()->result_array();
		$this->response($this->groupingByTujuan($query), 200);
	}

	private function _queryDetailProduk($startdate, $enddate, $reg, $kprk){
		$this->db->from("tiket t");
		$this->db->join("office b", "t.asal_pengaduan = b.code");
		$this->db->join("ref_wilayah c", "c.id = b.kd_wilayah");
		$this->db->join("channel_info d", "t.channel_aduan = d.id");
		$this->db->join("ticket_status e", "e.id = t.status");
		$this->db->join("ref_layanan g", "t.jenis_layanan = g.kd_layanan");
		$this->db->join("ticket_selesai f", "t.no_tiket= f.no_ticket", "LEFT", null);
		
		if ($reg == '00') { //all
			
		}else if($reg == '02'){ //all regional
			$this->db->where("b.regional <> 'KANTORPUSAT'", null, false);
		}else if($reg == '01'){ //pusat
			if ($kprk == '00') {
				$this->db->where_in('b.code', array('00001','00002', '40005'));
			}else{
				$this->db->where('b.code', $kprk);
			}
		}else{
			if ($kprk == '00') {
				$this->db->where('b.regional', $reg);
			}else{
				$this->db->where('b.code', $kprk);
			}
		}

		$this->db->where('g.nama_layanan', $this->post('layanan'));
		$this->db->where("DATE_FORMAT(t.tgl_tambah, '%Y-%m-%d') >=", $startdate);
		$this->db->where("DATE_FORMAT(t.tgl_tambah, '%Y-%m-%d') <=", $enddate);
	}

	private function _queryDetailAduan($startdate, $enddate, $reg, $kprk){
		$this->db->from("tiket t");
		$this->db->join("office b", "t.asal_pengaduan = b.code");
		$this->db->join("ref_wilayah c", "c.id = b.kd_wilayah");
		$this->db->join("channel_info d", "t.channel_aduan = d.id");
		$this->db->join("ticket_status e", "e.id = t.status");
		$this->db->join("ref_layanan g", "t.jenis_layanan = g.kd_layanan");
		$this->db->join("ticket_selesai f", "t.no_tiket= f.no_ticket", "LEFT", null);
		
		if ($reg == '00') { //all
			
		}else if($reg == '02'){ //all regional
			$this->db->where("b.regional <> 'KANTORPUSAT'", null, false);
		}else if($reg == '01'){ //pusat
			if ($kprk == '00') {
				$this->db->where_in('b.code', array('00001','00002', '40005'));
			}else{
				$this->db->where('b.code', $kprk);
			}
		}else{
			if ($kprk == '00') {
				$this->db->where('b.regional', $reg);
			}else{
				$this->db->where('b.code', $kprk);
			}
		}

		$this->db->where('t.jenis_aduan', $this->post('layanan'));
		$this->db->where("DATE_FORMAT(t.tgl_tambah, '%Y-%m-%d') >=", $startdate);
		$this->db->where("DATE_FORMAT(t.tgl_tambah, '%Y-%m-%d') <=", $enddate);
	}

	private function _queryDetailLokus($startdate, $enddate, $reg, $kprk){
		$this->db->from("tiket t");
		$this->db->join("office b", "t.asal_pengaduan = b.code");
		$this->db->join("ref_wilayah c", "c.id = b.kd_wilayah");
		$this->db->join("channel_info d", "t.channel_aduan = d.id");
		$this->db->join("ticket_status e", "e.id = t.status");
		$this->db->join("ref_layanan g", "t.jenis_layanan = g.kd_layanan");
		$this->db->join("ticket_selesai f", "t.no_tiket= f.no_ticket");
		
		if ($reg == '00') { //all
			
		}else if($reg == '02'){ //all regional
			$this->db->where("b.regional <> 'KANTORPUSAT'", null, false);
		}else if($reg == '01'){ //pusat
			if ($kprk == '00') {
				$this->db->where_in('b.code', array('00001','00002', '40005'));
			}else{
				$this->db->where('b.code', $kprk);
			}
		}else{
			if ($kprk == '00') {
				$this->db->where('b.regional', $reg);
			}else{
				$this->db->where('b.code', $kprk);
			}
		}

		$this->db->where('f.lokus_masalah', $this->post('layanan'));
		$this->db->where("DATE_FORMAT(t.tgl_tambah, '%Y-%m-%d') >=", $startdate);
		$this->db->where("DATE_FORMAT(t.tgl_tambah, '%Y-%m-%d') <=", $enddate);
	}

	private function groupingByTujuan($array){
		$new_tiket_list = array();
		for ($first_key = 0; $first_key < count($array); $first_key++) {
		    $tiket = $array[$first_key];
		    $current_notiket 	= $tiket['no_tiket']; // string
		    $current_tujuan 	= $tiket['tujuan_pengaduan']; // string

		    // looping untuk cek apakah ada notiket yang sama di $new_tiket_list
		    $same_tiket_found = 0;
		    for ($second_key = 0; $second_key < count($new_tiket_list); $second_key++) {
		        $new_tiket = $new_tiket_list[$second_key];
		        $new_notiket = $new_tiket['no_tiket']; // string
		        $new_tujuan = $new_tiket['tujuan_pengaduan']; // array

		        if ($new_notiket == $current_notiket) {
		            $same_tiket_found = 1;
		            array_push($new_tiket_list[$second_key]['tujuan_pengaduan'], $current_tujuan); // assign ke yang sudah ada
		        }
		    }

		    if ($same_tiket_found == 0) {
		        $new_tiket = [
		            'no_tiket' => $current_notiket,
		            'channel' => $tiket['channel'],
		            'asal_pengaduan' => $tiket['asal_pengaduan'],
		            'tujuan_pengaduan' => [$current_tujuan],
		            'status' => $tiket['status'],
		            'awb' => $tiket['awb'],
		            'waktu_selesai' => $tiket['waktu_selesai'],
		            'orderby' => $tiket['orderby'],
		            'tgl_exp' => $tiket['tgl_exp'],
		            'tgl_selesai' => $tiket['tgl_done'],
		            'tgl_tambah' => $tiket['tgl_tambah'],
		            'layanan' => $tiket['nama_layanan']
		        ];

		        array_push($new_tiket_list, $new_tiket);
		    }
		}

		return $new_tiket_list;
	}	
}
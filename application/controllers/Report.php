<?php

use Restserver\Libraries\REST_Controller;
require(APPPATH.'/libraries/REST_Controller.php');

class report extends REST_Controller{

    public function index_post(){
    	$type	      = $this->post("type");
      $reg        = $this->post('regional');
      $startdate  = $this->post('startdate');
      $enddate    = $this->post('enddate');
      $kprk       = $this->post('kprk');

      if ($type == '00') {//tiket masuk
        if ($reg == '00') { //all regional
          $this->allMasuk($startdate, $enddate);
        }else if($reg == '02'){
          $this->allMasukReg($startdate, $enddate);
        }else if($reg == '01'){
          $this->by_kprkMasuk($kprk, $startdate, $enddate, $reg);
        }else{ 
          if ($kprk == '00') { //all kprk
            $this->by_regionalMasuk($reg, $startdate, $enddate);
          }else{
            $this->by_kprkMasuk($kprk, $startdate, $enddate, '0000');
          }
        }
      }else{
      	if ($reg == '00') {
          $this->all($startdate, $enddate);
        }else if($reg =='02'){
          $this->allReg($startdate, $enddate);
        }else if($reg == '01'){
          $this->by_kprk($kprk, $startdate, $enddate, $reg);
        }else{
          if ($kprk == '00') {
            $this->by_regional($reg, $startdate, $enddate);
          }else{
            $this->by_kprk($kprk, $startdate, $enddate, '00000');
          }
        }
      }


      $query = $this->db->get()->result_array();
      $this->response($query, 200);
    }

    private function all($startdate, $enddate){ //all regional
        $this->db->select('a.id, a.wilayah as regional,
							sum(ifnull(c.jumlah, 0)) as hari1,
							sum(ifnull(d.jumlah, 0)) as hari2,
							sum(ifnull(e.jumlah, 0)) as hari3,
							sum(ifnull(f.jumlah, 0)) as hari4,
							ifnull(g.jumlah, 0) as tot_all');
        $this->db->from('ref_wilayah a');
        $this->db->join('office b', 'b on a.id = b.kd_wilayah');
        $this->db->join("(select asal_pengaduan, COUNT(DISTINCT no_tiket) as jumlah
                          from v_rekap_detail 
                          where day_in <= 1 AND periode between '".$startdate."' and '".$enddate."'
                          group by asal_pengaduan, periode
                          ) as  c" , 'b.code = c.asal_pengaduan', 'LEFT', NULL);
        $this->db->join("(select asal_pengaduan, COUNT(DISTINCT no_tiket) as jumlah
                          from v_rekap_detail 
                          where day_in > 1 AND day_in <= 2 AND periode between '".$startdate."' and '".$enddate."'
                          group by asal_pengaduan, periode
                        ) as d", 'b.code = d.asal_pengaduan', 'LEFT', NULL);
        $this->db->join("(select asal_pengaduan, COUNT(DISTINCT no_tiket) as jumlah
                          from v_rekap_detail 
                          where day_in > 2 AND day_in <= 3 AND periode between '".$startdate."' and '".$enddate."'
                          group by asal_pengaduan, periode
                        ) as e", 'b.code = e.asal_pengaduan', 'LEFT', NULL);
        $this->db->join("(select asal_pengaduan, COUNT(DISTINCT no_tiket) as jumlah
                          from v_rekap_detail 
                          where day_in > 3 AND periode between '".$startdate."' and '".$enddate."'
                          group by asal_pengaduan, periode
                        ) as f", 'b.code = f.asal_pengaduan', 'LEFT', NULL);
        $this->db->join("(SELECT COUNT(DISTINCT a.no_tiket) as jumlah, b.kd_wilayah
                          FROM tiket a inner join office b on a.asal_pengaduan = b.code
                          WHERE DATE_FORMAT(a.tgl_tambah, '%Y-%m-%d') between '".$startdate."' and '".$enddate."'
                          GROUP BY b.kd_wilayah) as g", 'g.kd_wilayah = a.id', 'LEFT', null);
        $this->db->group_by('a.id, a.wilayah, g.jumlah');
        $this->db->order_by('a.id', 'ASC');
    }

    private function allReg($startdate, $enddate){ //all regional
      $this->db->select('a.id, a.wilayah as regional,
            sum(ifnull(c.jumlah, 0)) as hari1,
            sum(ifnull(d.jumlah, 0)) as hari2,
            sum(ifnull(e.jumlah, 0)) as hari3,
            sum(ifnull(f.jumlah, 0)) as hari4,
            ifnull(g.jumlah, 0) as tot_all');
      $this->db->from('ref_wilayah a');
      $this->db->join('office b', 'b on a.id = b.kd_wilayah');
      $this->db->join("(select asal_pengaduan, COUNT(DISTINCT no_tiket) as jumlah
                        from v_rekap_detail 
                        where day_in <= 1 AND periode between '".$startdate."' and '".$enddate."'
                        group by asal_pengaduan, periode
                        ) as  c" , 'b.code = c.asal_pengaduan', 'LEFT', NULL);
      $this->db->join("(select asal_pengaduan, COUNT(DISTINCT no_tiket) as jumlah
                        from v_rekap_detail 
                        where day_in > 1 AND day_in <= 2 AND periode between '".$startdate."' and '".$enddate."'
                        group by asal_pengaduan, periode
                      ) as d", 'b.code = d.asal_pengaduan', 'LEFT', NULL);
      $this->db->join("(select asal_pengaduan, COUNT(DISTINCT no_tiket) as jumlah
                        from v_rekap_detail 
                        where day_in > 2 AND day_in <= 3 AND periode between '".$startdate."' and '".$enddate."'
                        group by asal_pengaduan, periode
                      ) as e", 'b.code = e.asal_pengaduan', 'LEFT', NULL);
      $this->db->join("(select asal_pengaduan, COUNT(DISTINCT no_tiket) as jumlah
                        from v_rekap_detail 
                        where day_in > 3 AND periode between '".$startdate."' and '".$enddate."'
                        group by asal_pengaduan, periode
                      ) as f", 'b.code = f.asal_pengaduan', 'LEFT', NULL);
      $this->db->join("(SELECT COUNT(DISTINCT a.no_tiket) as jumlah, b.kd_wilayah
                        FROM tiket a inner join office b on a.asal_pengaduan = b.code
                        WHERE DATE_FORMAT(a.tgl_tambah, '%Y-%m-%d') between '".$startdate."' and '".$enddate."'
                        GROUP BY b.kd_wilayah) as g", 'g.kd_wilayah = a.id', 'LEFT', null);
      $this->db->where("b.regional <> 'KANTORPUSAT'");
      $this->db->group_by('a.id, a.wilayah, g.jumlah');
      $this->db->order_by('a.id', 'ASC');
  }

    private function by_regional($reg, $startdate, $enddate){
        $this->db->select("b.code, CONCAT(b.code, ' - ', b.name) as regional, sum(ifnull(c.jumlah, 0)) as hari1,
							sum(ifnull(d.jumlah, 0)) as hari2,
							sum(ifnull(e.jumlah, 0)) as hari3,
							sum(ifnull(f.jumlah, 0)) as hari4,
							ifnull(g.jumlah, 0) as tot_all");
        $this->db->from('ref_wilayah a');
        $this->db->join('office b', 'b on a.id = b.kd_wilayah');
        $this->db->join("(select asal_pengaduan, COUNT(DISTINCT no_tiket) as jumlah
                          from v_rekap_detail 
                          where day_in <= 1 AND periode between '".$startdate."' and '".$enddate."'
                          group by asal_pengaduan, periode
                          ) as  c" , 'b.code = c.asal_pengaduan', 'LEFT', NULL);
        $this->db->join("(select asal_pengaduan, COUNT(DISTINCT no_tiket) as jumlah
                          from v_rekap_detail 
                          where day_in > 1 AND day_in <= 2 AND periode between '".$startdate."' and '".$enddate."'
                          group by asal_pengaduan, periode
                        ) as d", 'b.code = d.asal_pengaduan', 'LEFT', NULL);
        $this->db->join("(select asal_pengaduan, COUNT(DISTINCT no_tiket) as jumlah
                          from v_rekap_detail 
                          where day_in > 2 AND day_in <= 3 AND periode between '".$startdate."' and '".$enddate."'
                          group by asal_pengaduan, periode
                        ) as e", 'b.code = e.asal_pengaduan', 'LEFT', NULL);
        $this->db->join("(select asal_pengaduan, COUNT(DISTINCT no_tiket) as jumlah
                          from v_rekap_detail 
                          where day_in > 3 AND periode between '".$startdate."' and '".$enddate."'
                          group by asal_pengaduan, periode
                        ) as f", 'b.code = f.asal_pengaduan', 'LEFT', NULL);
        $this->db->join("(select asal_pengaduan, COUNT(DISTINCT no_tiket) as jumlah  FROM tiket WHERE DATE_FORMAT(tgl_tambah, '%Y-%m-%d') between '".$startdate."' and '".$enddate."'
                group by asal_pengaduan ) as g", 'g.asal_pengaduan = b.code ', 'LEFT', NULL);
        $this->db->where('b.regional', $reg);
        $this->db->group_by('a.id, a.wilayah, b.code, b.name, g.jumlah');
        $this->db->order_by('a.id', 'ASC');
    }

    private function by_kprk($kprk, $startdate, $enddate, $reg){
      $this->db->select("b.code , CONCAT(b.code, ' - ', b.name) as regional,sum(ifnull(c.jumlah, 0)) as hari1");
      $this->db->select("sum(ifnull(d.jumlah, 0)) as hari2, sum(ifnull(e.jumlah, 0)) as hari3, sum(ifnull(f.jumlah, 0)) as hari4");
      $this->db->select("ifnull(g.jumlah, 0) as tot_all");

      $this->db->from('ref_wilayah a');
      $this->db->join('office b', 'b on a.id = b.kd_wilayah');
      $this->db->join("(select asal_pengaduan, COUNT(DISTINCT no_tiket) as jumlah
                        from v_rekap_detail 
                        where day_in <= 1 AND periode between '".$startdate."' and '".$enddate."'
                        group by asal_pengaduan, periode
                        ) as  c" , 'b.code = c.asal_pengaduan', 'LEFT', NULL);
      $this->db->join("(select asal_pengaduan, COUNT(DISTINCT no_tiket) as jumlah
                        from v_rekap_detail 
                        where day_in > 1 AND day_in <= 2 AND periode between '".$startdate."' and '".$enddate."'
                        group by asal_pengaduan, periode
                      ) as d", 'b.code = d.asal_pengaduan', 'LEFT', NULL);
      $this->db->join("(select asal_pengaduan, COUNT(DISTINCT no_tiket) as jumlah
                        from v_rekap_detail 
                        where day_in > 2 AND day_in <= 3 AND periode between '".$startdate."' and '".$enddate."'
                        group by asal_pengaduan, periode
                      ) as e", 'b.code = e.asal_pengaduan', 'LEFT', NULL);
      $this->db->join("(select asal_pengaduan, COUNT(DISTINCT no_tiket) as jumlah
                        from v_rekap_detail 
                        where day_in > 3 AND periode between '".$startdate."' and '".$enddate."'
                        group by asal_pengaduan, periode
                      ) as f", 'b.code = f.asal_pengaduan', 'LEFT', NULL);
      $this->db->join("(select asal_pengaduan, COUNT(DISTINCT no_tiket) as jumlah  FROM tiket WHERE DATE_FORMAT(tgl_tambah, '%Y-%m-%d') between '".$startdate."' and '".$enddate."'
              group by asal_pengaduan ) as g", 'g.asal_pengaduan = b.code ', 'LEFT', NULL);
      if ($reg == '01') {
        if ($kprk == '00') {
          $this->db->where_in('b.code', array('00001', '00002','40005'));
        }else{
          $this->db->where('b.code', $kprk);
        }
      }else{
        $this->db->where('b.code', $kprk);
      }
      $this->db->group_by('b.code, b.name, g.jumlah');
    }

    private function allMasuk($startdate, $enddate){ //all regional
        $this->db->select('a.id, a.wilayah as regional,
							sum(ifnull(c.jumlah, 0)) as hari1,
							sum(ifnull(d.jumlah, 0)) as hari2,
							sum(ifnull(e.jumlah, 0)) as hari3,
							sum(ifnull(f.jumlah, 0)) as hari4,
							ifnull(g.jumlah, 0) as tot_all');
        $this->db->from('ref_wilayah a');
        $this->db->join('office b', 'b on a.id = b.kd_wilayah');
        $this->db->join("(select tujuan_pengaduan, COUNT(DISTINCT no_tiket) as jumlah
                          from v_rekap_detail_masuk 
                          where day_in <= 1 AND periode between '".$startdate."' and '".$enddate."'
                          group by tujuan_pengaduan, periode
                          ) as  c" , 'b.code = c.tujuan_pengaduan', 'LEFT', NULL);
        $this->db->join("(select tujuan_pengaduan, COUNT(DISTINCT no_tiket) as jumlah
                          from v_rekap_detail_masuk 
                          where day_in > 1 AND day_in <= 2 AND periode between '".$startdate."' and '".$enddate."'
                          group by tujuan_pengaduan, periode
                        ) as d", 'b.code = d.tujuan_pengaduan', 'LEFT', NULL);
        $this->db->join("(select tujuan_pengaduan, COUNT(DISTINCT no_tiket) as jumlah
                          from v_rekap_detail_masuk 
                          where day_in > 2 AND day_in <= 3 AND periode between '".$startdate."' and '".$enddate."'
                          group by tujuan_pengaduan, periode
                        ) as e", 'b.code = e.tujuan_pengaduan', 'LEFT', NULL);
        $this->db->join("(select tujuan_pengaduan, COUNT(DISTINCT no_tiket) as jumlah
                          from v_rekap_detail_masuk 
                          where day_in > 3 AND periode between '".$startdate."' and '".$enddate."'
                          group by tujuan_pengaduan, periode
                        ) as f", 'b.code = f.tujuan_pengaduan', 'LEFT', NULL);
        $this->db->join("(SELECT COUNT(DISTINCT a.no_tiket) as jumlah, b.kd_wilayah
                          FROM tiket a inner join office b on a.tujuan_pengaduan = b.code
                          WHERE DATE_FORMAT(a.tgl_tambah, '%Y-%m-%d') between '".$startdate."' and '".$enddate."'
                          GROUP BY b.kd_wilayah) as g", 'g.kd_wilayah = a.id', 'LEFT', null);
        $this->db->group_by('a.id, a.wilayah, g.jumlah');
        $this->db->order_by('a.id', 'ASC');
    }

    private function allMasukReg($startdate, $enddate){ //all regional
      $this->db->select('a.id, a.wilayah as regional,
            sum(ifnull(c.jumlah, 0)) as hari1,
            sum(ifnull(d.jumlah, 0)) as hari2,
            sum(ifnull(e.jumlah, 0)) as hari3,
            sum(ifnull(f.jumlah, 0)) as hari4,
            ifnull(g.jumlah, 0) as tot_all');
      $this->db->from('ref_wilayah a');
      $this->db->join('office b', 'b on a.id = b.kd_wilayah');
      $this->db->join("(select tujuan_pengaduan, COUNT(DISTINCT no_tiket) as jumlah
                        from v_rekap_detail_masuk 
                        where day_in <= 1 AND periode between '".$startdate."' and '".$enddate."'
                        group by tujuan_pengaduan, periode
                        ) as  c" , 'b.code = c.tujuan_pengaduan', 'LEFT', NULL);
      $this->db->join("(select tujuan_pengaduan, COUNT(DISTINCT no_tiket) as jumlah
                        from v_rekap_detail_masuk 
                        where day_in > 1 AND day_in <= 2 AND periode between '".$startdate."' and '".$enddate."'
                        group by tujuan_pengaduan, periode
                      ) as d", 'b.code = d.tujuan_pengaduan', 'LEFT', NULL);
      $this->db->join("(select tujuan_pengaduan, COUNT(DISTINCT no_tiket) as jumlah
                        from v_rekap_detail_masuk 
                        where day_in > 2 AND day_in <= 3 AND periode between '".$startdate."' and '".$enddate."'
                        group by tujuan_pengaduan, periode
                      ) as e", 'b.code = e.tujuan_pengaduan', 'LEFT', NULL);
      $this->db->join("(select tujuan_pengaduan, COUNT(DISTINCT no_tiket) as jumlah
                        from v_rekap_detail_masuk 
                        where day_in > 3 AND periode between '".$startdate."' and '".$enddate."'
                        group by tujuan_pengaduan, periode
                      ) as f", 'b.code = f.tujuan_pengaduan', 'LEFT', NULL);
      $this->db->join("(SELECT COUNT(DISTINCT a.no_tiket) as jumlah, b.kd_wilayah
                        FROM tiket a inner join office b on a.tujuan_pengaduan = b.code
                        WHERE DATE_FORMAT(a.tgl_tambah, '%Y-%m-%d') between '".$startdate."' and '".$enddate."'
                        GROUP BY b.kd_wilayah) as g", 'g.kd_wilayah = a.id', 'LEFT', null);
      $this->db->where("b.regional <> 'KANTORPUSAT'");
      $this->db->group_by('a.id, a.wilayah, g.jumlah');
      $this->db->order_by('a.id', 'ASC');
  }

    private function by_regionalMasuk($reg, $startdate, $enddate){
        $this->db->select("b.code , CONCAT(b.code, ' - ', b.name) as regional,
        					sum(ifnull(c.jumlah, 0)) as hari1,
							sum(ifnull(d.jumlah, 0)) as hari2,
							sum(ifnull(e.jumlah, 0)) as hari3,
							sum(ifnull(f.jumlah, 0)) as hari4,
							ifnull(g.jumlah, 0) as tot_all");
        $this->db->from('office b');
        $this->db->join("(select tujuan_pengaduan, COUNT(DISTINCT no_tiket) as jumlah
                          from v_rekap_detail_masuk 
                          where day_in <= 1 AND periode between '".$startdate."' and '".$enddate."'
                          group by tujuan_pengaduan, periode
                          ) as  c" , 'b.code = c.tujuan_pengaduan', 'LEFT', NULL);
        $this->db->join("(select tujuan_pengaduan, COUNT(DISTINCT no_tiket) as jumlah
                          from v_rekap_detail_masuk 
                          where day_in > 1 AND day_in <= 2 AND periode between '".$startdate."' and '".$enddate."'
                          group by tujuan_pengaduan, periode
                        ) as d", 'b.code = d.tujuan_pengaduan', 'LEFT', NULL);
        $this->db->join("(select tujuan_pengaduan, COUNT(DISTINCT no_tiket) as jumlah
                          from v_rekap_detail_masuk 
                          where day_in > 2 AND day_in <= 3 AND periode between '".$startdate."' and '".$enddate."'
                          group by tujuan_pengaduan, periode
                        ) as e", 'b.code = e.tujuan_pengaduan', 'LEFT', NULL);
        $this->db->join("(select tujuan_pengaduan, COUNT(DISTINCT no_tiket) as jumlah
                          from v_rekap_detail_masuk 
                          where day_in > 3 AND periode between '".$startdate."' and '".$enddate."'
                          group by tujuan_pengaduan, periode
                        ) as f", 'b.code = f.tujuan_pengaduan', 'LEFT', NULL);
        $this->db->join("(select tujuan_pengaduan, COUNT(DISTINCT no_tiket) as jumlah  FROM tiket WHERE DATE_FORMAT(tgl_tambah, '%Y-%m-%d') between '".$startdate."' and '".$enddate."'
                group by tujuan_pengaduan ) as g", 'g.tujuan_pengaduan = b.code ', 'LEFT', NULL);
        $this->db->where('b.regional', $reg);
        $this->db->group_by('b.code , b.name, g.jumlah');
        $this->db->order_by('b.code', 'ASC');
    }

    private function by_kprkMasuk($kprk, $startdate, $enddate, $reg){
        $this->db->select("b.code , CONCAT(b.code, ' - ', b.name) as regional,
                  sum(ifnull(c.jumlah, 0)) as hari1,
              sum(ifnull(d.jumlah, 0)) as hari2,
              sum(ifnull(e.jumlah, 0)) as hari3,
              sum(ifnull(f.jumlah, 0)) as hari4,
              ifnull(g.jumlah, 0) as tot_all");
        $this->db->from('office b');
        $this->db->join("(select tujuan_pengaduan, COUNT(DISTINCT no_tiket) as jumlah
                          from v_rekap_detail_masuk 
                          where day_in <= 1 AND periode between '".$startdate."' and '".$enddate."'
                          group by tujuan_pengaduan, periode
                          ) as  c" , 'b.code = c.tujuan_pengaduan', 'LEFT', NULL);
        $this->db->join("(select tujuan_pengaduan, COUNT(DISTINCT no_tiket) as jumlah
                          from v_rekap_detail_masuk 
                          where day_in > 1 AND day_in <= 2 AND periode between '".$startdate."' and '".$enddate."'
                          group by tujuan_pengaduan, periode
                        ) as d", 'b.code = d.tujuan_pengaduan', 'LEFT', NULL);
        $this->db->join("(select tujuan_pengaduan, COUNT(DISTINCT no_tiket) as jumlah
                          from v_rekap_detail_masuk 
                          where day_in > 2 AND day_in <= 3 AND periode between '".$startdate."' and '".$enddate."'
                          group by tujuan_pengaduan, periode
                        ) as e", 'b.code = e.tujuan_pengaduan', 'LEFT', NULL);
        $this->db->join("(select tujuan_pengaduan, COUNT(DISTINCT no_tiket) as jumlah
                          from v_rekap_detail_masuk 
                          where day_in > 3 AND periode between '".$startdate."' and '".$enddate."'
                          group by tujuan_pengaduan, periode
                        ) as f", 'b.code = f.tujuan_pengaduan', 'LEFT', NULL);
        $this->db->join("(select tujuan_pengaduan, COUNT(DISTINCT no_tiket) as jumlah  FROM tiket WHERE DATE_FORMAT(tgl_tambah, '%Y-%m-%d') between '".$startdate."' and '".$enddate."'
                group by tujuan_pengaduan ) as g", 'g.tujuan_pengaduan = b.code ', 'LEFT', NULL);
        if ($reg == '01') {
          if ($kprk == '00') {
            $this->db->where_in('b.code', array('00001', '00002','40005'));
          }else{
            $this->db->where('b.code', $kprk);
          }
        }else{
          $this->db->where('b.code', $kprk);
        }
        $this->db->group_by('b.code , b.name, g.jumlah');
    }
}


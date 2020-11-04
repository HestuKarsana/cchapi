<?php

use Restserver\Libraries\REST_Controller;
require(APPPATH.'/libraries/REST_Controller.php');

class dashboard extends REST_Controller{
    private function _queryKprk($type, $kprk){
        $this->db->select('code, regional, COUNT(DISTINCT no_tiket) as jumlah_tiket, status');
        $this->db->select("case 
                                when status = '0' then 'kurang dari 24'
                                when status is null then '-'
                                else 'lebih dari 24'
                            end as keterangan");
        
        if ($type === 1) {
            $this->db->from('v_pencapaian_masuk');       
        }else{
            $this->db->from('v_pencapaian_keluar');
        }

        $this->db->where('periode', $this->post('periode'));    
        $this->db->group_by(array('regional', 'code', 'status'));
        $this->db->where('code', $kprk);
    }

    private function _queryRegional($type, $regional){
        $this->db->select('regional, COUNT(DISTINCT no_tiket) as jumlah_tiket, status');
        $this->db->select("case 
                                when status = '0' then 'kurang dari 24'
                                when status is null then '-'
                                else 'lebih dari 24'
                            end as keterangan");
        if ($type === 1) {
            $this->db->from('v_pencapaian_masuk');    
        }else{
            $this->db->from('v_pencapaian_keluar');
        }

        $this->db->where('periode', $this->post('periode'));
        if ($regional === '02') {
            $this->db->where("regional <> 'KANTORPUSAT'");
        }else{
            $this->db->where('regional', $regional);
        }
        $this->db->group_by(array('regional','status'));
    }

    private function _queryPusat($type){

        $this->db->select('COUNT(DISTINCT no_tiket) as jumlah_tiket, status');
        $this->db->select("case 
                                when status = '0' then 'kurang dari 24'
                                when status is null then '-'
                                else 'lebih dari 24'
                            end as keterangan");
        if ($type === 1) {
            $this->db->from('v_pencapaian_masuk');     
        }else{
            $this->db->from('v_pencapaian_keluar');
        }

        $this->db->where('periode', $this->post('periode'));      
        $this->db->group_by(array('status'));
    }

    private function _queryCurrentPusat($type, $kprk){
        $this->db->select('code, regional, COUNT(DISTINCT no_tiket) as jumlah_tiket, status');
        $this->db->select("case 
                                when status = '0' then 'kurang dari 24'
                                when status is null then '-'
                                else 'lebih dari 24'
                            end as keterangan");
        
        if ($type === 1) {
            $this->db->from('v_pencapaian_masuk');
        }else{
            $this->db->from('v_pencapaian_keluar');
        }

        if ($kprk == '00') {
            $this->db->where_in('code', array('00001', '00002','40005'));
        }else{
            $this->db->where('code', $kprk);
        }

        $this->db->where('periode', $this->post('periode'));
        $this->db->group_by(array('regional', 'code', 'status'));
    }

    private function pencapaianMasuk($kprk, $regional){
        if ($regional == '00') {
            $this->_queryPusat(1);
        }else if($regional == '02'){
            $this->_queryRegional(1, $regional);   //not in pusat
        }else if($regional == 'KANTORPUSAT'){
            $this->_queryCurrentPusat(1, $kprk);
        }else{
            if ($kprk == '00') {
                $this->_queryRegional(1, $regional);   
            }else{
                $this->_queryKprk(1, $kprk);   
            }
        }

        $query = $this->db->get();
        
        $result   = array();

        if ($query) {
            foreach ($query->result_array() as $k) {
                if ($k['status'] === null) {
                    $result['lebih'] = 0;
                    $result['kurang'] = 0;
                }else if($k['status'] === '1'){
                    $result['lebih'] = (int)$k['jumlah_tiket'];
                }else{
                    $result['kurang'] = (int)$k['jumlah_tiket'];
                }
            }
        }else{
            $result['lebih'] = 0;
            $result['kurang'] = 0;
        }

        return $result;
    }

    private function pencapaianKeluar($kprk, $regional){
        if ($regional == '00') {
            $this->_queryPusat(2);
        }else if($regional == '02'){
            $this->_queryRegional(2, $regional);   
        }else if($regional == 'KANTORPUSAT'){
            $this->_queryCurrentPusat(2, $kprk);
        }else{
            if ($kprk == '00') {
                $this->_queryRegional(2, $regional);   
            }else{
                $this->_queryKprk(2, $kprk);   
            }
        }

        $query = $this->db->get();
        $result   = array();

        if ($query) {
            foreach ($query->result_array() as $k) {
                if ($k['status'] === null) {
                    $result['lebih'] = 0;
                    $result['kurang'] = 0;
                }else if($k['status'] === '1'){
                    $result['lebih'] = (int)$k['jumlah_tiket'];
                }else{
                    $result['kurang'] = (int)$k['jumlah_tiket'];
                }
            }
        }else{
            $result['lebih'] = 0;
            $result['kurang'] = 0;
        }

        return $result;
    }

    public function getPencapaian_post(){
        $kprk       = $this->post('kprk');
        $regional   = $this->post('regional');

        $masuk  = $this->pencapaianMasuk($kprk, $regional);
        $keluar = $this->pencapaianKeluar($kprk, $regional);

        $this->response(array('masuk' => $masuk, 'keluar' => $keluar), 200);
    }

    public function pencapaianTest_post(){
        $kprk       = $this->post('kprk');
        $regional   = $this->post('regional');

        if ($regional != '00') {
            if ($kprk == '00') { //all kprk dengan regional tertentu
                $this->_queryRegional(1, $regional);
            }else{ //where kprk
                $this->_queryKprk(1, $kprk);
            }
        }else{ //all 
            $this->_queryPusat(1);
        }

        $query    = $this->db->get();
        $result   = array();

        // $this->response(array('result' => $query->result()), 200);
        if ($query) {
            foreach ($query->result_array() as $k) {
                if ($k['status'] === null) {
                    $result['lebih'] = 0;
                    $result['kurang'] = 0;
                }else if($k['status'] === '1'){
                    $result['lebih'] = $k['jumlah_tiket'];
                }else{
                    $result['kurang'] = $k['jumlah_tiket'];
                }
            }
        }else{
            return false;
        }

        $this->response(array('result' => $result), 200);
    }

    private function tiketKeluar($kprk, $regional){
        $tiket_selesai = $this->getJumlahTiketKeluar($regional, $kprk, '01');
        $tiket_terbuka = $this->getJumlahTiketKeluar($regional, $kprk, '02');
        $all_tiket     = $this->getJumlahTiketKeluar($regional, $kprk, '00');

        return array(
            'tiket_selesai' => $tiket_selesai,
            'tiket_terbuka' => $tiket_terbuka,
            'semua_tiket' => $all_tiket
        );
    }

    private function getJumlahTiket($regional, $kprk, $type){
        $periode = $this->post('periode');
        $this->db->select('count(DISTINCT a.no_tiket) as jml');
        $this->db->from('tiket a');

        if ($regional == 'KANTORPUSAT') {
            if ($kprk == '00') {
                $this->db->where_in('a.tujuan_pengaduan', array('00001', '00002','40005'));
            }else{
                $this->db->where('a.tujuan_pengaduan', $kprk);
            }
        }else if($regional == '02'){
            $this->db->join('office b', 'a.tujuan_pengaduan = b.code');
            $this->db->where("b.regional <> 'KANTORPUSAT'");
        }else if($regional == '00'){ //all
        }else{
            if ($kprk == '00') {
                $this->db->join('office b', 'a.tujuan_pengaduan = b.code');
                $this->db->where('b.regional', $regional);
            }else{
                $this->db->join('office b', 'a.tujuan_pengaduan = b.code');
                $this->db->where('b.code', $kprk);
            }
        }

        if ($type == '01') {
            $this->db->where('a.status', '99');
        }else if($type == '02'){
            $this->db->where('a.status <> 99');
        }

        $this->db->where("DATE_FORMAT(a.tgl_tambah, '%Y-%m') = '$periode'");

        $query = $this->db->get()->row_array();
        return $query['jml'];
    }

    private function getJumlahTiketKeluar($regional, $kprk, $type){
        $periode = $this->post('periode');
        $this->db->select('count(DISTINCT a.no_tiket) as jml');
        $this->db->from('tiket a');

        if ($regional == 'KANTORPUSAT') {
            if ($kprk == '00') {
                $this->db->where_in('a.asal_pengaduan', array('00001', '00002','40005'));
            }else{
                $this->db->where('a.asal_pengaduan', $kprk);
            }
        }elseif ($regional == '02') {
            $this->db->join('office b', 'a.asal_pengaduan = b.code');
            $this->db->where("b.regional <> 'KANTORPUSAT'");
        }elseif($regional == '00'){ // all

        }else{
            if ($kprk == '00') {
                $this->db->join('office b', 'a.asal_pengaduan = b.code');
                $this->db->where('b.regional', $regional);
            }else{
                $this->db->join('office b', 'a.asal_pengaduan = b.code');
                $this->db->where('b.code', $kprk);
            }
        }

        if ($type == '01') {
            $this->db->where('a.status', '99');
        }else if($type == '02'){
            $this->db->where('a.status <> 99');
        }

        $this->db->where("DATE_FORMAT(a.tgl_tambah, '%Y-%m') = '$periode'");

        $query = $this->db->get()->row_array();
        return $query['jml'];
    }

    private function tiketMasuk($kprk, $regional){
        $tiket_selesai = $this->getJumlahTiket($regional, $kprk, '01');
        $tiket_terbuka = $this->getJumlahTiket($regional, $kprk, '02');
        $all_tiket     = $this->getJumlahTiket($regional, $kprk, '00');

        return array(
            'tiket_selesai' => $tiket_selesai,
            'tiket_terbuka' => $tiket_terbuka,
            'semua_tiket' => $all_tiket
        );
    }

    public function tiket_post(){
        $kprk       = $this->post('kprk');
        $regional   = $this->post('regional');

        $masuk  = $this->tiketMasuk($kprk, $regional);
        $keluar = $this->tiketKeluar($kprk, $regional);

        $this->response(array('masuk' => $masuk, 'keluar' => $keluar), 200);
    }

    public function tiketharini_post() {
        $regional   = $this->post('regional');
        $kprk       = $this->post('kprk');

        if ($regional != '00') {
            if ($kprk == '00') { //all kprk dengan regional tertentu
                $masuk      = $this->tiket_masuk_hari_ini_regional($regional);
                $keluar     = $this->tiket_keluar_hari_ini_regional($regional);
                $this->response(array('masuk' => $masuk, 'keluar' => $keluar), 200);
            }else{ //where kprk
                $masuk      = $this->tiket_masuk_hari_ini_kprk($kprk);
                $keluar     = $this->tiket_keluar_hari_ini_kprk($kprk);
                $this->response(array('masuk' => $masuk, 'keluar' => $keluar), 200);
            }
        }else{ //all 
            $masuk      = $this->tiket_masuk_hari_ini_pusat($kprk);
            $keluar     = $this->tiket_keluar_hari_ini_pusat($kprk);
            $this->response(array('masuk' => $masuk, 'keluar' => $keluar), 200);
        }
        
    }

    private function tiket_keluar_hari_ini_kprk($kprk){
        $this->db->select('COUNT(DISTINCT a.no_tiket) as jumlah');
        $this->db->from('tiket a');
        $this->db->join('office b', 'a.asal_pengaduan = b.code');
        $this->db->where("DATE_FORMAT(a.tgl_tambah, '%Y-%m-%d') = CURDATE()");
        $this->db->where('a.asal_pengaduan', $kprk);
        $query = $this->db->get()->row_array();
        return $query['jumlah'];
    }

    private function tiket_masuk_hari_ini_kprk($kprk){
        $this->db->select('COUNT(DISTINCT a.no_tiket) as jumlah');
        $this->db->from('tiket a');
        $this->db->join('office b', 'a.tujuan_pengaduan = b.code');
        $this->db->where("DATE_FORMAT(a.tgl_tambah, '%Y-%m-%d') = CURDATE()");
        $this->db->where('a.tujuan_pengaduan', $kprk);
        $query = $this->db->get()->row_array();
        return $query['jumlah'];
    }

    private function tiket_keluar_hari_ini_regional($regional, $kprk){
        $this->db->select('COUNT(DISTINCT a.no_tiket) as jumlah');
        $this->db->from('tiket a');
        $this->db->join('office b', 'a.asal_pengaduan = b.code');
        $this->db->where("DATE_FORMAT(a.tgl_tambah, '%Y-%m-%d') = CURDATE()");
        $this->db->where('b.regional', $regional);
        $query = $this->db->get()->row_array();
        return $query['jumlah'];
    }

    private function tiket_masuk_hari_ini_regional($regional, $kprk){
        $this->db->select('COUNT(DISTINCT a.no_tiket) as jumlah');
        $this->db->from('tiket a');
        $this->db->join('office b', 'a.tujuan_pengaduan = b.code');
        $this->db->where("DATE_FORMAT(a.tgl_tambah, '%Y-%m-%d') = CURDATE()");
        $this->db->where('b.regional', $regional);
        $query = $this->db->get()->row_array();
        return $query['jumlah'];
    }

    private function tiket_masuk_hari_ini_pusat(){
        $this->db->select('COUNT(DISTINCT a.no_tiket) as jumlah');
        $this->db->from('tiket a');
        $this->db->join('office b', 'a.tujuan_pengaduan = b.code');
        $this->db->where("DATE_FORMAT(a.tgl_tambah, '%Y-%m-%d') = CURDATE()");
        $query = $this->db->get()->row_array();
        return $query['jumlah'];
    }

        private function tiket_keluar_hari_ini_pusat(){
        $this->db->select('COUNT(DISTINCT a.no_tiket) as jumlah');
        $this->db->from('tiket a');
        $this->db->join('office b', 'a.asal_pengaduan = b.code');
        $this->db->where("DATE_FORMAT(a.tgl_tambah, '%Y-%m-%d') = CURDATE()");
        $query = $this->db->get()->row_array();
        return $query['jumlah'];
    }

    public function getProduk_post(){
        $regional   = $this->post('regional');
        $kprk       = $this->post('kprk');

        $masuk          = $this->getProdukmasuk($regional, $kprk);
        $keluar         = $this->getProdukKeluar($regional, $kprk);

        $this->response(array('masuk' => $masuk->result_array(), 'keluar' => $keluar->result_array()), 200);        
    }

    private function getProdukmasuk($reg, $kprk){
        $this->db->select('a.jenis_layanan, COUNT(DISTINCT a.no_tiket) as jumlah');
        $this->db->from('tiket a');
        $this->db->join('office b', 'a.tujuan_pengaduan = b.code');

        if ($reg != '00') {
            if ($reg == 'KANTORPUSAT') { //pusat
                $this->db->where('a.tujuan_pengaduan', '40005');
            }else{
                if ($kprk == '00') { //all kprk
                    $this->db->where('b.regional', $reg);
                }else{
                    $this->db->where('b.code', $kprk);
                }
            }
        }       

        $this->db->group_by('a.jenis_layanan');
        return $this->db->get();
    }

    private function getProdukKeluar($reg, $kprk){
        $this->db->select('a.jenis_layanan, COUNT(DISTINCT a.no_tiket) as jumlah');
        $this->db->from('tiket a');
        $this->db->join('office b', 'a.asal_pengaduan = b.code');

        if ($reg != '00') {
            if ($reg == 'KANTORPUSAT') { //pusat
                $this->db->where('a.asal_pengaduan', '40005');
            }else{
                if ($kprk == '00') { //all kprk
                    $this->db->where('b.regional', $reg);
                }else{
                    $this->db->where('b.code', $kprk);
                }
            }
        }       

        $this->db->group_by('a.jenis_layanan');
        return $this->db->get();
    }

    private function _queryInfo($regional, $kprk, $type){
        $this->db->select('count(DISTINCT a.no_tiket) as jml');
        $this->db->from('tiket a');

        if ($regional == 'KANTORPUSAT') {
            if ($kprk == '00') {
                if ($type == '01') { //masuk
                    $this->db->where_in('a.tujuan_pengaduan', array('00001', '00002','40005'));
                }else{
                    $this->db->where_in('a.asal_pengaduan', array('00001', '00002','40005'));
                }
            }else{
                if ($type == '01') {
                    $this->db->where('a.tujuan_pengaduan', $kprk);
                }else{
                    $this->db->where('a.asal_pengaduan', $kprk);
                }
            }
        }else if($regional != '00' && $regional != 'KANTORPUSAT'){
            if ($kprk == '00') {
                if ($type == '01') {
                    $this->db->join('office b', 'a.tujuan_pengaduan = b.code');
                    $this->db->where('b.regional', $regional);
                }else{
                    $this->db->join('office b', 'a.asal_pengaduan = b.code');
                    $this->db->where('b.regional', $regional);
                }
            }else{
                if ($type == '01') {
                    $this->db->where('a.tujuan_pengaduan', $kprk);
                }else{
                    $this->db->where('a.asal_pengaduan', $kprk);
                }
            }
        }

        $this->db->where('a.status', '1');
        $this->db->where("DATE_FORMAT(a.tgl_tambah, '%Y-%m-%d') = DATE_FORMAT(now(), '%Y-%m-%d')");

        $query = $this->db->get()->row_array();
        return $query['jml'];
    }

    private function _queryCloseTiket($regional, $kprk){
        $this->db->select('count(DISTINCT a.no_tiket) as jml');
        $this->db->from('tiket a');
        if ($regional == 'KANTORPUSAT') {
            if ($kprk == '00') {
                $this->db->where_in('a.asal_pengaduan', array('00001', '00002','40005'));
            }else{
                $this->db->where('a.asal_pengaduan', $kprk);
            }
        }else if($regional != '00' && $regional != 'KANTORPUSAT'){
            if ($kprk == '00') {
                $this->db->join('office b', 'a.asal_pengaduan = b.code');
                $this->db->where('b.regional', $regional);
            }else{
                $this->db->where('a.asal_pengaduan', $kprk);
            }
        }

        $this->db->where('a.status', '18');

        $query = $this->db->get()->row_array();
        return $query['jml'];   
    }

    private function _queryCountLastUpdate($regional, $kprk){
        $this->db->select('count(DISTINCT a.no_tiket) as jml');
        $this->db->from('tiket a');
        if ($regional == 'KANTORPUSAT') {
            if ($kprk == '00') {
                $this->db->where_in('a.asal_pengaduan', array('00001', '00002','40005'));
            }else{
                $this->db->where('a.asal_pengaduan', $kprk);
            }
        }else if($regional != '00' && $regional != 'KANTORPUSAT'){
            if ($kprk == '00') {
                $this->db->join('office b', 'a.asal_pengaduan = b.code');
                $this->db->where('b.regional', $regional);
            }else{
                $this->db->where('a.asal_pengaduan', $kprk);
            }
        }

        $this->db->where_in('a.status', array('12','18'));
        $this->db->where("DATE_FORMAT(a.lastupdate, '%Y-%m-%d') = DATE_FORMAT(now(), '%Y-%m-%d')");

        $query = $this->db->get()->row_array();
        return $query['jml'];        
    }

    public function getInfo_post(){
        $regional   = $this->post('regional');
        $kprk       = $this->post('kprk');
        $masuk      = $this->_queryInfo($regional, $kprk, '01');
        $keluar     = $this->_queryInfo($regional, $kprk, '02');
        $close      = $this->_queryCloseTiket($regional, $kprk);
        $lastUpdate = $this->_queryCountLastUpdate($regional, $kprk);

        $this->response(array(
            'masuk' => (int)$masuk, 
            'keluar' => (int)$keluar, 
            'close' => (int)$close,
            'lastUpdate' => (int)$lastUpdate
        ), 200);

    }

    public function getGrafikProduk_post(){
        $reg        = $this->post('regional');
        $kprk       = $this->post('kprk');
        $periode    = $this->post('periode');

        $this->db->select("a.nama_layanan as jenis_layanan, b.jumlah as jml_keluar, c.jumlah as jml_masuk");
        $this->db->from('ref_layanan a');

        if ($reg != '00') {
            if ($reg == 'KANTORPUSAT') {
                if ($kprk == '00') {
                    $this->db->join("(
                        select COUNT(DISTINCT a.no_tiket) as jumlah, a.jenis_layanan
                        from tiket a
                        inner join office b on a.asal_pengaduan = b.code 
                        WHERE a.jenis_layanan <> '' AND b.code IN ('00001', '00002','40005')
                        AND DATE_FORMAT(a.tgl_tambah, '%Y-%m') = '$periode'
                        group by a.jenis_layanan
                    ) b", "a.kd_layanan = b.jenis_layanan", "LEFT", null);

                    $this->db->join("(
                        select COUNT(DISTINCT a.no_tiket) as jumlah, a.jenis_layanan
                        from tiket a
                        inner join office b on a.tujuan_pengaduan = b.code 
                        WHERE a.jenis_layanan <> '' AND b.code IN ('00001', '00002','40005')
                        AND DATE_FORMAT(a.tgl_tambah, '%Y-%m') = '$periode'
                        group by a.jenis_layanan
                    ) c", "a.kd_layanan = c.jenis_layanan", "LEFT", null);
                    // $this->db->where_in('code', array('00001', '00002','40005'));       
                }else{
                    $this->db->join("(
                        select COUNT(DISTINCT a.no_tiket) as jumlah, a.jenis_layanan
                        from tiket a
                        inner join office b on a.asal_pengaduan = b.code 
                        WHERE a.jenis_layanan <> '' AND b.code = '$kprk'
                        AND DATE_FORMAT(a.tgl_tambah, '%Y-%m') = '$periode'
                        group by a.jenis_layanan
                    ) b", "a.kd_layanan = b.jenis_layanan", "LEFT", null);

                    $this->db->join("(
                        select COUNT(DISTINCT a.no_tiket) as jumlah, a.jenis_layanan
                        from tiket a
                        inner join office b on a.tujuan_pengaduan = b.code 
                        WHERE a.jenis_layanan <> '' AND b.code = '$kprk'
                        AND DATE_FORMAT(a.tgl_tambah, '%Y-%m') = '$periode'
                        group by a.jenis_layanan
                    ) c", "a.kd_layanan = c.jenis_layanan", "LEFT", null);
                    // $this->db->where('code', $kprk);
                }
            }elseif($reg == '02'){
                 $this->db->join("(
                        select COUNT(DISTINCT a.no_tiket) as jumlah, a.jenis_layanan
                        from tiket a
                        inner join office b on a.asal_pengaduan = b.code 
                        WHERE a.jenis_layanan <> '' AND b.regional <> 'KANTORPUSAT'
                        AND DATE_FORMAT(a.tgl_tambah, '%Y-%m') = '$periode'
                        group by a.jenis_layanan
                    ) b", "a.kd_layanan = b.jenis_layanan", "LEFT", null);

                    $this->db->join("(
                        select COUNT(DISTINCT a.no_tiket) as jumlah, a.jenis_layanan
                        from tiket a
                        inner join office b on a.tujuan_pengaduan = b.code 
                        WHERE a.jenis_layanan <> '' AND b.regional <> 'KANTORPUSAT'
                        AND DATE_FORMAT(a.tgl_tambah, '%Y-%m') = '$periode'
                        group by a.jenis_layanan
                    ) c", "a.kd_layanan = c.jenis_layanan", "LEFT", null);
            }else{
                if ($kprk == '00') {
                     $this->db->join("(
                        select COUNT(DISTINCT a.no_tiket) as jumlah, a.jenis_layanan
                        from tiket a
                        inner join office b on a.asal_pengaduan = b.code 
                        WHERE a.jenis_layanan <> '' AND b.regional = '$reg'
                        AND DATE_FORMAT(a.tgl_tambah, '%Y-%m') = '$periode'
                        group by a.jenis_layanan
                    ) b", "a.kd_layanan = b.jenis_layanan", "LEFT", null);

                    $this->db->join("(
                        select COUNT(DISTINCT a.no_tiket) as jumlah, a.jenis_layanan
                        from tiket a
                        inner join office b on a.tujuan_pengaduan = b.code 
                        WHERE a.jenis_layanan <> '' AND b.regional = '$reg'
                        AND DATE_FORMAT(a.tgl_tambah, '%Y-%m') = '$periode'
                        group by a.jenis_layanan
                    ) c", "a.kd_layanan = c.jenis_layanan", "LEFT", null);

                    //$this->db->where('regional', $reg);
                }else{
                    $this->db->join("(
                        select COUNT(DISTINCT a.no_tiket) as jumlah, a.jenis_layanan
                        from tiket a
                        inner join office b on a.asal_pengaduan = b.code 
                        WHERE a.jenis_layanan <> '' AND b.code = '$kprk'
                        AND DATE_FORMAT(a.tgl_tambah, '%Y-%m') = '$periode'
                        group by a.jenis_layanan
                    ) b", "a.kd_layanan = b.jenis_layanan", "LEFT", null);

                    $this->db->join("(
                        select COUNT(DISTINCT a.no_tiket) as jumlah, a.jenis_layanan
                        from tiket a
                        inner join office b on a.tujuan_pengaduan = b.code 
                        WHERE a.jenis_layanan <> '' AND b.code = '$kprk'
                        AND DATE_FORMAT(a.tgl_tambah, '%Y-%m') = '$periode'
                        group by a.jenis_layanan
                    ) c", "a.kd_layanan = c.jenis_layanan", "LEFT", null);
                    //$this->db->where('code', $kprk);   
                }
            }
        }else{
            $this->db->join("(
                select COUNT(DISTINCT a.no_tiket) as jumlah, a.jenis_layanan
                from tiket a
                inner join office b on a.asal_pengaduan = b.code 
                WHERE a.jenis_layanan <> ''
                AND DATE_FORMAT(a.tgl_tambah, '%Y-%m') = '$periode'
                group by a.jenis_layanan
            ) b", "a.kd_layanan = b.jenis_layanan", "LEFT", null);

            $this->db->join("(
                select COUNT(DISTINCT a.no_tiket) as jumlah, a.jenis_layanan
                from tiket a
                inner join office b on a.tujuan_pengaduan = b.code 
                WHERE a.jenis_layanan <> ''
                AND DATE_FORMAT(a.tgl_tambah, '%Y-%m') = '$periode'
                group by a.jenis_layanan
            ) c", "a.kd_layanan = c.jenis_layanan", "LEFT", null);
        }

        // $this->db->group_by('jenis_layanan');
        $this->db->order_by('jml_keluar', 'DESC');
        $this->db->order_by('jml_masuk', 'DESC');
        $this->db->limit(5);
        $query = $this->db->get();
        $this->response($query->result_array(), 200);
    }


    public function getDetail_post(){
        $this->db->select("t.no_tiket, d.name as channel, c.wilayah, t.awb, e.name as status, t.tgl_tambah, t.tgl_exp, g.nama_layanan");
        $this->db->select("t.tujuan_pengaduan, t.asal_pengaduan");
        $this->db->select("IFNULL(f.tgl_selesai, now()) as tgl_done");
        $this->db->select("IFNULL(timestampdiff(DAY, t.tgl_exp, f.tgl_selesai),'-') as waktu_selesai");
        $this->db->select("IFNULL(timestampdiff(DAY, t.tgl_exp, f.tgl_selesai),0) as orderby");

        $regional   = $this->post('regional');
        $kprk       = $this->post('kprk');
        $tipe       = $this->post('type');
        $label      = $this->post('label');

        $this->db->from("tiket t");
        if ($tipe == 1 || $tipe == 3) { //masuk
            $this->db->join("office b", "t.tujuan_pengaduan = b.code");
        }else if($tipe == 2 || $tipe == 4){ //keluar
            $this->db->join("office b", "t.asal_pengaduan = b.code");
        }else if($tipe == 5 || $tipe == 6 || $tipe == 7){ //detail tiket keluar
            $this->db->join("office b", "t.asal_pengaduan = b.code");
        }else if($tipe == 8 || $tipe == 9 || $tipe == 10){ //detail tiket masuk
            $this->db->join("office b", "t.tujuan_pengaduan = b.code");
        }
        
        $this->db->join("ref_wilayah c", "c.id = b.kd_wilayah");
        $this->db->join("channel_info d", "t.channel_aduan = d.id");
        $this->db->join("ticket_status e", "e.id = t.status");
        $this->db->join("ref_layanan g", "t.jenis_layanan = g.kd_layanan");
        $this->db->join("ticket_selesai f", "t.no_tiket= f.no_ticket", "LEFT", null);

        if ($regional == 'KANTORPUSAT') {
            if ($kprk == '00') {
                $this->db->where_in('b.code', array('00001', '00002','40005'));
            }else{
                $this->db->where('b.code', $kprk);
            }
        }else if($regional == '02'){
            $this->db->where("b.regional <> 'KANTORPUSAT'");
        }else if($regional == '00'){ //all
        }else{
            if ($kprk == '00') {
                $this->db->where('b.regional', $regional);
            }else{
                $this->db->where('b.code', $kprk);
            }
        }

        if ($tipe == 1 || $tipe == 2) { //per pencapaian
            if ($label == 'kurang') {
                $this->db->where('timestampdiff(HOUR, t.tgl_exp, f.tgl_selesai) <= 24');
            }else{
                $this->db->where('timestampdiff(HOUR, t.tgl_exp, f.tgl_selesai) > 24');
            }            
        }else if ($tipe == 3 || $tipe == 4) { //per produk
            $this->db->where('g.nama_layanan', trim($label, ' '));   
        }else if ($tipe == 7 || $tipe == 10){ //all tiket

        }else if($tipe == 6 || $tipe == 9){ //terbuka
            $this->db->where('f.tgl_selesai is null');
        }else if($tipe == 5 || $tipe == 8){ //selesai
            $this->db->where('t.status', '99');
        }

        if ($this->post('periode')) {
            $periode = $this->post('periode');
            $this->db->where("DATE_FORMAT(t.tgl_tambah, '%Y-%m') = '$periode'");
        }

        $query = $this->db->get()->result_array();

        $this->response($this->groupingByTujuan($query), 200);
    }


    private function groupingByTujuan($array){
        $new_tiket_list = array();
        for ($first_key = 0; $first_key < count($array); $first_key++) {
            $tiket = $array[$first_key];
            $current_notiket    = $tiket['no_tiket']; // string
            $current_tujuan     = $tiket['tujuan_pengaduan']; // string

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
?>
    	

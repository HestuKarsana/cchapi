<?php

use Restserver\Libraries\REST_Controller;
require(APPPATH.'/libraries/REST_Controller.php');

/**
 * 
 */
class getemployee extends REST_Controller
{
	
	public function index_post(){
		$this->db2  = $this->load->database('dbsqlsrvsso', TRUE);
        $idPegawai  = $this->post('idPegawai');
        $nopend     = $this->post('nopend');
        if ($nopend == '40005') {
            $sql        = "SELECT a.ID_Pegawai as nip, a.Nama as namaLengkap, 
            				a.Nomor_HP as phone, Email as email, b.Nomor_dirian as kdkantor, b.Jenis as jenisKantor, Jabatan as jabatanKantor
            				FROM sso.sso.sso_pegawai a 
            				LEFT JOIN sso.dbo.masterkantor b ON a.Nomor_dirian = b.Nomor_Dirian 
            				WHERE ID_Pegawai = ? AND Status_Kerja = 1";
            $query      = $this->db2->query($sql, array($idPegawai));
            if ($query->num_rows() > 0) {
                $listPegawai = $query->row_array();
                $this->response($listPegawai, 200);
            } else {
                $this->response(array("errors" => 'FAILED GET LIST EMPLOYEE'), 400);
            }
        } else {
            $sql        = "SELECT a.ID_Pegawai as nip, a.Nama as namaLengkap, 
                            a.Nomor_HP as phone, Email as email, b.Nomor_dirian as kdkantor, b.Jenis as jenisKantor, Jabatan as jabatanKantor
                            FROM sso.sso.sso_pegawai a 
                            LEFT JOIN sso.dbo.masterkantor b ON a.Nomor_dirian = b.Nomor_Dirian 
                            WHERE ID_Pegawai = ? AND Status_Kerja = 1 AND b.Divre = ?";
            $query      = $this->db2->query($sql, array($idPegawai, $nopend));
            if ($query->num_rows() > 0) {
                $listPegawai = $query->row_array();
                $this->response($listPegawai, 200);
            } else {
                $this->response(array("errors" => 'FAILED GET LIST EMPLOYEE'), 400);
            }
        }
        
	}
}
?>
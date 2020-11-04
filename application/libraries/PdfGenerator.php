<?php
 
class PdfGenerator
{
  public function generate($html,$filename)
  {
    define('DOMPDF_ENABLE_AUTOLOAD', false);
    require_once("./vendor/dompdf/dompdf/dompdf_config.inc.php");
    $dompdf = new DOMPDF();
    $dompdf->load_html($html);
    $dompdf->set_paper('A4','landscape');
    $dompdf->render();
    ob_end_clean();
    $output = $dompdf->output();
    //$dompdf->stream($filename.'.pdf',array("Attachment"=>1));
    $file   = './assets/download/tugas/'.$filename.'.pdf'; 
    file_put_contents($file, $output);
  }

  public function generatePO($html, $filename){
    define('DOMPDF_ENABLE_AUTOLOAD', false);
    require_once("./vendor/dompdf/dompdf/dompdf_config.inc.php");
    $dompdf = new DOMPDF();
    $dompdf->load_html($html);
    $dompdf->set_paper('A4','landscape');
    $dompdf->render();
    ob_end_clean();
    // $dompdf->stream($filename.'.pdf',array("Attachment"=>0));
    $output = $dompdf->output();
    $file   = './assets/download/po/'.$filename.'.pdf'; 
    file_put_contents($file, $output);
  }

  public function generateInvoice(){
    define('DOMPDF_ENABLE_AUTOLOAD', false);
    require_once("./vendor/dompdf/dompdf/dompdf_config.inc.php");
  }
  
}
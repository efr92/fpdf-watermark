<?

require(dirname(__DIR__) .'/pdf/fpdf/fpdf.php');
require_once dirname(__DIR__) .'/pdf/FPDI/fpdi.php';

$fullPathToFile = dirname(__DIR__) .'/pdf/gaid_v1_4.pdf';

class WaterMark
{
    public $pdf, $file, $newFile,
        $wmText = "Guide №";

    /** $file and $newFile have to include the full path. */
    public function __construct($file, $newFile)
    {
        $this->pdf = new FPDI();
        $this->file = $file;
        $this->newFile = $newFile;
    }

    /** $file and $newFile have to include the full path. */
    public static function applyAndSpit($file, $newFile, $k)
    {
        $wm = new WaterMark($file, $newFile);

        // if($wm->isWaterMarked())
        //     return $wm->spitWaterMarked();
        // else{
            $wm->doWaterMark($k);
            return $wm->spitWaterMarked();
        // }
    }

    /** @todo Make the text nicer and add to all pages */
    public function doWaterMark($k)
    {
        $currentFile = $this->file;
        $newFile = $this->newFile;

        $pagecount = $this->pdf->setSourceFile($currentFile);

        for($i = 1; $i <= $pagecount; $i++){
            $this->pdf->addPage();
            $tplidx = $this->pdf->importPage($i);
            $this->pdf->useTemplate($tplidx, 0, 0, 210);

            if($i == 1 || $i == 12 || $i == 28 || $i == 79 || $i == 89 || $i == 125) {
                continue;
            }

            $this->pdf->SetAlpha(0.1);
            // now write some text above the imported page
            $this->pdf->AddFont('Montserrat','','Montserrat.php');
            $this->pdf->SetFont('Montserrat', '', 50);
            $this->pdf->SetTextColor(80,80,80);
            $this->pdf->SetXY(70, 160);
            $this->_rotate(45);
            $this->pdf->Write(0, iconv('utf-8', 'windows-1251', $this->wmText.$k));
            $this->_rotate(0);
        }

        $this->pdf->Output($newFile, 'F');
    }

    public function isWaterMarked()
    {
        return (file_exists($this->newFile));
    }

    public function spitWaterMarked()
    {
        return readfile($this->newFile);
    }

    protected function _rotate($angle,$x=-1,$y=-1) {

        if($x==-1)
            $x=$this->pdf->x;
        if($y==-1)
            $y=$this->pdf->y;
        if($this->pdf->angle!=0)
            $this->pdf->_out('Q');
        $this->pdf->angle=$angle;

        if($angle!=0){
            $angle*=M_PI/180;
            $c=cos($angle);
            $s=sin($angle);
            $cx=$x*$this->pdf->k;
            $cy=($this->pdf->h-$y)*$this->pdf->k;

            $this->pdf->_out(sprintf(
                'q %.5f %.5f %.5f %.5f %.2f %.2f cm 1 0 0 1 %.2f %.2f cm',
                $c,$s,-$s,$c,$cx,$cy,-$cx,-$cy));
        }
    }

}

// header('Content-type: application/pdf');
header('Content-Disposition: attachment; filename="downloaded.pdf"');


if ($_COOKIE["pdf_key"]) {
    $key = $_COOKIE["pdf_key"]+12;
} else {
    $key = 1;
}

for ($k = $key; $k <= 2000; $k++) {
    $fullPathToProcessFile = dirname(__DIR__) .'/pdf/ready/gaid('.$k.').pdf';
    setcookie("pdf_key", $k, time()+48400);

    WaterMark::applyAndSpit($fullPathToFile, $fullPathToProcessFile, $k);
}

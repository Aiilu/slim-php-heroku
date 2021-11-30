<?php
use Fpdf\Fpdf;

class PDF extends Fpdf
{
    public function guardarPDF()
    {
        $this->AddPage();
        $this->SetFont('Arial','B',16);
        $this->Cell(40,10,'Listado de Usuarios',0,1);
    }    
}
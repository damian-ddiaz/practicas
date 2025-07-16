<?php
//HTML2PDF by Clément Lavoillotte
//ac.lavoillotte@noos.fr
//webmaster@streetpc.tk
//http://www.streetpc.tk

//require('fpdf.php');
sc_include_library("sys", "fpdf", "fpdf.php", true, true);
//function hex2dec
//returns an associative array (keys: R,G,B) from
//a hex html code (e.g. #3FE5AA)
function hex2dec($couleur = "#000000"){
	$R = substr($couleur, 1, 2);
	$rouge = hexdec($R);
	$V = substr($couleur, 3, 2);
	$vert = hexdec($V);
	$B = substr($couleur, 5, 2);
	$bleu = hexdec($B);
	$tbl_couleur = array();
	$tbl_couleur['R']=$rouge;
	$tbl_couleur['V']=$vert;
	$tbl_couleur['B']=$bleu;
	return $tbl_couleur;
}

//conversion pixel -> millimeter at 72 dpi
function px2mm($px){
	return $px*25.4/72;
}

function txtentities($html){
	$trans = get_html_translation_table(HTML_ENTITIES);
	$trans = array_flip($trans);
	return strtr($html, $trans);
}
////////////////////////////////////

class PDF_HTML extends FPDF
{
//variables of html parser
protected $B;
protected $I;
protected $U;
protected $HREF;
protected $fontlist;
protected $issetfont;
protected $issetcolor;
protected $align;
protected $col;
protected $nb_col;
protected $col_width;
protected $current_x;

function __construct($orientation='P', $unit='mm', $size='A4')
{
	//Call parent constructor
	parent::__construct($orientation,$unit,$size);
	//Initialization
	$this->B=0;
	$this->I=0;
	$this->U=0;
	$this->HREF='';
	$this->fontlist=array('arial', 'times', 'courier', 'helvetica', 'symbol');
	$this->issetfont=false;
	$this->issetcolor=false;
	$this->align = 'L'; // Default align is Left
	$this->col = 0; // Current column
	$this->nb_col = 1; // Number of columns
	$this->col_width = 0;
	$this->current_x = $this->lMargin;
	error_log("PDF_HTML::__construct() called");
}

function SetAlign($align)
{
	if(in_array($align, array('L', 'C', 'R', 'J')))
		$this->align = $align;
	else
		$this->align = 'L';
	error_log("PDF_HTML::SetAlign(" . $align . ") called");
}

function SetNbColumns($nb)
{
	if($nb>0)
	{
		$this->nb_col = $nb;
		$this->col_width = floor(($this->w - $this->lMargin - $this->rMargin - ($nb - 1) * 5) / $nb);
		$this->SetCol(0);
	}
	error_log("PDF_HTML::SetNbColumns(" . $nb . ") called");
}

function SetCol($col)
{
	$this->col = $col;
	$x = $this->lMargin + $col * ($this->col_width + 5);
	$this->SetLeftMargin($x);
	$this->SetX($x);
	error_log("PDF_HTML::SetCol(" . $col . ") called");
}

function AcceptPageBreak()
{
	if($this->col<($this->nb_col-1))
	{
		$this->SetCol($this->col+1);
		$this->SetY($this->tMargin);
		return false;
	}
	else
	{
		$this->SetCol(0);
		return true;
	}
}

function WriteHTML($html, $justify = false, $columna = 1, $num_columnas = 1, $ancho_pagina = 0, $margen_izquierdo = null, $espacio_entre_columnas = 5)
{
	error_log("PDF_HTML::WriteHTML() called with justify=" . ($justify ? 'true' : 'false') . ", columns=" . $num_columnas);
	$html=strip_tags($html,"<b><u><i><a><img><p><br><strong><em><font><tr><blockquote><h1><h2><h3><h4><h5><h6><div><span>");
	$html=str_replace("\n",' ',$html);
	$a=preg_split('/<(.*)>/U',$html,-1,PREG_SPLIT_DELIM_CAPTURE);
	$list = array();
	$i = 0;
	foreach($a as $e) {
		if ($i%2==0) {
			$list[] = array('type' => 'text', 'value' => trim($e));
		} else {
			$list[] = array('type' => 'tag', 'value' => $e);
		}
		$i++;
	}

	// Desactivar temporalmente la lógica de columnas y justificación para aislar el problema
	$num_columnas = 1;
	$justify = false;
	$this->SetNbColumns(1);

	foreach($list as $item)
	{
		if($item['type'] == 'text')
		{
			error_log("WriteHTML: Processing text: '" . $item['value'] . "'");
			if($this->HREF)
				$this->PutLink($this->HREF,txtentities($item['value']));
			else {
				$this->Write(5,txtentities($item['value']));
			}
		}
		else
		{
			$tag_info = strtoupper(substr($item['value'], (strpos($item['value'], '/') === 0) ? 1 : 0));
			error_log("WriteHTML: Processing tag: '" . $tag_info . "'");
			if($item['value'][0]=='/')
				$this->CloseTag(strtoupper(substr($item['value'],1)));
			else
			{
				$a2=explode(' ',$item['value']);
				$tag=strtoupper(array_shift($a2));
				$attr=array();
				foreach($a2 as $v)
				{
					if(preg_match('/([^=]*)=["\']?([^"\']*)/',$v,$a3))
						$attr[strtoupper($a3[1])]=$a3[2];
				}
				$this->OpenTag($tag,$attr);
			}
		}
	}
}

// Función auxiliar para imprimir con justificado (temporalmente desactivada)
function PrintJustify($text)
{
	error_log("PDF_HTML::PrintJustify() called (TEMPORARILY DISABLED)");
	$this->Write(5, $text); // Simplemente escribir sin justificar por ahora
}

function OpenTag($tag, $attr)
{
	error_log("PDF_HTML::OpenTag(" . $tag . ")");
	switch($tag){
		case 'STRONG':
			$this->SetStyle('B',true);
			break;
		case 'EM':
			$this->SetStyle('I',true);
			break;
		case 'B':
		case 'I':
		case 'U':
			$this->SetStyle($tag,true);
			break;
		case 'A':
			$this->HREF=$attr['HREF'];
			break;
		case 'IMG':
			if(isset($attr['SRC']) && (isset($attr['WIDTH']) || isset($attr['HEIGHT']))) {
				if(!isset($attr['WIDTH']))
					$attr['WIDTH'] = 0;
				if(!isset($attr['HEIGHT']))
					$attr['HEIGHT'] = 0;
				$this->Image($attr['SRC'], $this->GetX(), $this->GetY(), px2mm($attr['WIDTH']), px2mm($attr['HEIGHT']));
			}
			break;
		case 'BR':
			$this->Ln(5); // <br> sigue generando salto
			break;
		case 'FONT':
			if (isset($attr['COLOR']) && $attr['COLOR']!='') {
				$coul=hex2dec($attr['COLOR']);
				$this->SetTextColor($coul['R'],$coul['V'],$coul['B']);
				$this->issetcolor=true;
			}
			if (isset($attr['FACE']) && in_array(strtolower($attr['FACE']), $this->fontlist)) {
				$this->SetFont(strtolower($attr['FACE']));
				$this->issetfont=true;
			}
			break;
	}
}


function CloseTag($tag)
{
	error_log("PDF_HTML::CloseTag(" . $tag . ")");
	if($tag == 'STRONG') $tag = 'B';
	if($tag == 'EM') $tag = 'I';

	if($tag == 'B' || $tag == 'I' || $tag == 'U') {
		$this->SetStyle($tag, false);
	}

	if($tag == 'A') {
		$this->HREF = '';
	}

	if($tag == 'FONT') {
		if ($this->issetcolor) {
			$this->SetTextColor(0);
			$this->issetcolor = false;
		}
		if ($this->issetfont) {
			$this->SetFont('arial');
			$this->issetfont = false;
		}
	}

	// Salto de línea al cerrar etiquetas de bloque (temporalmente incondicional)
	if(in_array($tag, ['P', 'BLOCKQUOTE', 'H1', 'H2', 'H3', 'H4', 'H5', 'H6', 'DIV', 'TR'])) {
		$this->Ln(5);
		error_log("CloseTag: Added line break for tag '" . $tag . "'");
	}
}

function SetStyle($tag, $enable)
{
	$this->$tag+=($enable ? 1 : -1);
	$style='';
	foreach(array('B','I','U') as $s)
	{
		if($this->$s>0)
			$style.=$s;
	}
	$this->SetFont('',$style);
}

function PutLink($URL, $txt)
{
	$this->SetTextColor(0,0,255);
	$this->SetStyle('U',true);
	$this->Write(5,$txt,$URL);
	$this->SetStyle('U',false);
	$this->SetTextColor(0);
}

}//end of class
?>
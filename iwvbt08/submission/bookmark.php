<?php
require('fpdf.php');

class PDF_Bookmark extends FPDF
{
var $outlines=array();
var $OutlineRoot;

function Bookmark($txt,$level=0,$y=0)
{
    if($y==-1)
        $y=$this->GetY();
    $this->outlines[]=array('t'=>$txt,'l'=>$level,'y'=>$y,'p'=>$this->PageNo());
}

function _putbookmarks()
{
    $nb=count($this->outlines);
    if($nb==0)
        return;
    $lru=array();
    $level=0;
    foreach($this->outlines as $i=>$o)
    {
        if($o['l']>0)
        {
            $parent=$lru[$o['l']-1];
            //Set parent and last pointers
            $this->outlines[$i]['parent']=$parent;
            $this->outlines[$parent]['last']=$i;
            if($o['l']>$level)
            {
                //Level increasing: set first pointer
                $this->outlines[$parent]['first']=$i;
            }
        }
        else
            $this->outlines[$i]['parent']=$nb;
        if($o['l']<=$level and $i>0)
        {
            //Set prev and next pointers
            $prev=$lru[$o['l']];
            $this->outlines[$prev]['next']=$i;
            $this->outlines[$i]['prev']=$prev;
        }
        $lru[$o['l']]=$i;
        $level=$o['l'];
    }
    //Outline items
    $n=$this->n+1;
    foreach($this->outlines as $i=>$o)
    {
        $this->_newobj();
        $this->_out('<</Title '.$this->_textstring($o['t']));
        $this->_out('/Parent '.($n+$o['parent']).' 0 R');
        if(isset($o['prev']))
            $this->_out('/Prev '.($n+$o['prev']).' 0 R');
        if(isset($o['next']))
            $this->_out('/Next '.($n+$o['next']).' 0 R');
        if(isset($o['first']))
            $this->_out('/First '.($n+$o['first']).' 0 R');
        if(isset($o['last']))
            $this->_out('/Last '.($n+$o['last']).' 0 R');
        $this->_out(sprintf('/Dest [%d 0 R /XYZ 0 %.2f null]',1+2*$o['p'],($this->h-$o['y'])*$this->k));
        $this->_out('/Count 0>>');
        $this->_out('endobj');
    }
    //Outline root
    $this->_newobj();
    $this->OutlineRoot=$this->n;
    $this->_out('<</Type /Outlines /First '.$n.' 0 R');
    $this->_out('/Last '.($n+$lru[0]).' 0 R>>');
    $this->_out('endobj');
}

function _putresources()
{
    parent::_putresources();
    $this->_putbookmarks();
}

function _putcatalog()
{
    parent::_putcatalog();
    if(count($this->outlines)>0)
    {
        $this->_out('/Outlines '.$this->OutlineRoot.' 0 R');
        $this->_out('/PageMode /UseOutlines');
    }
}
}

class PDF_Index extends PDF_Bookmark
{
function CreateIndex(){
    //Titre
    $this->SetFontSize(20);
    $this->Cell(0,5,'Index',0,1,'C');
    $this->SetFontSize(15);
    $this->Ln(10);

    $size=sizeof($this->outlines);
    $PageCellSize=$this->GetStringWidth('p. '.$this->outlines[$size-1]['p'])+2;
    for ($i=0;$i<$size;$i++){
        //Décalage
        $level=$this->outlines[$i]['l'];
        if($level>0)
            $this->Cell($level*8);

        //Libellé
        $str=$this->outlines[$i]['t'];
        $strsize=$this->GetStringWidth($str);
        $avail_size=$this->w-$this->lMargin-$this->rMargin-$PageCellSize-($level*8)-4;
        while ($strsize>=$avail_size){
            $str=substr($str,0,-1);
            $strsize=$this->GetStringWidth($str);
        }
	$link=$this->AddLink();
	$this->SetLink($link,0,$this->outlines[$i]['p']);
        $this->Cell($strsize+2,$this->FontSize+2,$str,0,0,'L',0,$link);

        //Points
        $w=$this->w-$this->lMargin-$this->rMargin-$PageCellSize-($level*8)-($strsize+2);
        $nb=$w/$this->GetStringWidth('.');
        $dots=str_repeat('.',$nb);
        $this->Cell($w,$this->FontSize+2,$dots,0,0,'R');

        //Numéro de page
        $this->Cell($PageCellSize,$this->FontSize+2,'p. '.$this->outlines[$i]['p'],0,1,'R',0,$link);
    }
}
}
?>
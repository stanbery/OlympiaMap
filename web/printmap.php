<?php

require('fpdf16/fpdf.php');
include_once('oly.php');

$grey = (isset($_REQUEST['grey']) ? 1 : 0);

$cellW = 8;
$cellH = 2;
if ($grey)
{
$mapColors = array (
  'clear' => array ('ff', 'ff', 'ff'),
  'ocean' => array ('ff', 'ff', 'ff'),
  'oroute' => array ('ff', 'ff', 'ff'),
  'unknown' => array ('dd', 'dd', 'dd'),
  'swamp' => array ('66', '66', '66'),
  'mountain' => array ('88', '88', '88'),
  'forest' => array ('aa', 'aa', 'aa'),
  'plain' => array ('bb', 'bb', 'bb'),
  'desert' => array('cc', 'cc', 'cc'),
);
} else {
$mapColors = array (
  "mountain" =>  array ("ff", "99", "99"),
  "desert" => array ("ff", "cc", "00"),
  "plain" => array ("ff", "ff", "66"),
  "forest" => array ("33", "ff", "33"),
  "swamp" => array ("bb", "bb", "bb"),
  "ocean" => array ("33", "cc", "ff"),
  "oroute" => array ("33", "cc", "ff"),
  "unknown" => array ("66", "66", "66"),
  "clear" => array ("ff", "ff", "ff"),
  );
}
$printFont = 'Times';
$printSize = 6;
$printPage = 'A3';
$seenFormat = 'B';
$slashWidth = 0.4;
$cornerBox = 1;

class myPdf extends FPDF
{
  var $leftMargin;

  function SetDash($black=null, $white=null)
  {
    if($black!==null)
      $s=sprintf('[%.3F %.3F] 0 d',$black*$this->k,$white*$this->k);
    else
      $s='[] 0 d';
    $this->_out($s);
  }

function Circle($x, $y, $r, $style='D')
{
    $this->Ellipse($x,$y,$r,$r,$style);
}

function Ellipse($x, $y, $rx, $ry, $style='D')
{
    if($style=='F')
        $op='f';
    elseif($style=='FD' || $style=='DF')
        $op='B';
    else
        $op='S';
    $lx=4/3*(M_SQRT2-1)*$rx;
    $ly=4/3*(M_SQRT2-1)*$ry;
    $k=$this->k;
    $h=$this->h;
    $this->_out(sprintf('%.2F %.2F m %.2F %.2F %.2F %.2F %.2F %.2F c',
        ($x+$rx)*$k,($h-$y)*$k,
        ($x+$rx)*$k,($h-($y-$ly))*$k,
        ($x+$lx)*$k,($h-($y-$ry))*$k,
        $x*$k,($h-($y-$ry))*$k));
    $this->_out(sprintf('%.2F %.2F %.2F %.2F %.2F %.2F c',
        ($x-$lx)*$k,($h-($y-$ry))*$k,
        ($x-$rx)*$k,($h-($y-$ly))*$k,
        ($x-$rx)*$k,($h-$y)*$k));
    $this->_out(sprintf('%.2F %.2F %.2F %.2F %.2F %.2F c',
        ($x-$rx)*$k,($h-($y+$ly))*$k,
        ($x-$lx)*$k,($h-($y+$ry))*$k,
        $x*$k,($h-($y+$ry))*$k));
    $this->_out(sprintf('%.2F %.2F %.2F %.2F %.2F %.2F c %s',
        ($x+$lx)*$k,($h-($y+$ry))*$k,
        ($x+$rx)*$k,($h-($y+$ly))*$k,
        ($x+$rx)*$k,($h-$y)*$k,
        $op));
}

  function Poly($points, $close = false, $style = '')
  {
    if($style == 'F')
    {
      $op = 'f';
    } else if($style == 'FD' || $style == 'DF')
    {
      $op = 'B';
    } else {
      $op = 'S';
    }
    $buffer = sprintf("%.2f %.2f m\n", $points[0][0] * $this->k,
      ($this->h - $points[0][1]) * $this->k);
    for ($i=1; $i<count($points); $i++)
    {
      $buffer = sprintf("%.2f %.2f l\n", $points[$i][0] * $this->k,
       ($this->h - $points[$i][1]) * $this->k);
    }
    if($close)
    {
      $buffer .= " h\n";
    }
    $buffer .= " $op\n";
    $this->_out($buffer);
  }

  function Triangle($x, $y)
  {
    $this->Line($x, $y, $x+0.5, $y+0.5);
    $this->Line($x+0.5, $y+0.5, $x+1, $y);
    $this->Line($x, $y, $x+1, $y);
  }
  
  function setFill($s)
  {
    global $mapColors;
    $this->SetFillColor(hexdec($mapColors[$s][0]), hexdec($mapColors[$s][1]),
      hexdec($mapColors[$s][2]));
  }
  function decorateCell($x1, $y1, $x2, $y2, $fmt)
  {
    global $slashWidth, $cornerBox;
    // decorate the cell
    $w = $this->LineWidth;
    $this->SetLineWidth($slashWidth);
    $this->SetFillColor('00', '00', '00');
    $parms = explode("+", $fmt);
    foreach ($parms as $fmtX)
    {
      $fmtType = substr($fmtX, 0, 1);
      $fmtLoc = substr($fmtX, 1);
      if (strlen($fmtLoc) > 0)
      {
        switch ($fmtLoc)
        {
          case 'rt':
            $a1 = $x2 - $cornerBox;
            $b1 = $y1;
            break;
          case 'lt':
            $a1 = $x1;
            $b1 = $y1;
            break;
          case 'lb':
            $a1 = $x1;
            $b1 = $y2 - $cornerBox;
            break;
          case 'rb':
            $a1 = $x2 - $cornerBox;
            $b1 = $y2 - $cornerBox;
            break;
        }
      }
      switch ($fmtType)
      {
        case 'o':
          $this->SetLineWidth(0.1);
          $this->Circle($a1+0.3, $b1+0.3, 0.3);
          break;
        case '\\':
          $this->Line($x1, $y1, $x2, $y2);
          break;
        case '/':
          $this->Line($x1, $y2, $x2, $y1);
          break;
        case 'x':
        case 'X':
          $this->Line($x1, $y1, $x2, $y2);
          $this->Line($x1, $y2, $x2, $y1);
          break;
        case '.':
          $this->Rect($a1, $b1, $cornerBox, $cornerBox, 'DF');
          break;
        case '^':
          $this->Triangle($x1, $y1);
          break;
        case '|':
          $this->SetDash(1,1);
          $this->Line($x1+0.5, $y1, $x1+0.5, $y2);
          $this->Line($x2-0.5, $y1, $x2-0.5, $y2);
          $this->SetDash();
          break;
      }
    }
    $this->SetLineWidth($w);
  }
  function makeCell($s, $s2 = ' ', $fmt = '', $decorate = '')
  {
    global $cellW, $cellH, $printFont;
    $y1 = $this->GetY();
    $x1 = $this->GetX();
    // top cell + bottom cell
    if (strlen($fmt) > 0)
    {
      $this->SetFont($printFont, $fmt);
    }
    $this->Cell($cellW, $cellH, $s, 'LTR', 2, 'L', true);
    if (strlen($fmt) > 0)
    {
      $this->SetFont($printFont, '');
    }
    $this->Cell($cellW, $cellH+1, $s2, 'BLR', 0, 'L', true);
    $x2 = $this->GetX();
    $y2 = $this->GetY() + $cellH + 1;
    if (strlen($decorate) > 0)
    {
      $this->decorateCell($x1, $y1, $x2, $y2, $decorate);
    }
    $this->SetXY($x2, $y1);
  }
  function topRow($n)
  {
    $this->setFill('clear');
    $this->makeCell(' ');
    // $n is the page number
    // even = lower 50, odd = upper 50
    $x = ($n % 2 == 0 ? 0 : 50);
    for ($i = $x; $i < $x + 50; $i++)
    {
      $this->makeCell(sprintf("%02d", $i));
    }
  }
  
  function mapCell($id)
  {
    global $olyData, $cities, $seenFormat, $grey;
    if (isset($olyData['mapData'][$id]['terrain']))
    {
      if (is_array($olyData['mapData'][$id]['terrain'])) print "$id ";
      $this->setFill($olyData['mapData'][$id]['terrain']);
      $t = substr($olyData['mapData'][$id]['terrain'], 0, 1);
      $t = ($grey ? $id.' '.$t : $id);
      if ($olyData['mapData'][$id]['seen'])
      {
        $fmt = $seenFormat;
      } else {
        $fmt = '';
      }
      $c = 0;
      $decorate = (is_array($olyData['mapData'][$id]['ruler']) ? '.rt' : '');
      $decorate .= ($olyData['mapData'][$id]['group'] == 4 ? '+.lb' : '');
      $decorate .= ($olyData['mapData'][$id]['group'] == 3 ? '+|' : '');
      $decorate .= ($olyData['mapData'][$id]['group'] == 2 ? '+olt' : '');
      if (isset($olyData['mapData'][$id]['inner']))
      {
        foreach ($olyData['mapData'][$id]['inner'] as $k => $v)
        {
          if ($cities[$olyData['mapData'][$k]['type']])
          {
            $c ++;
          } else {
            $i ++;
          }
        }
        $s = ($c ? "C" : "");
        $s .= ($i ? "x" : "");

        $this->makeCell($t, $s, $fmt, $decorate);
      } else {
        $this->makeCell($t, '', $fmt, $decorate);
      }
    } else {
      $this->setFill('clear');
      $this->makeCell(' ');
    }

  }
  
  function mapRows($pageNum)
  {
    // page 0 = 0-49, 0-49
    // page 1 = 50-99, 0-49
    // page 2 = 0-49, 50-99
    // page 3 = 50-99, 50-99
    $y = ($pageNum % 2 == 0 ? 0 : 50);
    $x = (floor($pageNum / 2) == 0 ? 0 : 50);
    for ($i = $x; $i < $x + 50; $i++)
    {
      // line break
      $this->SetXY($this->leftMargin, $this->GetY() + 5);
      $this->setFill('clear');
      $this->makeCell(letterCell($i));
      for ($j = $y; $j < $y + 50; $j++)
      {
        $id = makeLoc($i, $j);
        $this->mapCell($id);
      }
    }
  }
  
  function makePages($pageFormat)
  {
    for ($pageNum = 0; $pageNum < 4; $pageNum++)
    {
      $this->AddPage("L", $pageFormat);
      $this->leftMargin = $this->GetX();
      $this->topRow($pageNum);
      $this->mapRows($pageNum);
    }
  }
  
  function makeMap()
  {
    global $olyData, $printFont, $printSize, $printPage;
    $this->SetFont($printFont, '', $printSize);
    $this->makePages($printPage);
    $this->Output();
  }

}

mergeData("data.xml");
$pdf = new myPdf();
$pdf->makeMap();

?>

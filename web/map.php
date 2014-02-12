<?php

include_once("oly.php");
  
  $mapKind = (isset($_REQUEST['map']) ? $_REQUEST['map'] : 'norm');
  $zoom = (isset($_REQUEST['zoomLoc']) ?
    array ('id' => $_REQUEST['zoomLoc'], 'size' => $_REQUEST['zoomSize']) :
    NULL);
  $center = (isset($_REQUEST['center']) ? $_REQUEST['center'] : '');
  $lvl = (isset($_REQUEST['lvl']) ? $_REQUEST['lvl'] : 0);
  $suffix = ($lvl ? ' level '.$lvl : '');
  
  $foundNobles = 0;
  $title = array (
    'norm' => 'Olympia G3 Map',
    'gates' => 'Olympia G3 Gate Map',
    'faery' => 'Olympia G3 Faery Map',
    'civ' => 'Olympia G3 Civ Map',
    'sewer' => 'Olympia G3 Sewer Map',
    'skill' => 'Olympia G3 Sewer Map',
    'garr' => 'Olympia G3 Garrison Map',
    'weather' => 'Olympia G3 Weather Map',
    );
  
  $ymax = 0;
  $ymin = 0;
  $xmax = 0;
  $xmin = 0;
  
  $altStart = array (
    "sewer" => "gh80",
//    "faery" => "xd76",
    "faery" => "kr99",
    );
  
  function topRow()
  {
    global $mColStart, $mColEnd;
    // top row consists of 2-digit numbers; but only from 'start' to 'finish'
    print "<tr><td></td>";
    for ($i = $mColStart; $i <= $mColEnd; $i++)
    {
      print sprintf("<td class=\"map\">&nbsp;%02d&nbsp;</td>", $i);
    }
    print "</tr>\n";
  }

  function createData()
  {
    global $mapCells;
    for ($i = 0; $i < 100; $i++)
    {
      for ($j = 0; $j < 100; $j++)
      {
      $mapCells[sprintf("%s%02d", letterCell($i), $j)] = "";
      }
    }
  }
  
  function addNobles($x)
  {
    global $hover, $cellTxt, $olyData, $br;
    if (!empty($x["nobles"]))
    {
      $hover .= "Nobles:".$br;
      foreach ($x["nobles"] as $n => $q)
      {
        $nid = $n;
	      $hover .= substr($n, 1);
	      if (!empty($olyData["nobles"][$nid]))
	      {
          $hover .= ": ".htmlspecialchars($olyData["nobles"][$nid]["name"], ENT_QUOTES);
			    if (!empty($olyData["nobles"][$nid]["faction"]))
			    {
			      $hover .= " [".$olyData["nobles"][$nid]["faction"]."]";
		      }
	  	  }
	      $hover .= $br;
      }
      if (strlen($cellTxt) == 0)
      {
        $celTxt = ".";
      }
    }
  }

  function formatCellInner($x, $n)
  {
    global $hover, $cellTxt, $olyData, $cities, $innerTypes, $br, $foundNobles, $friendlies, $cellClass, $unfriendly;
    $addedInner = 0;
    if (!empty($x["inner"]))
    {
      // add city
      foreach ($x["inner"] as $k => $v)
      {
        if (is_array($olyData["mapData"][$k]["name"]))
        {
          print "Array found: $k.";
        }
        if (is_array($olyData['mapData'][$k]['nobles']))
        {
          foreach ($olyData['mapData'][$k]['nobles'] as $z => $zz)
          {
          //print "loc: $n nob: $z fac:".$olyData['nobles'][$z]['faction']."\n";
            if (!$friendlies[$olyData['nobles'][$z]['faction']])
            {
              $unfriendly = 1;
            }
          }
        }
        $foundNobles += count($olyData["mapData"][$k]["nobles"]);
        if ($cities[$olyData["mapData"][$k]["type"]])
        {
          $hover .= "&gt;".$k." ".$olyData["mapData"][$k]["name"]." ".$olyData["mapData"][$k]["type"].$br;
          $cellTxt .= "C";
        } else {
          if (!$addedInner)
            $cellTxt .= "x";
          $addedInner = 1;
          if (!isset($innerTypes[$olyData["mapData"][$k]["type"]]))
          {
            $q = substr($k, 1);
          } else {
            $q = $k;
          }
          $hover .= "&gt;".$q." ".htmlspecialchars($olyData["mapData"][$k]["name"], ENT_QUOTES);
          if (isset($olyData["mapData"][$k]["hidden"]))
          {
            $hover .= " [h]";
          }
          $hover .= $br;
        }
      }
    }
  }

  function formatShipCell($x, $n)
  {
    global $br, $cities, $hover, $cellTxt, $cellClass;
    $cellTxt = "";
    $cellClass = "map";
    $terrain = "";
    $hover = "";
    // find terrain
    if (is_array($x))
    {
      $hover = "[".$n."]";
      $cellClass = ($x["seen"] ? "seen" : "map");
      if (!empty($x["terrain"]))
      {
        $terrain = $x["terrain"];
        $hover .= " ".$terrain;
        $cellClass .= " ".$terrain;
      }
      $hover .= $br.$x["marked"];
      $cellTxt = $x["marked"];
      if (empty($cellTxt))
      {
        $cellTxt = "&nbsp;";
      }
    } else {
      $cellTxt = $x;
    }

  }
  
  function formatSkillCell($x, $n)
  {
    global $br, $hover, $cellTxt, $cellClass, $skillNames, $olyData;
    $cellTxt = "";
    $cellClass = "map";
    $terrain = "";
    $hover = "";
    $first = 1;
    // find terrain
    if (is_array($x))
    {
      $hover = "[".$n."]";
      $cellClass = ($x["seen"] ? "seen" : "map");
      if (!empty($x["terrain"]))
      {
        $terrain = $x["terrain"];
        $hover .= " ".$terrain;
        $cellClass .= " ".$terrain;
      }
      $hover .= $br;
      if (isset($x['city']))
      {
        $city = $olyData['mapData'][$x['city']];
        if (isset($city['skills']))
        {
          foreach ($city['skills'] as $sk => $v)
          {
            if ($first) $cellTxt .= 'S';
            $first = 0;
            $s = substr($sk, 2);
            $hover .= $skillNames[$s].' ['.$s.']'.$br;
          }
        }
        if (empty($cellTxt))
        {
         $cellTxt = "C";
        }
      }
      if (empty($cellTxt))
      {
        $cellTxt = ".";
      }
    } else {
      $cellTxt = $x;
    }
  }
  
  function formatWeatherCell($x, $n)
  {
    global $br, $hover, $cellTxt, $cellClass;
    $cellTxt = "";
    $cellClass = "map";
    $hover = "";
    if (is_array($x))
    {
      $hover = "[$n]";
      $cellClass = ($x["seen"] ? "seen" : "map");
      if (!empty($x['terrain']))
      {
        $hover .= " ".$x['terrain'].$br;
        $cellClass .= ' '.$x['terrain'];
      }
      $hover .= 'Seen turn '.$x['seen'];
      if ($x['foggy']) $cellTxt .= 'f';
      if ($x['rainy']) $cellTxt .= 'r';
      if ($x['windy']) $cellTxt .= 'w';
    } else {
      $cellTxt = $x;
    }
  }
  
  function formatGateCell($x, $n)
  {
    global $br, $cities, $hover, $cellTxt, $cellClass, $olyData;
    $cellTxt = "";
    $cellClass = "map";
    $terrain = "";
    $hover = "";
    // find terrain
    if (is_array($x))
    {
      $hover = "[".$n."]";
      $cellClass = ($x["seen"] ? "seen" : "map");
      if (!empty($x["terrain"]))
      {
        $terrain = $x["terrain"];
        $hover .= " ".$terrain;
        $cellClass .= " ".$terrain;
      }
      $hover .= $br;
      if (isset($olyData["gates"][$n]["dist"]))
      {
        if ($d = $olyData['gates'][$n]['dist'])
        {
          $hover .= "Checked on t".$olyData["gates"][$n]["turn"].", dist ".$d;
          $cellTxt .= "X";
        } else {
          // 0 dist means didn't find yet
          $cellTxt = sprintf('G%02d', $x['gateDest']['num']);
          $hover .= 'Gate Dest unknown';
          unset ($x['marked']);
        }
      }
      if ($x["marked"] > 0)
      {
        $hover .= "Gate Chances: ".$x["marked"].$br;
        $cellTxt .= $x["marked"];
      }
//      if (isset($x['gates']['id']))
      if (isset($olyData['gates'][$n]['gate']))
      {
        $zz = $olyData['gates'][$n]['gate'];
        if (isset($zz['id']))
        {
          // one gate
          $gateNum = sprintf('G%02d', $zz['num']);
          $cellTxt = $gateNum;
          $hover .= 'Gate '.$gateNum.' id: '.$zz['id'].' dest: '.$zz['dest'];
        } else {
          foreach ($zz as $k => $v)
          {
            $cellTxt = sprintf('G%02d+', $zz['num']);
            $gateNum = sprintf('G%02d', $zz[$k]['num']);
            $hover .= 'Gate '.$gateNum.' id: '.$zz[$k]['id'].' dest: '.$zz[$k]['dest'].$br;
          }
        }
      }
      if (isset($x['gateDest']))
      {
        $cellTxt .= sprintf('g%02d', $x['gateDest']['num']);
        $hover .= 'Gate Dest id: '.$x['gateDest']['id'].' from: '.$x['gateDest']['source'];
      }
      if (empty($cellTxt))
      {
        $cellTxt = "&nbsp;";
      }
    } else {
      $cellTxt = $x;
    }

  }
  
  function formatSewerCell($x, $n)
  {
    global $cellTxt, $cellClass, $hover, $br, $olyData;
    $cellTxt = $n;
    $cellClass = "";
    $hover = "[".$n."]".$br;
    $cellClass = ($x["seen"] ? "seen" : "map");
    $cellClass .= " tunnel";
    if (!empty($x["route"]))
    {
      // look for exits
      foreach ($x["route"] as $k => $v)
      {
        if ($olyData["mapData"][$k]["type"] == "sewer")
        {
          $cellTxt = strtoupper($cellTxt);
        }
        if ($v['dir'] == 'down' || $v['dir'] == 'up')
        {
          $cellTxt = strtoupper($cellTxt);
        }
        $hover .= "[".$k."] ".$olyData["mapData"][$k]["name"].$br;
      }
    }
  }

  function formatFaeryCell($x, $n)
  {
    global $cellTxt, $cellClass, $hover, $br, $olyData;
    $cellTxt = $n;
    $cellClass = "";
    $hover = "[".$n."]".$br;
    $cellClass = ($x["seen"] ? "seen" : "map");
    $cellClass .= " forest";
    if (!empty($x["inner"]))
    {
      // add city
      foreach ($x["inner"] as $k => $v)
      {
        if ($olyData["mapData"][$k]["type"] == "faery")
        {
          $cellTxt = strtoupper($cellTxt);
        }
        $hover .= "[".$k."] ".$olyData["mapData"][$k]["name"].$br;
      }
    }
  }
  
  function formatResourceCell($x, $n, $z)
  {
    global $cellTxt, $cellClass, $hover, $br, $olyData;
    $cellTxt = "";
    $cellClass = "";
    $hover = "";
    $cellClass = ($x["seen"] ? "seen" : "map");
    if (is_array($x))
    {
      $hover = "[".$n."]";
      if (!empty($x["terrain"]))
      {
        $terrain = $x["terrain"];
        $hover .= " ".$terrain;
        $cellClass .= " ".$terrain;
      }
      $hover .= $br;
      if (!empty($x['inner']))
      {
        //$cellTxt .= '.';
        foreach ($x['inner'] as $k => $v)
        {
          $t = $olyData["mapData"][$k]["type"];
          $hover .= '&gt;'.$k.' '.htmlspecialchars($olyData["mapData"][$k]["name"], ENT_QUOTES)." ".$t.$br; 
          if (isset($z[$t]))
          {
            $cellTxt .= $z[$t];
          }
        }
      }
    } else {
      $cellTxt = $x;
    }
  }
  
  function formatCivCell($x, $n)
  {
    global $cellTxt, $cellClass, $hover, $br;
    $cellTxt = "";
    $cellClass = "";
    $hover = "";
    $cellClass = ($x["seen"] ? "seen" : "map");
    if (is_array($x))
    {
      $hover = "[".$n."]";
      if (!empty($x["terrain"]))
      {
        $terrain = $x["terrain"];
        $hover .= " ".$terrain;
        if ($terrain == "ocean")
        {
          $cellClass .= " ocean";
        }
      }
      $hover .= $br;
      if (isset($x["civ"]) && $terrain != "ocean")
      {
        $cellClass .= " civ".$x["civ"];
      }
      formatCellInner($x, $n);
    } else {
      $cellTxt = $x;
    }
  }
  
  function formatGarrisonCell($x, $n)
  {
    global $br, $hover, $cellTxt, $cellClass;
    if (is_array($x))
    {
      if (is_array($x['ruler']))
      {
        $cellClass .= 'garrison ';
      }
    } else {
      $cellTxt = $x;
    }
    if ($cellClass == "")
    {
      $cellClass = 'map';
    }
  }
  
  function formatNormalCell($x, $n)
  {
    global $br, $cities, $hover, $cellTxt, $cellClass, $foundNobles, $friendlies, $olyData, $unfriendly;
    $cellTxt = "";
    $cellClass = "";
    $terrain = "";
    $hover = "";
    // find terrain
    if (is_array($x))
    {
      $hover = "[".$n."]";
      if ($x['group'])
      {
        $cellClass .= 'group'.$x['group'].' ';
      }
      if (is_array($x['ruler']))
      {
        $cellClass .= 'garrison ';
      }
      if ($x['seen'])
      {
        $cellClass .= 'seen';
      } else {
        $cellClass .= 'map';
      }
      if (!empty($x["terrain"]))
      {
        $terrain = $x["terrain"];
        $hover .= " ".$terrain;
        $cellClass .= " ".$terrain;
      }
      $hover .= $br;
      $unfriendly = 0;
      if (is_array($x['nobles']))
      {
        foreach ($x['nobles'] as $k => $v)
        {
        //print "loc: $n nob: $k fac:".$olyData['nobles'][$k]['faction']."\n";
          if (!$friendlies[$olyData['nobles'][$k]['faction']])
          {
            $unfriendly = 1;
          }
        }
      }
      $foundNobles += count($x["nobles"]);
      formatCellInner($x, $n);
      if ($foundNobles > 0)
      {
        $cellTxt .= "N";
      }
    /*   if ($unfriendly)
      {
        $cellTxt .= "E";
        $cellClass .= ' enemy';
      } */
      if (empty($cellTxt))
      {
        $cellTxt = ".";
      }
    } else {
      $cellTxt = $x;
    }
    if ($cellClass == "")
    {
      $cellClass = 'map';
    }
  }
  
  function displayData($n)
  {
    global $cellTxt, $cellClass, $hover, $foundNobles, $lvl;
    print "<td class=\"".$cellClass."\">";
    if (!empty($hover))
    {
      print "<a name=\"$n\" target=\"data\" href=\"location.php?id=$n";
      if ($lvl) print "&lvl=$lvl";
      print "\" onMouseOver='ddrivetip(\"$hover\",\"yellow\")'; onMouseout='hideddrivetip()'>";
    }
    if (empty($cellTxt))
    {
      print "&nbsp;&nbsp;";
    } else {
      print $cellTxt;
    }
    if (!empty($hover))
    {
      print "</a>";
    }
    print "</td>";
    $foundNobles = 0;
  }

  function setGate($num, $id, $source, $dest, $turn, $inst = -1)
  {
    global $olyData;
    if ($inst > 0)
    {
      $olyData['gates'][$source]['gate'][$inst]['num'] = $num;
    } else {
      $olyData['gates'][$source]['gate']['num'] = $num;
    }
    $olyData['mapData'][$dest]['gateDest']['id'] = $id;
    $olyData['mapData'][$dest]['gateDest']['source'] = $source;
    $olyData['mapData'][$dest]['gateDest']['num'] = $num;
    $olyData['mapData'][$dest]['gateDest']['turn'] = $turn;
  }

  function preProcessGates()
  {
    global $olyData;
    $gateNum = 1;
    foreach ($olyData['gates'] as $k => $v)
    {
      if ($v['dist'])
      {
        // no gate, just distance
        circle($k, $v['dist'], 0);
      } else if (isset($v['gate'])) {
        // got a gate - but is one or more?
        if (isset($v['gate']['id']))
        {
          // just one
          setGate($gateNum++, $v['gate']['id'], $k, $v['gate']['dest'], $v['turn']);
        } else {
          // multiple
          foreach ($v['gate'] as $n => $vv)
          {
            setGate($gateNum++, $v['gate'][$n]['id'], $k, $v['gate'][$n]['dest'], $v['turn'], $n);
          }
        }
      } else {
        // got a gate here somewhere, haven't id'd it yet.
        setGate($gateNum++, "99", $k, "", $v['turn']);
      }
    }
    foreach ($olyData['gates'] as $k => $v)
    {
      if (!is_numeric($v['dist'])) $v['dist'] = 0;
      for ($i = $v['dist'] - 1; $i >= 0; $i--)
        circle($k, $i, 1);
    }
  }
  
  function preProcessShips()
  {
  }
  
  function displayCell($x, $n)
  {
    global $mapKind, $resourceTypes, $questTypes;
    if ($mapKind == "civ") {
      formatCivCell($x, $n);
    } else if ($mapKind == "gates") {
      formatGateCell($x, $n);
    } else if ($mapKind == 'faery') {
      formatFaeryCell($x, $n);
    } else if ($mapKind == 'skill') {
      formatSkillCell($x, $n);
    } else if ($mapKind == 'sewer') {
      formatSewerCell($x, $n);
    } else if ($mapKind == 'weather') {
      formatWeatherCell($x, $n);
    } else if ($mapKind == 'resource') {
      formatResourceCell($x, $n, $resourceTypes);
    } else if ($mapKind == 'quest') {
      formatResourceCell($x, $n, $questTypes);
    } else if ($mapKind == 'garrison') {
      formatGarrisonCell($x, $n);
    } else {
      formatNormalCell($x, $n);
    }
    displayData($n);
  }
  
  function makeCivKey()
  {
    print "<table><tr>";
    for ($i = 0; $i < 9; $i++)
    {
      print "<td class=\"civ$i\">$i</td>";
    }
    print "</tr></table>";
  }
  
  function makeAltMap($locId, $x, $y)
  {
    global $olyData, $altMap, $mapOffsets, $ymax, $ymin, $xmax, $xmin;
    //print "$locId $x $y ";
    // need to check for wrap, see if locid matches either way
    $match = ($altMap[$xmin][$y] == $locId) || ($altMap[$xmax][$y] == $locId) ||
      ($altMap[$x][$ymin] == $locId) || ($altMap[$x][$ymax] == $locId);
    if (!isset($altMap[$x][$y]) && !$match)
    {
      // data to process
      $altMap[$x][$y] = $locId;
      if ($x > $xmax) $xmax = $x;
      if ($x < $xmin) $xmin = $x;
      if ($y > $ymax) $ymax = $y;
      if ($y < $ymin) $ymin = $y;
      if (is_array($olyData["mapData"][$locId]["route"]))
      {
        foreach ($olyData["mapData"][$locId]["route"] as $k => $v)
        {
          makeAltMap($k, $x + $mapOffsets[$v["dir"]]["x"], $y+$mapOffsets[$v["dir"]]["y"]);
        }
      }
    }
  }
    
  function makeAltTable($s)
  {
    global $olyData, $altMap, $ymax, $ymin, $xmax, $xmin, $altStart, $center;
    // pick a start point
    $altMap = array();
    // now start walking to make map.
    if (strlen($center))
    {
      makeAltMap($center, 1, 1);
    } else {
      makeAltMap($altStart[$s], 1, 1);
    }
    //print_r($altMap);
    print "<table>";
    for ($y = $ymin; $y <= $ymax; $y++)
    {
      print "<tr>";
      for ($x = $xmin; $x <= $xmax; $x++)
      {
        $n = $altMap[$x][$y];
        displayCell($olyData["mapData"][$n], $n);
      }
      print "</tr>\n";
    }
    print "</table>\n";
    //print_r($altMap);
  }
  
  function makeHead()
  {
    global $mapKind, $title, $suffix;
    print <<< EOH
<html><head><link rel="stylesheet" type="text/css" href="map.css" title="Default" />
<link rel="alternate stylesheet" type="text/css" href="map2.css" title="Alternate" />
<script type="text/javascript" src="styleswitcher.js"></script>
EOH;
    print "<title>".$title[$mapKind]."</title></head><body>";

    // script below from: http://www.dynamicdrive.com/dynamicindex5/dhtmltooltip.htm
    print <<< EOHEAD
<div id="dhtmltooltip"></div>

<script type="text/javascript">

/***********************************************
* Cool DHTML tooltip script- © Dynamic Drive DHTML code library (www.dynamicdrive.com)
* This notice MUST stay intact for legal use
* Visit Dynamic Drive at http://www.dynamicdrive.com/ for full source code
***********************************************/

var offsetxpoint=-60 //Customize x offset of tooltip
var offsetypoint=20 //Customize y offset of tooltip
var ie=document.all
var ns6=document.getElementById && !document.all
var enabletip=false
if (ie||ns6)
var tipobj=document.all? document.all["dhtmltooltip"] : document.getElementById? document.getElementById("dhtmltooltip") : ""

function ietruebody(){
return (document.compatMode && document.compatMode!="BackCompat")? document.documentElement : document.body
}

function ddrivetip(thetext, thecolor, thewidth){
if (ns6||ie){
if (typeof thewidth!="undefined") tipobj.style.width=thewidth+"px"
if (typeof thecolor!="undefined" && thecolor!="") tipobj.style.backgroundColor=thecolor
tipobj.innerHTML=thetext
enabletip=true
return false
}
}

function positiontip(e){
if (enabletip){
var curX=(ns6)?e.pageX : event.clientX+ietruebody().scrollLeft;
var curY=(ns6)?e.pageY : event.clientY+ietruebody().scrollTop;
//Find out how close the mouse is to the corner of the window
var rightedge=ie&&!window.opera? ietruebody().clientWidth-event.clientX-offsetxpoint : window.innerWidth-e.clientX-offsetxpoint-20
var bottomedge=ie&&!window.opera? ietruebody().clientHeight-event.clientY-offsetypoint : window.innerHeight-e.clientY-offsetypoint-20

var leftedge=(offsetxpoint<0)? offsetxpoint*(-1) : -1000

//if the horizontal distance isn't enough to accomodate the width of the context menu
if (rightedge<tipobj.offsetWidth)
//move the horizontal position of the menu to the left by it's width
tipobj.style.left=ie? ietruebody().scrollLeft+event.clientX-tipobj.offsetWidth+"px" : window.pageXOffset+e.clientX-tipobj.offsetWidth+"px"
else if (curX<leftedge)
tipobj.style.left="5px"
else
//position the horizontal position of the menu where the mouse is positioned
tipobj.style.left=curX+offsetxpoint+"px"

//same concept with the vertical position
//if (bottomedge<tipobj.offsetHeight)
//tipobj.style.top=ie? ietruebody().scrollTop+event.clientY-tipobj.offsetHeight-offsetypoint+"px" : window.pageYOffset+e.clientY-tipobj.offsetHeight-offsetypoint+"px"
//else
tipobj.style.top=curY+offsetypoint+"px"
tipobj.style.visibility="visible"
}
}

function hideddrivetip(){
if (ns6||ie){
enabletip=false
tipobj.style.visibility="hidden"
tipobj.style.left="-1000px"
tipobj.style.backgroundColor=''
tipobj.style.width=''
}
}

document.onmousemove=positiontip

</script>
EOHEAD;
  print $title[$mapKind].' '.$suffix."<br />";
//foreach ($mapKinds as $k => $v)
//{
//  print "<a href=\"map.php?map=$k\">$v</a> ";
//}
//print <<<EOHEAD2
//<a href=".">Report Dir</a>
//<form action="map.php" method="post">Zoom - id: 
//<input type="text" name="zoomLoc" size="5" maxlength="5" /> size:
//<input type="text" name="zoomSize" size="5" maxlength="5" />
//<input type="submit" name="zoomGo" value="Go" />
//EOHEAD2;
//  print "<input type=\"hidden\" name=\"map\" value=\"$mapKind\" /></form>";
//<a href="#" onclick="setActiveStylesheet('Default'); return false;">Default Style</a> 
//<a href="#" onclick="setActiveStylesheet('Alternate'); return false;">Alternate Style</a>
  }
  
  function makeTail()
  {
    print "</body></html>\n";
  }
  
  function makeRows()
  {
    global $olyData, $mapKind, $mColStart, $mColEnd, $mRowStart, $mRowEnd;
    //mergeData("data.xml");
    if ($mapKind == "civ")
    {
      makeCivKey();
    }

    print "<table>\n";
    topRow();
    $currRow = "";
    if ($mapKind == "gates")
    {
      preProcessGates();
    } else if ($mapKind == "ship")
    {
      preProcessShips();
    }
    for ($i = $mRowStart; $i <= $mRowEnd; $i++)
    {
      print "<tr>";
      displayCell(letterCell($i), 0);
      for ($j = $mColStart; $j <= $mColEnd; $j++)
      {
        $x = makeLoc($i, $j);
        displayCell($olyData["mapData"][$x], $x);
      }
      print "</tr>\n";
    }
    print "</table>\n";
  }
  
  function figureZoom()
  {
    global $zoom, $mColStart, $mColEnd, $mRowStart, $mRowEnd;
    // set boundaries for map, based on zoom
    if (is_array($zoom))
    {
      // where's the center of interest?
      $mRowStart = reverseLetterCell($zoom['id']);
      $mColStart = substr($zoom['id'], 2);
      $mRowEnd = min($mRowStart + ($zoom['size'] / 2), 99);
      $mRowStart = max($mRowStart - ($zoom['size'] / 2), 0);
      $mColEnd = min($mColStart + ($zoom['size'] / 2), 99);
      $mColStart = max($mColStart - ($zoom['size'] / 2), 0);
    } else {
      $mColStart = 0;
      $mColEnd = 99;
      $mRowStart = 0;
      $mRowEnd = 99;
    }
  }
  // begin main code here
  
  makeHead();
  figureZoom();
  mergeData('data.xml');
  if (isset($altStart[$mapKind])) {
    makeAltTable($mapKind);
  } else {
    makeRows();
  }
  makeTail();

?>

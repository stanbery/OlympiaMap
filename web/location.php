<?php

// generate a report for an area

include_once("oly.php");

$locId = $_GET["id"];
$allData = (isset($_GET["all"]) ? 1 : 0);
$lvl = $_REQUEST['lvl'];
$olyData = array();
$maxCityDist = 11;

  function makeHead()
  {
    print "<html>\n<head>\n<link rel=\"stylesheet\" type=\"text/css\" href=\"map.css\" />";
    print "</head>\n<body>\n";
  }
  
  function makeTail()
  {
    print "</body></html>\n";
  }

  function printit($x, $y)
  {
    global $br;
    if (isset($y))
    {
      if (is_array($y))
      {
        print "<h2>".$x.$y[0]."</h2>";
      } else {
        print "<h2>".$x.$y."</h2>";
      }
    }
  }
  
  function printGates($id)
  {
    global $olyData, $br;
    $g = $olyData["mapData"][$id]["gates"];
    if ($g["dist"])
    {
      print "Gate detection here on turn ".$g["turn"].$br;
      print "Nearest gate is ".$g["dist"]." provinces away.".$br;
    }
    if (isset($g['id']))
    {
     print "Gate ".$g['id']." leads to ".$g['dest'].$br;
    }
  }
  
  function printRoutes($id)
  {
    global $olyData, $br, $lvl;
    $lid = $olyData["mapData"][$id]["route"];
    if (isset($lid))
    {
      print "<h2>Routes for $id:</h2>";
      foreach ($lid as $did => $q)
      {
        if ($q["days"] < 0)
        {
          print $q['dir'].": [".$did."] impassable";
        } else {
          if (is_array($q["days"]))
          {
            // find lowest number, not negative
            $days = 99;
            foreach ($q["days"] as $d)
            {
              $days = ($d < $days && $d > 0 ? $d : $days);
            }
          } else {
            $days = $q['days'];
          }
          if ($q['dir'] == 'down')
          {
            $dir = '<a target="map" href="map.php?map=sewer&lvl='.($lvl+1).'&center='.$did.'">'.$q['dir'].'</a>';
          } else if ($q['dir'] == 'up') {
            $dir = '<a target="map" href="map.php?map=sewer&lvl='.($lvl-1).'&center='.$did.'">'.$q['dir'].'</a>';
          } else {
            $dir = $q['dir'];
          }
          print "$dir: [$did] $days days";
          if (isset($q["hidden"]))
          {
            print " hidden; seen by : ";
            foreach ($q["hidden"] as $fid => $z)
            {
              print $fid." ";
            }
          }
        }
        print $br;
      }
      $x = $olyData["mapData"][$id]["inner"];
      if (isset($x))
      {
        foreach ($x as $k => $v)
        {
          printRoutes($k);
        }
      }
    }
  }
  
  function printSkills($id)
  {
    global $olyData, $br, $skillNames;
    $lid = $olyData["mapData"][$id]["inner"];
    if (isset($lid))
    {
      foreach ($lid as $iid => $q)
      {
        $ln = $olyData["mapData"][$iid]["skills"];
        if (isset($ln))
        {
          print "<h2>Skills for $iid:</h2>";
          foreach ($ln as $k => $v)
          {
            $x = substr($k, 2);
            print $skillNames[$x]." [".$x."]".$br;
          }
        }
      }
    }
  }
  
  function printMarket($id)
  {
    global $olyData, $br, $itemNames;
    $lid = $olyData["mapData"][$id]["inner"];
    if (isset($lid))
    {
      foreach ($lid as $iid => $q)
      {
        $ln = $olyData["mapData"][$iid]["market"];
        if (isset($ln))
        {
          print "<h2>Market for $iid:</h2>";
          print "<table><tr><th>trade</th><th>who</th><th>item</th><th>qty</th><th>amount</th></tr>";
          foreach ($ln as $trade => $stuff)
          {
            foreach ($stuff as $who => $what)
            {
              foreach ($what as $item => $more)
              {
                // FIXME
                $i = substr($item, 1);
                print "<tr><td>$trade</td><td>".substr($who,1)."</td><td>".$itemNames[$i]." [$i]</td><td>".
                $more["qty"]."</td><td>".$more["price"]."</td></tr>";
              }
            }
          }
          print "</table>";
        }
      }
    }
  }
  
  function printGarrison($id)
  {
    global $olyData, $br, $itemNames;
    $gid = $olyData['mapData'][$id]['garrison'];
    if (isset($gid))
    {
      print "<h2>Garrison Inventory:</h2>";
      print "<table><tr><td>item</td><td>qty</td></tr>";
      foreach ($olyData['garrison'][$gid]['items'] as $it => $q)
      {
        $i = substr($it, 1);
        $n = (isset($itemNames[$i]) ? $itemNames[$i] : "Unknown");
        print "<tr><td>$n [$i]</td><td>$q</td></tr>";
      }
      print "</table>";
    }
  }
  
  function printNobles($id, $turn)
  {
    global $olyData, $allData, $br;
    $ln = $olyData["mapData"][$id]["nobles"];
    if (isset($ln))
    {
      foreach ($ln as $k => $v)
      {
        $n = $olyData["nobles"][$k];
        if (isset($n))
        {
          if ($allData || ($n["seen"] > $turn - 3))
          {
            print "<a href=\"http://oly.larrware.com/index.php/Nobles/".substr($k,1)."\">[".
              substr($k,1)."] ".$n["name"]."</a>";
            if (isset($n["faction"]))
            {
              print " [".$n["faction"]."]";
            }
            if (isset($n["seen"]) && $n["seen"] != $turn)
            {
              print " - last seen turn ".$n["seen"];
            }
            print $br;
          }
        } else {
          print "Noble ".$k." details not found.".$br;
        }
      }
    }
  }

  function printShipDetails($set, $turn)
  {
    global $olyData, $allData, $br;
    if (isset($set))
    {
      foreach ($set as $s => $q)
      {
        if ($allData || ($olyData["ships"][$s]["seen"] > $turn - 3))
        {
          print "[".substr($s,1)."] ".$olyData["ships"][$s]["name"]." - ".$olyData["ships"][$s]["kind"];
          if ($olyData["ships"][$s]["seen"] != $turn)
          {
            print " - seen ".$olyData["ships"][$s]["seen"];
          }
          print $br;
        }
      }
    }
  }
  
  function printShips($id, $turn)
  {
    global $olyData;
    print "<h2>Ships:</h2>";
    printShipDetails($olyData["mapData"][$id]["ships"], $turn);
    $in = $olyData["mapData"][$id]["inner"];
    if (isset($in))
    {
      foreach ($in as $k => $v)
      {
        printShipDetails($olyData["mapData"][$k]["ships"], $turn);
      }
    }
  }
  
  function printInner($id, $first, $turn)
  {
    global $br, $olyData;
    // print all inner for this loc, then recurse
    $printed = 0;
    if (isset($olyData["mapData"][$id]["inner"]))
    {
      foreach ($olyData["mapData"][$id]["inner"] as $k => $v)
      {
        //if (!$printed && $first)
          print "<h2>Inner locations:</h2>";
        $printed = 1;
        print "[".$k."] ".$olyData["mapData"][$k]["name"].', '.$olyData['mapData'][$k]['type'];
        if (isset($olyData["mapData"][$k]["hidden"]))
        {
          print " [h]".$br;
          print "-- Seen by:";
          foreach ($olyData["mapData"][$k]["hidden"] as $k1 => $v1)
          {
            print $br."---- Faction ".$k1;
          }
        }
        print $br;
        print "<h2>Nobles for inner location $k:</h2>";
        printNobles($k, $turn);
        printInner($k, 1, $turn);
      }
    }
  }

  function makeLink($id)
  {
    return "<a href=\"location.php?id=$id\">$id</a>";
  }
  
  function printNearbyCities($id)
  {
    global $olyData, $br, $maxCityDist;
    print "<h2>Nearby Cities:</h2>";
    print "(Items marked with '*' are from turn report.)<br />";
    // iterate cities, find ones within 10 of our location
    $x = reverseLetterCell($id);
    $y = substr($id, 2);
    $locCity = $olyData['mapData'][$id]['city'];
    foreach ($olyData['cities'] as $xn => $yn)
    {
      $x1 = substr($xn, 1);
      $xsum = abs($x1 - $x);
      if ($xsum < $maxCityDist)
      {
        foreach ($yn as $yz => $c)
        {
          $y1 = substr($yz, 1);
          $ysum = abs($y1 - $y);
          if ((($xsum+$ysum) < $maxCityDist) && ($xsum+$ysum) > 0)
          {
            if ($olyData['mapData'][$locCity]['nearby'][$c])
            {
              print "* ";
            }
            print $olyData['mapData'][$c]['name']." [".$c."] in [".
              makeLink(makeLoc($x1, $y1))."], ".($xsum+$ysum)." away".$br;
          }
        }
      }
    }
  }

  function printOwned($id)
  {
    global $olyData;
    if (is_array($olyData['mapData'][$id]['ruler']))
    {
      $x = $olyData['mapData'][$id]['ruler'];
      print "<h2>Ruler: [".$x['id'].'] in castle ['.$x['castle'].'] in ['.$x['locid'].']</h2>';
    }
  }
  
  function makeData($id)
  {
    global $olyData, $br;
    mergeData("data.xml");
    //print $br."<a href=\"map.php#$id\">Map</a> ";
    $turn = $olyData["mapData"][$id]["seen"];
    $civ = $olyData["mapData"][$id]["civ"];
    $terr = $olyData["mapData"][$id]["terrain"];
    $group = $olyData['mapData'][$id]['group'];
    if (file_exists('editloc.php'))
    {
      print "<a href=\"editloc.php?locId=$id&amp;turnNum=$turn&amp;civLvl=$civ&amp;terrain=$terr&amp;groupNum=$group\">Edit</a> ";
    }
    print "<a target=\"_blank\" href=\".\">Report Directory</a> ";
    print "<h1>Data for $id</h1>";
    print "<div class=\"region\">";

    printit("Region Name: ", $olyData["mapData"][$id]["name"]);
    printit("Terrain: ", $olyData["mapData"][$id]["terrain"]);
    printit("Civ: ", $olyData["mapData"][$id]["civ"]);
    printit("Last seen: turn ", $turn);
    if ($olyData['mapData'][$id]['rainy']) print "<h2>Rainy</h2>";
    if ($olyData["mapData"][$id]["windy"]) print "<h2>Windy</h2>";
    if ($olyData["mapData"][$id]["foggy"]) print "<h2>Foggy</h2>";
    printOwned($id);
    printGates($id);
    printNearbyCities($id);
    printRoutes($id);
    printSkills($id);
    printMarket($id);
    printGarrison($id);
    print "<h2>Nobles for province:</h2>";
    printNobles($id, $turn);
    print "</div><div class=\"subregion\">";
    printInner($id, 1, $turn);
    printShips($id, $turn);
    print "</div>";
    print $br."<a href=\"map.php#$id\">Map</a> ";
    clearstatcache();
    if (file_exists('editloc.php'))
    {
      print "<a href=\"editloc.php?locId=$id&amp;turnNum=$turn&amp;civLvl=$civ&amp;terrain=$terr\">Edit</a> ";
    }
    print "<a href=\".\">Report Directory</a> ";
  }
  
  makeHead();
  makeData($locId);
  makeTail();
?>
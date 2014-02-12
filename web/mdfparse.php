<?php

include_once('oly.php');

// parse an MDF file
// #id |type|hidden|visi|prov|name|out|other|North|South|East|West|Up|Down|owner|civ|source|barrier|region

$mdfDirs = array("north", "south", "east", "west");

function parseMDF($fn)
{
  global $olyData, $lnum, $terrainTypes, $mdfDirs;
  $castles = array();
  if (strlen($fn) == 0)
  {
    print "No file!\n";
    return;
  }
  $f = file($fn);
  // now we got it, explode it
  foreach ($f as $lnum => $ln)
  {
    if ($lnum != 0)
    {
      $d = explode("|", $ln);
      $id = $d[0];
      debug(1, "id: $id");
      // terrain
      $t = explode(" ", $d[1]);
      debug(1, "terrain: ".$t[0]);
// added - see if it changes things.
if ($t[0] == "faery")
{
  print_r($d);
}
      $qq = "";
      // is it a city/inner?
      if (strlen($d[7]) > 0)
      {
        $d[4] = $d[7];
      }
      if (strlen($d[4]) > 0)
      {
        if (is_numeric($id))
        {
          $qq = "i";
        } else {
          $qq = "";
        }
        if ($d[4] != $id)
        {
          debug(1, "inner: ".$d[4]);
          $olyData["mapData"][$d[4]]["inner"][$qq.$id] = array();
          unset($olyData['mapData'][$qq.$id]['terrain']);
          $olyData['mapData'][$qq.$id]['type'] = $t[0];
          $olyData['mapData'][$qq.$id]['parent'] = $d[4];
          if ($t[0] == "city")
          {
            $olyData['cities']['x'.reverseLetterCell($d[4])]['y'.substr($d[4], 2)] = $id;
          }
        }
      }
      if ($t[0] == "lane") $t[0] = 'oroute';
      if ($t[0] == 'safe') $t[0] = 'city';
      if (strlen($t[0]) == 0)
      {
        if ($terrainTypes[strtolower($t[5])])
        {
          $olyData["mapData"][$qq.$id]["terrain"] = strtolower($t[5]);
        } else {
          $olyData["mapData"][$qq.$id]["terrain"] = 'unknown';
        }
      } else {
        $olyData["mapData"][$qq.$id]["terrain"] = $t[0];
      }
      // seen?
      if (strlen($d[3]) > 0)
      {
        debug(1, "seen: ".$d[3]);
        $n = substr($d[3], 1);
        if ($n > $olyData['mapData'][$qq.$id]['seen'])
        {
          $olyData["mapData"][$qq.$id]["seen"] = $n;
        }
      }
      // name
      if (strlen($d[5]) > 0)
      {
        $olyData['mapData'][$qq.$id]['name'] = $d[5];
      }
      // garrison
      if (strlen($d[14]) > 0)
      {
        $g = explode(':', $d[14]);
        $castles[$qq.$id] = 'i'.$g[1];
        $olyData['mapData'][$qq.$id]['ruler']['castle'] = $g[1];
        $olyData['mapData'][$qq.$id]['ruler']['locid'] = 'x';
        $olyData['mapData'][$qq.$id]['ruler']['id'] = $g[1];
      }
      // civ
      if (strlen($d[15]) > 0)
      {
        debug(1, "civ: ".$d[15]);
        $olyData['mapData'][$qq.$id]['civ'] = $d[15];
      }
      // directions - only useful for faery
      for ($n = 8; $n < 12; $n ++)
      {
        if (strlen($d[$n]) > 0)
        {
debug(1, "route: ".$d[$n]);
          if (!isset($olyData["mapData"][$qq.$id]["route"][$d[$n]]["dir"]))
          $olyData["mapData"][$qq.$id]["route"][$d[$n]]["dir"] = $mdfDirs[$n-8];
        }
      }
    }
  }
  foreach ($castles as $k => $v)
  {
    $olyData['mapData'][$k]['ruler']['locid'] =
      $olyData['mapData'][$v]['parent'];
  }
}

function parseMDFcity($fn)
{
  global $olyData, $lnum;
  if (strlen($fn) == 0)
  {
    print "No file!\n";
    return;
  }
  $f = file($fn);
  $lnum = 0;
  $maxLines = count($f);
  // now we got it, explode it
  while ($lnum <= $maxLines)
  {
    // parse city info
    // [n68] Hollowview, city, in province [ap61]
    debug(1, $f[$lnum]);
    $matched = preg_match('/\[(\S+)\] (\w+), (\w+), in province \[(\S+)\]/', $f[$lnum], $city);
    if (!$matched)
    {
      print "Error! No match! $ln\n";
      $lnum++;
    } else {
      $cid = $city[1];
      setCity($city[1], $city[4], $city[2]);
      $lnum++;
      // skills
      $lnum++;debug(1, $f[$lnum]);
      while (strpos($f[$lnum], '['))
      {
        debug(1, "skills");
        $sk = explode(',', $f[$lnum]);
        foreach ($sk as $z)
        {
          $skf = preg_match('/\[(\S+)\]/', $z, $m);
          if ($skf)
          {
            $olyData["mapData"][$cid]["skills"]["sk".$m[1]] = 1;
          }
        }
        unset($sk);
        $lnum++;
      }
      // market
      $lnum += 4;debug(1, $f[$lnum]);
      do {
        $x = preg_match('/(\w+)\s+(\w+)\s+(\d+)\s+(\d+)\s+(\S+)\s+(.+)\[(\w+)\]/', $f[$lnum], $match);
        if (strlen($match[1]) == 0) print "Market error: $lnum";
        $olyData["mapData"][$cid]["market"][$match[1]]["m".$match[2]]["i".$match[7]]["price"] = $match[3];
        $olyData["mapData"][$cid]["market"][$match[1]]["m".$match[2]]["i".$match[7]]["qty"] = $match[4];
        $olyData["market"]["i".$match[7]][$cid]["price"] = $match[3];
        $olyData["market"]["i".$match[7]][$cid]["qty"] = $match[4];
        $olyData["market"]["i".$match[7]][$cid]["turn"] = $turnNum;
        $olyData['market']['i'.$match[7]][$cid][$match[1]] = 1;
        $olyData['market']['i'.$match[7]]['name'] = trim($match[6]);
        if ($debug >= 1) print_r($match);
        $lnum++;debug(1, $f[$lnum]);
      } while (strlen($f[$lnum]) > 2);
      $lnum++;
    }
  }
}

?>

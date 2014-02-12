<?php

include_once("oly.php");

// some code to parse turn reports
$orders = array();
$currLine = 0;
$currToken = 0;
$scanFilter = " \n\r\t";
$maxLines = 0;
$tok = "";

$parseLocDays = 1;


// use to search for something

function getToken($reset = 0)
{
  global $tok, $orders, $currLine, $scanFilter, $maxLines;
  if ($reset)
  {
    $tok = false;
  } else {
    $tok = strtok($scanFilter);
  }
  if ($tok === false)
  {
    do
    {
      $currLine++;
      $tok = strtok($orders[$currLine], $scanFilter);
      debug(5, "");
    } while ($tok === false && $currLine <= $maxLines);
  }
  debug(5, "get [".$currLine."]: $tok<br />\n");
  return $tok;
}

function findToken($x)
{
  global $tok, $currLine, $maxLines;
  debug(5, "findToken: $x<br />\n");
  $found = ($tok == $x);
  while (!$found && $currLine <= $maxLines)
  {
    getToken();
    $found = ($tok == $x);
  }
  return $tok;
}

function findFirst($x)
{
  global $tok, $currLine, $maxLines;
  debug(5, "findFirst: $x<br />\n");
  $found = in_array($tok, $x);
  while (!$found && $currLine <= $maxLines)
  {
    getToken();
    $found = in_array($tok, $x);
  }
  return $tok;
}

function findPrefix($x)
{
  global $tok, $currLine, $maxLines;
  debug(5, "findPrefix: $x<br />\n");
  $found = strstr($tok, $x);
  while (!$found && $currLine <= $maxLines)
  {
    getToken();
    $found = strstr($tok, $x);
  }
  return $tok;
}
	 
function skipLines()
{
  global $orders, $currLine, $maxLines;
  while (strlen(trim($orders[$currLine])) && $currLine <= $maxLines)
  {
    debug(5, "skip: ".$orders[$currLine]."<br />\n");
    $currLine++;
  }
  getToken(1);
}

function skipBlank()
{
  global $orders, $currLine, $maxLines;
  while(!strlen(trim($orders[$currLine])) && $currLine <= maxLines)
  {
    debug(5, "skip: ".$orders[$currLine]."<br />\n");
    $currLine++;
  }
}

function skipUntil($s)
{
  global $orders, $currLine, $maxLines;
  // a combination of skipLines() and findPrefix() -
  // we want to walk text until we hit blank, or until we find that prefix
  do
  {
    $found = strpos($orders[$currLine+1], $s);
    $done = $found || strlen(trim($orders[$currLine+1])) <= 2 ||
      $currLine == $maxLines;
    if (!$done)
      $currLine++;
  } while (!$done);
  return $found;
}

function getName()
{
  global $orders, $currLine;
  // if there's a day on it, strip it first
  $s = preg_replace('/^[ ]*\d+:[ ]*/','', $orders[$currLine]);
  debug(5, "getName: $s");
  return trim(substr($s, 0, strpos($s, "[")-1));
}

function idify($s)
{
  return preg_replace(array("/\[/","/\]/","/,/","/\./","/ /","/\t/","/\n/"),array("","","","","","",""),$s);
}

function nobleName($s)
{
  $cname = trim($s);
  if ($cname[0] == '*')
  {
    $cname = trim(substr($cname, 2));
  }
  return $cname;
}

function setNoble($id, $name, $turn, $faction, $location)
{
  global $olyData, $br, $eliminate;
  if (!$eliminate[$id])
  {
    if ($location != "")
    {
      if (!empty($olyData["nobles"][$id]["loc"]))
      {
        // remove noble from location contents
        debug(2, "remove $id from ".$olyData["nobles"][$id]["loc"].$br);
        unset($olyData["mapData"][$olyData["nobles"][$id]["loc"]]["nobles"][$id]);
      }
      // put noble into a location's contents
      debug(2, "add $id to $location".$br);
      $olyData["mapData"][$location]["nobles"][$id] = array();
      $olyData["nobles"][$id]["loc"] = $location;
    }
    if ($faction != "")
    {
        $olyData["nobles"][$id]["faction"] = $faction;
    }
    $olyData["nobles"][$id]["name"] = $name;
    $olyData["nobles"][$id]["seen"] = $turn;
  } else {
    debug (0, "Eliminated $id");
  }
}

function setGarrison($id, $turn, $location)
{
  global $olyData;
  $olyData['garrison'][$id]['loc'] = $location;
  $olyData['garrison'][$id]['seen'] = $turn;
  $olyData['mapData'][$location]['garrison'] = $id;
}

// function adjustGarrisonInv($id, $item, $qty)
// {
  // global $olyData;
  // if (isset($olyData['garrison'][$id]['items']['i'.$item]))
  // {
    // $olyData['garrison'][$id]['items']['i'.$item] += $qty;
  // } else {
    // $olyData['garrison'][$id]['items']['i'.$item] = $qty;
  // }
// }

function getEntity()
{
  global $orders, $currLine, $br, $tok;
  // get an entity from the data
  // current token is id
  $x = $tok;
  $id = idify($tok);
  // get line, get name (everything up to token)
  $name = nobleName(getName());
  // it may have a 'kind' immediatly after - try getting that.
  $kind = idify(preg_replace('/(\w+)(.*)/', '$1', substr($orders[$currLine], strpos($orders[$currLine], $x)+strlen($x))));
  debug(2, "getEntity: $x $id $name $kind - zz".$br);
  return array("id" => $id, "name" => $name, "kind" => $kind);
}

function cleanDays($dayMark)
{
  global $orders, $currLine, $maxLines;
  $currLine ++;
  $i = $currLine;
  // $dayMark is where the ':' is
  $s = substr($orders[$currLine], 0, $dayMark);
  $d = strlen($s);
  debug (3, "cleanDays: pos: $dayMark str: $s len: $s");
  while ((substr($orders[$i], 0, $d) == $s) && ($i <= $maxLines))
  {
    $orders[$i] = trim(substr($orders[$i], $d + 1));
    $i ++;
  }
  debug (3, "cleanDays: $currLine $i");
  getToken(1);
  return $i;
}

function handleNobleDays($id, $name)
{
  global $myFaction, $turnNum, $olyData, $orders, $currLine, $maxLines, $numbers;
  // ok, what can happen?
  debug (2, "handleNobleDays : ".$currLine);
  $currLine += 2;
  debug (3, "line: ".$currLine." ".$orders[$currLine]);
  // walk each day
  while (strlen(trim($orders[$currLine])) >= 1 && $currLine <= $maxLines)
  {
    // moving
    if (preg_match("/: Arrival at .* \[(\S+)\]/", $orders[$currLine], $move))
    {
      if (is_mapcell($move[1]))
      {
        debug(3, "$name [$id] moved to ".$move[1]);
        setNoble("n$id", $name, $turnNum, "", $move[1]);
      }
    }
    // explore
    if (strpos($order[$currLine], "Exploration of") !== false)
    {
      // didn't find anything; note attempt
    }
    // gate
    if (strpos($orders[$currLine], "There are no gates here") !== false)
    {
      debug(3, "gate handling");
      $currLine ++;
      $loc = $olyData["nobles"]["n".$id]["loc"];
       debug(3, "Map: ".$loc);
      // if it's not a map loc, walk up til we find a map loc
      while (!is_mapcell($loc))
      {
        debug(3, "Map: ".$loc);
        $loc = $olyData['mapData'][$loc]['parent'];
      }
      $q = preg_match('/(\w+) province/', $orders[$currLine], $match);
      if ($q)
      {
        if (strlen($match[1]) == 0)
        {
          // A gate exists somewhere in this province.
          $olyData['mapData'][$loc]['gates']['dist'] = 0;
          $olyData['gates'][$loc]['dist'] = 0;
        } else {
          $olyData["mapData"][$loc]["gates"]["dist"] = $numbers[$match[1]];
          $olyData['gates'][$loc]['dist'] = $numbers[$match[1]];
        }
        $olyData["mapData"][$loc]["gates"]["turn"] = $turnNum;
      } else {
        debug(1, "Unknown gate state!");
      }
    }
    if (strpos($orders[$currLine], "Gates here:") !== false)
    {
      // get gates
      //10: Gates here:
      //10:    Gate [x110], to Plain [ak60]
      debug(2, "Found gate!");
      $loc = $olyData["nobles"]["n".$id]["loc"];
      // if it's not a map loc, walk up til we find a map loc
      while (!is_mapcell($loc))
      {
        $loc = $olyData['mapData'][$loc]['parent'];
      }
      $currLine++;
      $q = preg_match('/Gate \[(\S+)\], to \S+ \[(\S+)\]/', $orders[$currLine], $match);
      if ($q)
      {
        debug(2, "Gate: ".$match[1]." at ".$match[2]);
        $olyData['mapData'][$loc]['gates']['id'] = $match[1];
        $olyData['mapData'][$loc]['gates']['dest'] = $match[2];
        $olyData['gates'][$loc]['id'] = $match[1];
        $olyData['gates'][$loc]['dest'] = $match[2];
      }
    }
    // see if we got a scry attempt
    if (strpos($orders[$currLine], "A vision"))
    {
      debug(3, "Vision!");
      // ok, handle it like a map location, but need to strip days off the lines
      $endLine = cleanDays(strpos($orders[$currLine], ':'));
      // as a result of cleaning days, need to set the 'end' of the area, so we can
      // know when to quit parsing the sub-area.
      findPrefix("[");
      handleMap($endLine);
    }
    // if (strpos($orders[$currLine], 'Installed Garrison'))
    // {
      // $loc = $olyData["nobles"]["n".$id]["loc"];
      //if it's not a map loc, walk up til we find a map loc
      // while (!is_mapcell($loc))
      // {
        // $loc = $olyData['mapData'][$loc]['parent'];
      // }
      // $olyData['mapData'][$loc]['garrison']['i12'] = 10;
    // }
    //garrison interaction
    // if (strpos($orders[$currLine], 'give garrison'))
    // {
      //figure out what was received
      // debug (2, "give garrison: ".$orders[$currLine]);
      // $garr = preg_match('/give garrison (\S+) (\S+)/', $orders[$currLine], $garrMatch);
      // if ($garr)
      // {
       // have to parse next line to get what was actually given; give 0
       // may have been used, or some variant thereof, so we don't know how
       // much of whatever was given, unless we parse it.
        // $currLine ++;
        // $loc = $olyData["nobles"]["n".$id]["loc"];
       // if it's not a map loc, walk up til we find a map loc
        // while (!is_mapcell($loc))
        // {
          // $loc = $olyData['mapData'][$loc]['parent'];
        // }
        // $item = $garrMatch[1];
        // $garr = preg_match('/Gave (\S+) .+ to Garrison/', $orders[$currLine], $garrMatch);
        // debug (2, "Gave $loc ".$garrMatch[1]." $item");
        // if ($numbers[$garrMatch[1]])
        // {
          // $nItem = $numbers[$garrMatch[1]];
        // } else {
          // $nItem = $garrMatch[1];
        // }
        // if (isset($olyData['mapData'][$loc]['garrison']['i'.$item]))
        // {
          // $olyData['mapData'][$loc]['garrison']['i'.$item] += $nItem;
        // } else {
          // $olyData['mapData'][$loc]['garrison']['i'.$item] = $nItem;
        // }
      // }
    // }
    // if (strpos($orders[$currLine], 'get garrison'))
    // {
      // $garr = preg_match('/get garrison (\S+) (\S+)/', $orders[$currLine], $garrMatch);
      // if ($garr)
      // {
        //have to parse next line to get what was actually taken; get 0
        //may have been used, or some variant thereof, so we don't know how
        //much of whatever was taken, unless we parse it.
        // $currLine ++;
        // $loc = $olyData["nobles"]["n".$id]["loc"];
        //if it's not a map loc, walk up til we find a map loc
        // while (!is_mapcell($loc))
        // {
          // $loc = $olyData['mapData'][$loc]['parent'];
        // }
        // $item = $garrMatch[1];
        // $garr = preg_match('/Took (\S+) .+ from Garrison/', $orders[$currLine], $garrMatch);
        // if ($garr)
        // {
          // debug (2, "Took $loc ".$garrMatch[1]." $item");
          // if ($numbers[$garrMatch[1]])
          // {
            // $nItem = $numbers[$garrMatch[1]];
          // } else {
            // $nItem = $garrMatch[1];
          // }
          // if (isset($olyData['mapData'][$loc]['garrison']['i'.$item]))
          // {
            // $olyData['mapData'][$loc]['garrison']['i'.$item] -= $nItem;
          // } else {
            // $olyData['mapData'][$loc]['garrison']['i'.$item] = -$nItem;
          // }
        // }
      // }
    // }
    // what else?
    $currLine ++;
    debug (3, "line: ".$currLine." ".$orders[$currLine]);
  }
  getToken(1);
}

function handleLocDays($id)
{
  global $myFaction, $turnNum, $olyData, $orders, $currLine, $maxLines, $numbers, $shipKinds, $debug;
  // ok, what can happen?
  debug (3, "handleLocDays : ".$currLine);
  $currLine += 2;
  debug (3, "line: ".$currLine." ".$orders[$currLine]);
  // walk each day
  while (strlen(trim($orders[$currLine])) >= 1 && $currLine <= $maxLines)
  {
    $x = preg_match("/(\d+): (.+)/", $orders[$currLine], $match);
    // match[1] is day num
    // did we see a ship? find an id then type
    if (preg_match("/\[(\S+)\], (\S+)/", $orders[$currLine], $ship))
    {
      debug (3, "ship? ");
      if ($debug >= 3) print_r($ship);
      if ($shipKinds[$ship[2]])
      {
        debug (3, "ship!");
      }
    }
    $currLine ++;
    debug (3, "line: ".$currLine." ".$orders[$currLine]);
  }
  debug (3, "ship done: line: ".$currLine);
  getToken(1);
}

function handleNoble()
{
  global $tok, $myFaction, $olyData, $turnNum;
  // handle noble
  $id = idify($tok);
  // get line, get name (everything up to token)
  $name = getName();
  debug(2, "name: $name id: $id\n");
  setNoble("n".$id, trim($name), $turnNum, $myFaction, "");
  // walk through the days the noble is doing stuff, parse stuff there
  handleNobleDays($id, $name);
  findToken("Capacity:");
  skipLines();
  if ($tok == "Pending")
  {
    findToken("trade");
    skipLines();
  }
}

function handleLocNobles($locId)
{
  global $tok, $olyData, $orders, $currLine, $maxLines, $shipKinds, $turnNum;
  $done = 0;
  while (!$done && $currLine <= $maxLines)
  {
    findPrefix("[");
    debug(4, "found: $tok");
    $x = getEntity();
    $nid = "n".$x["id"];
    $cname = $x["name"];
    if ($x['kind'] == "garrison")
    {
      debug(2, "Setting garrison");
      setGarrison('g'.$x['id'], $turnNum, $locId);
    } else {
      setNoble($nid, $cname, $turnNum, "", $locId);
    }
    $found = skipUntil("[");
    // if the next line is blank we're done
    if ($found)
    {
      $done = 0;
      getToken();
    } else {
      $done = 1;
      skipLines();
    }
  }
}


function handleLocShips($locId)
{
  global $tok, $olyData, $orders, $currLine, $maxLines, $shipKinds, $turnNum;
  $done = 0;
  while (!$done && $currLine <= $maxLines)
  {
    findPrefix("[");
    debug(4, "found: $tok");
    $xx = getEntity();
    $sname = $xx["name"];
    $kind = $xx["kind"];
    if ($shipKinds[$kind])
    {
      // it's a ship
      debug(3, "ship\n");
      $sid = "s".$xx["id"];

      if (!empty($olyData["ships"][$sid]["loc"]))
      {
        unset($olyData["mapData"][$olyData["ships"][$sid]["loc"]]["ships"][$sid]);
      }
      $olyData["ships"][$sid]["seen"] = $turnNum;
      $olyData["ships"][$sid]["loc"] = $locId;
      $olyData["ships"][$sid]["name"] = $sname;
      $olyData["ships"][$sid]["kind"] = idify($kind);
      $olyData["mapData"][$locId]["ships"][$sid] = array();
    } else {
      // noble on a ship
      debug(3, "nob on ship\n");
      $sid = "n".$xx["id"];
      setNoble($sid, nobleName($sname), $turnNum, "", $locId);
    }
    // skip lines until we find a '[' or a blank
    $found = skipUntil('[');
    // if the next line is blank we're done
    if ($found)
    {
      $done = 0;
      getToken();
    } else {
      $done = 1;
      skipLines();
    }
    //$done = !$found && (strlen($orders[$currLine+1]) <= 2);
    //if ($done)
    //{
    //  skipLines();
    //} else {
    //  getToken();
    //}
  }
}
function handleLocRoutes($locId)
{
  global $tok, $olyData, $orders, $currLine, $maxLines, $shipKinds, $turnNum, $myFaction, $cities,
    $mapOffsets;
  $done = 0;
  // typical kinds of route
  // North, to Forest [sc80], 8 days
  // North, to Ocean [bp71], Great Sea, 1 day
  // North, city, to Kinglake [a64], Itona, 1 day
  // To Mountain [bf64], Carisos, 1 day -- from faery
  // and today's fun item:
  //    Underground, to Hades [nz32], Hades, hidden, 1 day
  //    "Notice to mortals, from the Gatekeeper Spirit of Hades: 100 gold/head
  //     is removed from any stack taking this road."
  while (!$done && $currLine <= $maxLines)
  {
    findPrefix("[");
    debug(4, "found: $tok");
    $xx = preg_match('/(\w+),/', $orders[$currLine], $matchDir);
    $yy = preg_match('/(\w+), to (.+) \[(\w+)\],/', $orders[$currLine], $matchLoc);
    $zz = preg_match('/To (.+) \[(\w+)\],/', $orders[$currLine], $matchOdd);
    debug(1, "LocRoutes: $xx $yy $zz");
//    print_r($matchDir);
//    print_r($matchLoc);
//    print_r($matchOdd);
    $impas = strpos($orders[$currLine], "impassable");
    $hid = strpos($orders[$currLine], "hidden");
    $hades = strpos($orders[$currLine], ' Hades ');
    $days = preg_match('/, (\d+) day/', $orders[$currLine], $matchDay);
    if ($impas)
    {
      $matchDay[1] = -1;
    }
    $lid = $matchLoc[3];
    $lname = $matchLoc[2];
    $ldir = strtolower($matchDir[1]);
    // ok, is this an oddball?
    if ($zz)
    {
      // yep, handle it.
      $ldir = strtolower($matchOdd[2]);
      $lname = $matchOdd[1];
      $lid = $matchOdd[2];
    } else {
      if (strtolower($matchLoc[1]) == $ldir)
      {
        // got a dir, not something else
        if (is_mapcell($lid))
        {
          setProvince($lid, strtolower(trim($lname)));
        } else {
          debug(1, "got $ldir for $lname - not map cell");
        }
      } else {
        debug(1, "No dir match -is it city?");
        if ($cities[$matchLoc[1]])
        {
          if (is_array($mapOffsets[$ldir]))
          {
            setCity($matchLoc[3], neighborCell($locId, $ldir), $lname);
          } else {
            debug(1, 'No dir match');
          }
        }
      }
    }
    debug(3, "id: $lid type: $lname dir:$ldir\n");
    $olyData["mapData"][$locId]["route"][$lid]["days"] = $matchDay[1];
    $olyData["mapData"][$locId]["route"][$lid]["dir"] = strtolower($ldir);
    if ($hid)
    {
      $olyData["mapData"][$locId]["route"][$lid]["hidden"][$myFaction] = 1;
    }
    // ok, handle Hades fun here
    if ($hades)
    {
      debug(3, "hades: ".$orders[$currLine+1]);
      if (strpos($orders[$currLine+1], 'Notice to mortals'))
      {
        $currLine += 2;
      }
    }
    $done = (strlen($orders[$currLine+1]) <= 2);
    if (!$done)
    {
      getToken();
    } else {
      skipLines();
    }
  }
}

function handleLocInner($locId)
{
  global $tok, $olyData, $orders, $currLine, $maxLines, $shipKinds, $turnNum, $innerTypes, $myFaction;
  $done = 0;
  while (!$done && $currLine <= $maxLines)
  {
    findPrefix("[");
    $xx = getEntity();
    $inId = $xx["id"];
    $inName = $xx["name"];
    $inType = $xx["kind"];
    // if it's a building, it may have occupants - need to skip them
    // and handle them as nobs
    if (!isset($innerTypes[$inType]))
    {
      // not a building - handle nob
      setNoble("n".$inId, $inName, $turnNum, "", $locId);
    } else {
      $inHidden = strpos($orders[$currLine], "hidden");
      debug(1, "hid: $inHidden");
      $inId = $innerTypes[$inType].$inId;
      $olyData["mapData"][$inId]["name"] = $inName;
      $olyData["mapData"][$locId]["inner"][$inId] = array();
      $olyData["mapData"][$inId]["type"] = $inType;
      $olyData['mapData'][$inId]['parent'] = $locId;
      if ($inHidden)
      {
        $olyData["mapData"][$inId]["hidden"][$myFaction] = 1;
      }
      if ($inType == "city")
      {
        debug(3, "city: ".$locId." ".reverseLetterCell($locId)." ".$inId);
        setCity($inId, $locId);
      }
    }
    // skip lines until we find a '[' or a blank
    $found = skipUntil('[');
    // if the next line is blank we're done
    if ($found)
    {
      $done = 0;
      getToken();
    } else {
      $done = 1;
      skipLines();
    }
    debug(2, "inner: $inName $inId $inType $inHidden\n");
  }
}

function handleMarket($locId)
{
  global $olyData, $tok, $orders, $currLine, $debug, $turnNum;
  findToken("trade");
  // jump to first market line
  $currLine += 2;
  // clear current market data - it's old, and no longer valid
  unset($olyData["mapData"][$locId]["market"]);
  // walk a line at a time, as long as it isn't empty, grab each piece
  // trade    who   price    qty     wt/ea   item
  do
  {
    //$x = preg_match('/(\w+)\s+(\w+)\s+(\d+)\s+(\d+).+\[(\w+)\]/', $orders[$currLine], $match);
    $x = preg_match('/(\w+)\s+(\w+)\s+(\d+)\s+(\d+)\s+(\S+)\s+(.+)\[(\w+)\]/', $orders[$currLine], $match);
    if (strlen($match[1]) == 0) print "Market error: $currLine";
    $olyData["mapData"][$locId]["market"][$match[1]]["m".$match[2]]["i".$match[7]]["price"] = $match[3];
    $olyData["mapData"][$locId]["market"][$match[1]]["m".$match[2]]["i".$match[7]]["qty"] = $match[4];
    $olyData["market"]["i".$match[7]][$locId]["price"] = $match[3];
    $olyData["market"]["i".$match[7]][$locId]["qty"] = $match[4];
    $olyData["market"]["i".$match[7]][$locId]["turn"] = $turnNum;
    $olyData['market']['i'.$match[7]][$locId][$match[1]] = 1;
    $olyData['market']['i'.$match[7]]['name'] = trim($match[6]);
    if ($debug >= 3) print_r($match);
    $currLine++;
  } while (strlen($orders[$currLine]) > 2);
  getToken(1);
}

function handleLocControlled($id)
{
  global $olyData, $currLine, $tok, $orders;
  // seen stored with province, no need to store again
  // store castle id, where castle is, who ruler is (ignore status)
  // ignore token, use regex
  $x = preg_match('/Province controlled by (.+) \[(\S+)\], .+\[(\S+)\]/', $orders[$currLine], $match);
  debug(3, "found: $tok");
  $cid = $match[2];
  $cname = $match[1];
  $cloc = $match[3];
  $currLine++;
  $x = preg_match('/Ruled by .+\[(\S+)\]/', $orders[$currLine], $match);
  $king = $match[1];
  debug(3, "control: a$cid b$cname c$cloc d$king");
  $olyData['mapData'][$id]['ruler']['castle'] = $cid;
  $olyData['mapData'][$id]['ruler']['locid'] = $cloc;
  $olyData['mapData'][$id]['ruler']['id'] = $king;
  $olyData['kingdom']['k'.$cid]['king'] = $king;
  $olyData['kingdom']['k'.$cid]['locs'][$cloc] = array();
}

function handleMap($endLine = 0)
{
  global $theFileName, $orders, $currLine, $tok, $olyData, $maxLines, $turnNum, $cities, $parseLocDays, $terrainTypes;
  // get id of area
  $id = idify($tok);
  // is this a 'day' handle?
  if ($endLine)
  {
    debug(2, "Parsing vision");
    $savedMax = $maxLines;
    $maxLines = $endLine-2;
  }
  // name of area
  $name = getName();
  $terrain = "";
  $done = 0;
  while (!$done)
  {
    $terrain .= getToken();
    $done = strpos($tok, ",");
  }
  $terrain = idify(trim($terrain));
  //$olyData["mapData"][$id]["seen"] = $turnNum;
  // get civ level, if any
  $civ = preg_match('/civ-(\d+)/', $orders[$currLine], $match);
  if ($civ)
  {
    $civ = $match[1];
  } else {
    $civ = 0;
  }
  //$olyData["mapData"][$id]["civ"] = $civ;
  debug(2, "name: $name id: $id terr: $terrain ");
  // is it a city? put it in the list
  if ($cities[$terrain])
  {
    // get the parent location
    findPrefix('[');
    setCity($id, idify($tok), $name);
  } else if ($terrainTypes[$terrain])
  {
    setProvince($id, $terrain, $name, "", $civ, $turnNum);
  } else {
    debug(0, "Unknown terrain: $id $terrain");
  }
  //if (is_mapcell($id))
  //{
    //$olyData["mapData"][$id]["terrain"] = $terrain;
  //}
  if (!$endLine && $parseLocDays)
  {
    handleLocDays($id);
  } elseif ($endLine)
  {
    $currLine ++;
    getToken(1);
  }
  debug(3, "Next!! " . $tok);
  // we really expect the token to be 'Province' or 'Routes' - if it
  // isn't, we have something really bad, so scream about it, assume we're
  // done with the province, and move on.
  if ($tok != "Province" && $tok != "Routes" && $tok != 'Recovery')
  {
    debug(0, $theFileName.": Token error - bailing on province parsing.");
    if ($endLine)
    {
      debug(0, "Even worse, happened in a vision!");
      $maxLines = $savedMax;
    }
    return;
  }
  // is it controlled?
  findFirst(array("Province", "Routes", 'Recovery'));
  if ($tok == 'Recovery')
  {
    // do nothing
    skipLines();
  }
  if ($tok == "Province")
  {
    // yep, controlled
    handleLocControlled($id);
  }
  // get routes
  findToken("Routes");
  handleLocRoutes($id);
  // do we have inner?

  debug(3, "maptokb: $tok ");
  if ($tok == "Cities")
  {
    // nearby cities
    while (strlen($orders[$currLine]) > 2 && $tok != "Skills")
    {
      findPrefix("[");
      debug(4, "found: $tok");
      $cid = idify($tok);
      $cname = getName();
      getToken(); // 'in'
      $cterr = strtolower(getToken());
      $cloc = idify(getToken());
      debug(2, "nearby: $cid $cname $cterr $cloc\n");
      $olyData["mapData"][$cloc]["terrain"] = $cterr;
      $olyData["mapData"][$cloc]["inner"][$cid] = array();
      $olyData["mapData"][$cid]["name"] = $cname;
      $olyData["mapData"][$cid]["type"] = "city";
      $olyData['mapData'][$id]['nearby'][$cid] = 1;
      debug(3, "city: ".$cloc." ".reverseLetterCell($cloc)." ".$cid);
      setCity($cid, $cloc);
      getToken(1);
    }
  }
  debug(3, "maptokc: $tok ");
  if ($tok == "Skills")
  {
    // skills are all jumbled together; look for the Market report
    while ($tok != "Market")
    {
      while ($tok != "Market" && strpos($tok, "[") === false)
      {
        getToken();
      }
      if ($tok != "Market")
      {
        $olyData["mapData"][$id]["skills"]["sk".idify($tok)] = 1;
        getToken();
      }
    }
    //skipLines();
  }
  debug(3, "maptokd: $tok ");
  if ($tok == "Market")
  {
    handleMarket($id);
  }
  debug(3, "maptok-inner: $tok ");
  if ($tok == "Inner")
  {
    // inner
    handleLocInner($id);
  }
  debug(3, "maptoke: $tok ");
  if ($tok == "It")
  {
    debug(3, $orders[$currLine]);
    // rainy?
    if (strpos($orders[$currLine], 'raining'))
    {
      $olyData['mapData'][$id]['rainy'] = 1;
      $currLine ++;
      getToken(1);
    } else {
      $olyData['mapData'][$id]['rainy'] = 0;
    }
    // windy?
    if (strpos($orders[$currLine], 'windy'))
    {
      $olyData["mapData"][$id]["windy"] = 1;
      $currLine ++;
      getToken(1);
    } else {
      $olyData['mapData'][$id]['windy'] = 0;
    }
  } else {
    $olyData["mapData"][$id]["windy"] = 0;
    $olyData['mapData'][$id]['rainy'] = 0;
  }
  debug(3, "maptokf: $tok ");
  if ($tok == "The")
  {
    // foggy?
    $olyData["mapData"][$id]["foggy"] = 1;
    skipLines();
  } else {
    $olyData["mapData"][$id]["foggy"] = 0;
  }
  debug(3, "maptokg: $tok ");
  if ($tok == "Seen")
  {
    // nobles here
    handleLocNobles($id);
  }
  debug(3, "maptokh: $tok ");
  if ($tok == "No")
  {
    // no one can be seen
    $olyData["mapData"][$id]["foggy"] = 1;
    skipLines();
  } else {
    $olyData["mapData"][$id]["foggy"] = 0;
  }
  debug(3, "maptoki: $tok ");
  if ($tok == "Ships")
  {
    handleLocShips($id);
  }
  if ($endLine)
  {
    $maxLines = $savedMax;
  }
}

function resetEnv()
{
  $GLOBALS["orders"] = array();
  $GLOBALS["currLine"] = 0;
  $GLOBALS["maxLines"] = 0;
  $GLOBALS["tok"] = "";
  $GLOBALS["myFaction"] = "";
  $GLOBALS["turnNum"] = 0;
}

function parse($tn, $fac, $data)
{
  global $orders, $currLine, $tok, $maxLines, $myFaction, $turnNum;
  set_time_limit(99);
  debug (3, "<pre>");
  resetEnv();
  $turnNum = $tn;
  $myFaction = $fac;
  $orders = explode("\n", $data);
  $orders[-1] = "x";
  $currLine = -1;
  $maxLines = count($orders);
  debug(3, "max: $maxLines\n");
  $tok = strtok($orders[$currLine], $scanFilter);
  debug(3, "prime: $tok<br />\n");
  findPrefix("[");
  // not starting with turn stuff - pasting in data from report
  // assume map data only, in limited form
  handleMap();
  debug (3, $orders[$currLine]);
  debug (3, "</pre>");
}

/* function parseGarrisonLog()
{
  global $olyData, $turnNum, $orders, $currLine;
  while (strlen($orders[$currLine]) > 2)
  {
    // did we get something?
    // day: id: Received qty [id] from
    $x = preg_match("/\d+: (\S+): Received (\S+) .+ \[(\d+)\] from/", $orders[$currLine], $match);
    if ($x)
    {
      adjustGarrisonInv('g'.$match[1], $match[3], unniceNumber($match[2]));
    } else {
      // was something taken?
      // day: id: name [id] took qty [id]
      $x = preg_match("/\d+: (\S+): .+ \[(\S+)\] took (\S+) .+ \[(\d+)\] from us/", $orders[$currLine], $match);
      if ($x)
      {
        adjustGarrisonInv('g'.$match[1], $match[4], -1*unniceNumber($match[3]));
      } else {
        // did we fail to pay maintenance?
        $x = preg_match("/30: (\S+): Maintenance costs/", $orders[$currLine], $match);
        if ($x)
        {
          debug(1, "Garrison can't pay for people in ".$olyData['garrison']['g'.$match[1]]['loc']);
        }
      }
    }
    $currLine++;
  }
} */

function parseGarrisonLog()
{
  skipLines();
}

function processUnits()
{
}

function parseFile($fn)
{
  global $orders, $currLine, $tok, $maxLines, $myFaction, $turnNum, $theFileName;
  debug (3, "<pre>");
  resetEnv();
  $theFileName = $fn;
  $orders = file($fn);
  $maxLines = count($orders);
  debug(3, "max: $maxLines\n");
  // prime the token pump
  $tok = strtok($orders[$currLine], $scanFilter);
  debug(3, "prime: $tok<br />\n");
  // get turn
  findToken("turn");
  $turnNum = getToken();
  debug(1, "Turn: ".$turnNum."\n");
  // find faction
  findPrefix("[");
  $myFaction = idify($tok);
  debug(1, "Faction: ".$myFaction."\n");
  // skip rankings, find the unit list
  findToken("Provinces");
  findToken('unit');
  processUnits();
  findToken("[".$myFaction."]");
  findToken("qty");
  skipLines();
  // handle nobles
  $notDone = 1;
  debug(1, 'Garrison check: '.$tok);
  if ($tok == 'Garrison')
  {
    // parse the garrison data
    $currLine += 2;
    parseGarrisonLog();
  }
  while ($notDone)
  {
    if ($tok == "Lore" || $tok == "New" || $tok == "Order")
    {
      $notDone = 0;
      $currLine = $maxLines + 1;
    } else {
      findPrefix("[");
      $id = $tok;
      // is it a cell?  If so, move to area processing
      if (!$isMap)
      {
        $isMap = is_mapcell($id);
      }
      if ($isMap)
      {
        handleMap();
      } else {
        handleNoble();
      }
      debug(2, "done?\n");
      $notDone = ($currLine <= $maxLines);
    }
  }
  // loop through, handle nobles, until we find a province
 
  debug (3, $orders[$currLine]);
  debug (3, "</pre>");
  
}

?>

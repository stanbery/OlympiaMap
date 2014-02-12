<?php

$debug = (isset($_GET["debug"]) ? $_GET["debug"] : 0);

  $letters = "abcdfghjkmnpqrstvwxz";
//  $br = "&#13;";
  $br = "<br />";

  $cities = array ("city" => 1, "port" => 1, "portcity" => 1);
  $innerTypes = array ("castle" => "i", "inn" => "i", "temple" => "i", "tower-in-progress" => "i", "tower" => "i", 
    "battlefield" => "", "bog" => "", "cave" => "", "circle" => "", "city" => "", 
    "enchanted" => "", "faery" => "", "graveyard" => "", "island" => "", "lair" => "", 
    "mallorn" => "", "mine" => "i", "oasis" => "", "pasture" => "", "pits" => "", "poppy" => "",
    "port" => "", "rocky" => "", "ring" => "", "ruins" => "", "sacred" => "", "sand" => "", "sewer" => "", 
    "tunnel" => "", "yew" => "",
    );
  $resourceTypes = array(
    'sacred' => 's',
    'circle' => 'c',
    'rocky' => 'h',
    'cave' => 'v',
    'pasture' => 'p',
    'mallorn' => 'm',
    'bog' => 'b',
    'pits' => 'P',
    'yew' => 'Y',
    'graveyard' => 'g',
    'pit' => 's',
    'poppy' => 'O',
    'city' => 'C',
    'port' => 'C',
  );
  $questTypes = array(
    'island' => 'i',
    'cave' => 'c',
    'ruins' => 'r',
    'battlefield' => 'b',
    'graveyard' => 'g',
    'lair' => 'L',
    'enchanted' => 'e',
    'faery' => 'F',
    'pits' => 'P',
    'bog' => 'B',
    'sand' => 's',
    'ring' => 'O',
    'chamber' => 'c',
    'city' => 'C',
    'port' => 'C',
  );
  $innerChars = array (
    'battlefield' => 'B',
    'bog' => 'b',
    'cave' => 'c',
    'circle' => 't',
    'city' => 'C',
    'port' => 'C',
    'enchanted' => 'f',
    'faery' => 'F',
    'graveyard' => 'H',
    'island' => 'i',
    'lair' => 'l',
    'mallorn' => 'M',
    'oasis' => 'o',
    'pasture' => 'p',
    'pits' => 'd',
    'poppy' => 'P',
    'rocky' => 'r',
    'ring' => 'G',
    'ruins' => 'R',
    'sacred' => 's',
    'sand' => 'S',
    'sewer' => 'D',
    'yew' => 'Y',
  );
  $shipKinds = array("galley" => 1, "roundship" => 1, "galley-in-progress" => 1, "roundship-in-progress" => 1);
  $skillNames = array("600" => "Shipcraft", "610" => "Combat", "630" => "Stealth", "650" => "Beastmastery",
    "670" => "Persuasion", "680" => "Construction", "690" => "Alchemy", "700" => "Forestry", "720" => "Mining",
    "730" => "Trade", "750" => "Religion", "800" => "Magic", "820" => "Weather magic", "840" => "Scrying",
    "860" => "Gatecraft", "880" => "Artifact construction", "900" => "Necromancy", "920" => "Advanced sorcery",
    );
  $terrainTypes = array ("unknown" => 1, "mountain" => 1, "desert" => 1, "plain" => 1, "forest" => 1, "swamp" => 1, "ocean" => 1);
  $civLevels = array ("wilderness", "civ-1", "civ-2", "civ-3", "civ-4", "civ-5", "civ-6", "civ-7", "civ-8");
    
  $numbers = array (
  "one" => 1,
  "two" => 2,
  "three" => 3,
  "four" => 4,
  "five" => 5,
  "six" => 6,
  "seven" => 7,
  "eight" => 8,
  "nine" => 9,
  "ten" => 10,
  );

  $mapOffsets = array (
    "north" => array ("x" => 0, "y" => -1),
    "east" => array ("x" => 1, "y" => 0),
    "south" => array ("x" => 0, "y" => 1),
    "west" => array ("x" => -1, "y" => 0),
    );
  
  $itemNames = array(
  1 => "gold",
10 => "peasant",
11 => "worker",
12 => "soldier",
13 => "archer",
14 => "knight",
15 => "elite guard",
16 => "pikeman",
17 => "blessed soldier",
18 => "ghost warrior",
19 => "sailor",
20 => "swordsman",
21 => "crossbowman",
22 => "elite archer",
23 => "angry peasant",
24 => "pirate",
25 => "elf",
26 => "spirit",
31 => "undead",
32 => "savage",
33 => "skeleton",
34 => "barbarian",
51 => "wild horse",
52 => "riding horse",
53 => "warmount",
54 => "winged horse",
55 => "nazgul",
59 => "flotsam",
60 => "battering ram",
61 => "catapult",
62 => "siege tower",
63 => "ratspider venom",
64 => "lana bark",
65 => "avinia leaf",
66 => "spiny root",
67 => "farrenstone",
68 => "yew",
69 => "elfstone",
70 => "mallorn wood",
71 => "pretus bones",
72 => "longbow",
73 => "plate armor",
74 => "longsword",
75 => "pike",
76 => "ox",
77 => "wood",
78 => "stone",
79 => "iron",
80 => "leather",
81 => "ratspider",
82 => "mithril",
83 => "gate crystal",
84 => "blank scroll",
85 => "crossbow",
87 => "fish",
93 => "opium",
94 => "woven basket",
95 => "clay pot",
96 => "tax cookie",
98 => "drum",
99 => "hide",
101 => "mob cookie",
102 => "lead",
261 => "pitch",
271 => "centaur",
272 => "minotaur",
273 => "undead cookie",
274 => "fog cookie",
275 => "wind cookie",
276 => "rain cookie",
277 => "mage menial cookie",
278 => "giant spider",
279 => "rat",
280 => "lion",
281 => "giant bird",
282 => "giant lizard",
283 => "bandit",
284 => "chimera",
285 => "harpie",
286 => "dragon",
287 => "orc",
288 => "gorgon",
289 => "wolf",
290 => "crystal orb",
291 => "cyclops",
292 => "giant",
293 => "faery",
294 => "petty thief cookie",
295 => "hound",
401 => "Imperial Throne",
);

$friendlies = array(
 'ax8' => 1,
 'ca7' => 1,
 'cg5' => 1,
 'gl8' => 1,
 'mh1' => 1,
 'or4' => 1,
 'tm5' => 1,
 'tm7' => 1,
 'vl7' => 1,
 'xj9' => 1,
 );


  // I'll describe $olyData here, so it is captured someplace:
  // note - this is approximate - could be out of date
  // $olyData -- all data
  // + 'mapData' -- all map locations
  //   + [id] -- id of location (includes inner locations
  //     + 'seen' -- last turn location seen
  //     + 'civ' -- civ level of location
  //     + 'terrain' -- type of terrain of location (only for map locs)
  //     + 'type' -- type of inner location (only for inner locs)
  //     + 'route' -- collection of routes from location
  //       + [id] -- id of destination of route
  //         + 'days' -- number of days to dest (-1 means impassable)
  //         + 'dir' -- direction of route (north, out, etc.)
  //     + 'inner' -- any inner locations
  //       + [id] -- id of inner location (no contents in array)
  //     + 'windy' -- 1 if windy
  //     + 'foggy' -- 1 if foggy
  //     + 'nobles' -- collection of nobles in location
  //       + [id] -- id of noble (no contents in array)
  //     + 'name' -- name of location
  //     + 'skills' -- skills taught in location
  //       + [id] -- id of skill (typically has value 1)
  //     + 'ships' -- collection of ships in location
  //       + [id] -- id of ship (no contents in array)
  //     + 'market' -- collection of market items in location
  //       + 'buy' -- an offer to buy product
  //         + [id] -- id of buyer
  //           + [id] -- id of item
  //             + 'price' -- price offered for item
  //             + 'qty' -- max quantity offered to be bought
  //       + 'sell' -- an offer to sell product
  //         + [id] -- id of seller
  //           + [id] -- id of item
  //             + 'price' -- price asked for item
  //             + 'qty' -- quantity for sale
  // + 'nobles' -- all nobles
  //   + [id] -- id of noble
  //     + 'faction' -- faction of noble
  //     + 'name' -- name of noble
  //     + 'seen' -- last turn noble was seen
  //     + 'loc' -- where the noble was last seen
  // + 'market' -- all market data
  //   + [id] -- id of item
  //     + [id] -- id of location
  //       + 'price' -- price asked
  //       + 'qty' -- quantity offered
  //       + 'turn' -- turn last seen
  //       + 'buy' -- want to buy item
  //       + 'sell' -- want to sell item
  // + 'ships' -- all ships
  //   + [id] -- id of ship
  //     + 'seen' -- last turn ship was seen
  //     + 'loc' -- last location seen
  //     + 'name' -- name of ship
  //     + 'kind' -- what kind of ship
  // + 'cities' -- all cities
  //   + [id] -- id of city -- value is location of city

  function debug($lvl, $x)
  {
    global $debug, $lnum, $currLine;
    if ($debug >= $lvl)
    {
      print "line $lnum $currLine: $x<br />";
    }      
  }
  
  function letterCell($n)
  {
    global $letters;
    return $letters[$n / 20].$letters[$n%20];
  }
  
  function reverseLetterCell($s)
  {
    global $letters;
    $a = substr($s,0,1);
    $b = substr($s,1,1);
    $n1 = strpos($letters, $a);
    $n2 = strpos($letters, $b);
    return $n1*20 + $n2;
  }

  function is_mapcell($x)
  {
    // map cell has 4 chars, 2 alpha, 2 numeric
    return preg_match("/[a-f][a-z][0-9][0-9]/", $x);
  }
  
  function mapDistance($startLoc, $endLoc)
  {
    $x1 = reverseLetterCell($startLoc);
    $x2 = reverseLetterCell($endLoc);
    $y1 = substr($startLoc, 2);
    $y2 = substr($endLoc, 2);
    return abs($x1-$x2) + abs($y1-$y2);
  }
  
  function makeLoc($r, $c)
  {
    return sprintf("%s%02d", letterCell($r), $c);
  }
  
  function neighborCell($locId, $dir)
  {
    global $mapOffsets;
    return makeLoc(reverseLetterCell($locId)+$mapOffsets[$dir]['y'],
      substr($locId, 2)+$mapOffsets[$dir]['x']);
  }
  
  function unniceNumber($s)
  {
    global $numbers;
    if ($numbers[$s])
    {
      return $numbers[$s];
    } else {
      return $s;
    }
  }
  
  function addMark($loc, $clear)
  {
    global $olyData;
    if (isset($olyData["mapData"][$loc]["marked"]))
    {
      if ($clear)
      {
        unset($olyData['mapData'][$loc]['marked']);
      } else {
        $olyData["mapData"][$loc]["marked"] += 1;
      }
    } else {
      if (!$clear)
      {
        $olyData["mapData"][$loc]["marked"] = 1;
      }
    }
  }
  
  function setCity($cityId, $parentId, $name = "")
  {
    global $olyData;
    $olyData['cities']['x'.reverseLetterCell($parentId)]['y'.substr($parentId, 2)] = $cityId;
    $olyData["mapData"][$cityId]["type"] = 'city';
    if (strlen($name) > 0)
      $olyData['mapData'][$cityId]['name'] = $name;
    $olyData['mapData'][$parentId]['inner'][$cityId] = array();
    $olyData['mapData'][$parentId]['city'] = $cityId;
    $olyData['mapData'][$cityId]['parent'] = $parentId;
    debug(1, "setCity: $cityId $parentId $name");
  }
  
  function getCity($cityId)
  {
    global $olyData;
    return $olyData['mapData'][$cityId];
  }
  
  function setProvince($provId, $terr="", $name="", $type="", $civ="", $seen="")
  {
    global $olyData;
    if (strlen($terr) > 0)
      $olyData["mapData"][$provId]["terrain"] = $terr;
    if (strlen($name) > 0)
      $olyData["mapData"][$provId]["name"] = $name;
    if (strlen($type) > 0)
      $olyData["mapData"][$provId]["type"] = $type;
    if (strlen($civ) > 0)
      $olyData["mapData"][$provId]["civ"] = $civ;
    if (strlen($seen) > 0)
      $olyData["mapData"][$provId]["seen"] = $seen;
  }
  
  function circle($loc, $radius, $overwrite)
  {
    global $olyData;
    // make a circle around $loc
    if ($radius < 1)
    {
      addMark($loc, $overwrite);
    } else {
      $r = reverseLetterCell($loc);
      $c = substr($loc, 2, 2);
      $jTop = $r;
      $jBottom = $r;
      $jInc = 1;
      for ($i = $c - $radius; $i <= $c + $radius; $i++)
      {
        addMark(makeLoc($jTop, $i), $overwrite);
        // avoid double-marking
        if ($i != ($c - $radius) && $i != ($c + $radius))
        {
          addMark(makeLoc($jBottom, $i), $overwrite);
        }
        if ($i == $c)
          $jInc = -1;
        $jTop += $jInc;
        $jBottom += ($jInc * -1);
      }
    }
  }
  
function objectsIntoArray($arrObjData, $arrSkipIndices = array())
{
    $arrData = array();
   
    // if input is object, convert into array
    if (is_object($arrObjData)) {
        $arrObjData = get_object_vars($arrObjData);
    }
   
    if (is_array($arrObjData)) {
        foreach ($arrObjData as $index => $value) {
            if (is_object($value) || is_array($value)) {
                $value = objectsIntoArray($value, $arrSkipIndices); // recursive call
            }
            if (in_array($index, $arrSkipIndices)) {
                continue;
            }
            $arrData[$index] = $value;
        }
    }
    return $arrData;
}

function loadData($fn)
{
  global $olyData;
  if (!file_exists($fn))
  {
    $olyData = array();
  } else {
    $olyData = objectsIntoArray(simplexml_load_file($fn));  
  }
}

function mergeData($fn)
{
  global $olyData;
  loadData($fn);
  if (file_exists("pre.xml"))
  {
    $pre = objectsIntoArray(simplexml_load_file("pre.xml"));
  } else {
    $pre = array();
  }
  $olyData = array_merge_recursive($pre, $olyData);
}

function writeElements($f, $r)
{
  if (is_array($r))
  {
    foreach ($r as $k => $v)
    {
      if (is_array($v) && !count($v))
      {
        $s = fwrite($f, "<".$k." />\n");
      } else {
        $s = fwrite($f, "<".$k.">");
        writeElements($f, $v);
        $s = fwrite($f, "</".$k.">\n");
      }
    }
  } else {
    $s = fwrite($f, $r);
  }
}

function writeXml($fn)
{
  global $olyData;
  $f = fopen($fn, "w");
  $s = fwrite($f, "<oly>\n");
  // write data
  writeElements($f, $olyData);
  $s = fwrite($f, "</oly>\n");
  fclose($f);
}

function removeNobles()
{
  global $eliminate, $olyData;
  if (file_exists("x.dat"))
  {
    $foo = file("x.dat", FILE_IGNORE_NEW_LINES);
    print "Eliminating: ";
    foreach ($foo as $x)
    {
      print "$x ";
//      $eliminate['n'.$x] = 1;
      $loc = $olyData['nobles']['n'.$x]['loc'];
      unset($olyData['mapData'][$loc]['nobles']['n'.$x]);
      unset($olyData['nobles']['n'.$x]);
    }
//  } else {
//    $eliminate = array();
  }
}
?>

<?php

include_once("oly.php");
include_once("parser.php");

function make_head()
{
  print <<< EOH
<html><head><title>Edit page</title></head><body>
EOH;
}

function make_form()
{
  global $terrainTypes, $civLevels;
  $c = $_REQUEST["locId"];
  $tn = $_REQUEST['turnNum'];
  $civ = $_REQUEST['civLvl'];
  $terr = $_REQUEST['terrain'];
  $group = $_REQUEST['groupNum'];
  print "<form action=\"editloc.php\" method=\"post\">";
  print "Turn Number: <input type=\"text\" name=\"turnNum\" size=\"5\" maxlength=\"5\" value=\"$tn\" /><br />";
  print "Cell ID: <input type=\"text\" name=\"locId\" size=\"5\" maxlength=\"5\" value=\"$c\" /><br />";
  print "Civ Level: <select name=\"civLvl\">";
  foreach ($civLevels as $n => $v)
  {
    print "<option value=\"$n\"";
    if ($n == $civ) print " selected";
    print ">$v</option>";
  }
  print "</select><br />";
  print "Terrain Type: <select name=\"terrain\">";
  foreach ($terrainTypes as $t => $z)
  {
    print "<option value=\"$t\"";
    if ($t == $terr) print " selected";
    print ">$t</option>";
  }
  print "</select><br />";
  print "Group ownership? <input type=\"checkbox\" name=\"isGroup\" value=\"1\" />Yes</br>";
  print "Group: <select name=\"groupNum\">";
  for ($z = 1; $z < 10; $z++)
  {
    print "<option value=\"$z\"";
    if ($z == $group) print " selected";
    print ">Group $z</option>";
  }
  print "</select><br />";
  print "City? <input type=\"checkbox\" name=\"isCity\" value=\"1\" />Yes<br />";
  print "City ID: <input type=\"text\" name=\"cityId\" size=\"5\" maxlength=\"5\" /><br />";
  print "City name: <input type=\"text\" name=\"cityName\" size=\"20\" maxlength=\"60\" /><br />";
  print "<input type=\"submit\" name=\"action\" value=\"Save\" /></form>";
}

function updateMap()
{
  global $olyData;
  $id = $_REQUEST['locId'];
  $olyData["mapData"][$id]['seen'] = $_REQUEST['turnNum'];
  $olyData['mapData'][$id]['civ'] = $_REQUEST['civLvl'];
  $olyData['mapData'][$id]['terrain'] = $_REQUEST['terrain'];
  if ($_REQUEST['isCity'])
  {
    $cid = $_REQUEST['cityId'];
    $olyData["mapData"][$id]['inner'][$cid] = array();
    $olyData['mapData'][$cid]['seen'] = $_REQUEST['turnNum'];
    $olyData['mapData'][$cid]['name'] = $_REQUEST['cityName'];
    $olyData['mapData'][$cid]['type'] = 'city';
  }
  if ($_REQUEST['isGroup'])
  {
    $olyData['mapData'][$id]['group'] = $_REQUEST['groupNum'];
  }
}

function handle_action()
{
  global $debug;
  $debug = 5;
  if ($_REQUEST["action"])
  {
    // save a copy for later use
    //$fn = sprintf("%04d%s-%s.txt", $_REQUEST['turnNum'], $_REQUEST['facId'], date("YmdHis"));
    //$f = fopen($fn, "w");
    //$s = fwrite($f, $_REQUEST['data']);
    //fclose($f);
    loadData("data.xml");
    updateMap();
    writeXml("data.xml");
    print "Done!<br /><a href=\"map.php\">Map</a>";
  } else {
    print "Insert data above.<br />";
  }
}

function make_tail()
{
  print <<< EOT
</body></html>
EOT;
}

make_head();
make_form();
handle_action();
make_tail();


?>
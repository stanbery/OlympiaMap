<?php

include_once('oly.php');

$claims = array(
  3 => array (
    // Other on Ebor
    "ax78" => 83,
    "az77" => 84,
    "ba77" => 84,
    "bb77" => 84,
    "bc77" => 84,
    "bd78" => 84,
    "bf79" => 84,
    "bg80" => 85,
    "bh81" => 85,
    "bj82" => 85,
    ),
  2 => array (
    // IMG
    "bd76" => 77,
    "bf75" => 78,
    "bg71" => 79,
    "bh71" => 80,
    "bj72" => 81,
    "bk74" => 81,
    "bm75" => 79,
    // North Ebor
    "an71" => 81,
    "ap71" => 83,
    "aq71" => 84,
    "ar71" => 85,
    "as71" => 85,
    "at73" => 83,
    "av75" => 81,
    "aw78" => 81,
    ),
  4 => array (
    // Arcana
    "ac40" => 48,
    "ad40" => 48,
    "af40" => 48,
    "ag40" => 48,
    "ah40" => 48,
    "aj40" => 48,
    "ak40" => 48,
    "am40" => 48,
    "an40" => 48,
    "ap40" => 48,
    // Riverwind
    "bf71" => 74,
    "bd70" => 75,
    "bc70" => 76,
    "bb69" => 76,
    "ba68" => 76,
    "az67" => 76,
    "ax67" => 77,
    "aw66" => 77,
    "av66" => 74,
    "at66" => 72,
    "as67" => 70,
    "ar67" => 70,
    "aq68" => 70,
    "ap69" => 70,
    // East Ebor
    "bj86" => 86,
    "bh86" => 93,
    "bg86" => 95,
    "bf85" => 96,
    "bd85" => 96,
    "bc85" => 95,
    "bb85" => 94,
    "ba85" => 90,
    "az85" => 89,
    "ax84" => 87,
    "aw82" => 86,
    "av82" => 86,
    "at84" => 85,
    ),
  );

function markClaims($claims)
{
  global $olyData;
  foreach ($claims as $g => $v)
  {
    print "group $g ";
    foreach ($v as $s => $e)
    {
      $r = substr($s, 0, 2);
      $c = substr($s, 2);
      for ($i = $c; $i <= $e; $i++)
      {
        $x = sprintf("%s%02d", $r, $i);
        print "$x ";
        $olyData['mapData'][$x]['group'] = $g;
      }
    }
  }
}

loadData('data.xml');
markClaims($claims);
writeXML('edata.xml');

?>

<?php

include_once('oly.php');

function findLocations()
{
  global $olyData, $innerTypes, $data;
  foreach ($olyData['mapData'] as $k => $v)
  {
    if (isset($v['type']) && $v['type'] != 'i')
    {
      $data[$v['type']][] = $k;
    }
  }
}

function listLocations()
{
  global $olyData, $innerTypes, $data;
  foreach ($innerTypes as $k => $v)
  {
    print "<h1>$k</h1>\n";
    if (is_array($data[$k]))
    {
      foreach ($data[$k] as $c)
      {
        print $olyData['mapData'][$c]['name'].' ['.$c.'] in ['.$olyData['mapData'][$c]['parent']."]<br />\n";
      }
    } else {
      print "Nothing found<br />\n";
    }
  }
}

mergeData('data.xml');
$data = array();
findLocations();
listLocations();

?>
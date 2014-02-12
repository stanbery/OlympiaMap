<?php

include_once('oly.php');

function findSkills()
{
  global $olyData, $skillList, $cities;
  foreach ($olyData['mapData'] as $k => $v)
  {
    if ($cities[$v['type']])
    {
      if (isset($v['skills']))
      {
        foreach ($v['skills'] as $sk => $x)
        {
          $skillList[$sk][] = $k;
        }
      }
    }
  }
}

function listSkills()
{
  global $olyData, $skillList, $skillNames;
  foreach ($skillList as $sk => $v)
  {
    print "<h1>".$skillNames[substr($sk, 2)]." [".substr($sk, 2)."]</h1>\n";
    foreach ($v as $c)
    {
      print $olyData['mapData'][$c]['name'].' ['.$c.'] in ['.$olyData['mapData'][$c]['parent']."]<br />\n";
    }
  }
}

mergeData('data.xml');
$skillList = array();
findSkills();
listSkills();

?>
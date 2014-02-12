<?php

include_once('oly.php');

$arcana = array (
  // Arcana
  'ac39' => 49,
  'ad37' => 52,
  'af36' => 53,
  'ag35' => 54,
  'ah34' => 55,
  'aj33' => 55,
  'ak33' => 55,
  'am32' => 56,
  'an32' => 56,
  'ap31' => 56,
  'aq31' => 56,
  'ar30' => 56,
  'as30' => 56,
  'at31' => 56,
  'av31' => 55,
  'aw32' => 55,
  'ax34' => 55,
  'az35' => 55,
  'ba47' => 54,
  'bb48' => 52,
  );

function copyIt($x)
{
  global $olyData, $filtered;
  if (isset($olyData['mapData'][$x]))
  {
    $filtered['mapData'][$x] = $olyData['mapData'][$x];
  }
  if (isset($olyData['mapData'][$x]['inner']))
  {
    foreach ($olyData['mapData'][$x]['inner'] as $k => $v)
    {
      copyIt($k);
    }
  }
}

function copyData()
{
  global $arcana;
  foreach ($arcana as $k => $v)
  {
    $s = substr($k, 2);
    $p = substr($k, 0, 2);
    for ($i = $s; $i <= $v; $i++)
      copyIt(sprintf("%s%02d", $p, $i));
  }
}

mergeData("data.xml");
copyData();
$filtered['nobles'] = $olyData['nobles'];
$olyData = $filtered;
//unset ($olyData['nobles']);
unset ($olyData['gates']);
unset ($olyData['ships']);

writeXml("xdata.xml");

?>

<?php

include_once('oly.php');

mergeData("data.xml");

foreach ($olyData['mapData'] as $id => $loc)
{
  if ($cities[$loc['type']])
  {
    if (!$loc['seen'])
    {
      print $loc['name'].' ['.$id.'] in ['.$loc['parent'].'] not seen'.$br;
    }
  }
}
?>
<?php

// generate a report for an area

include_once("oly.php");

 function makeHead()
  {
    print "<html>\n<head>\n<link rel=\"stylesheet\" type=\"text/css\" href=\"map.css\" />";
    print "</head>\n<body>\n";
  }
  
  function makeTail()
  {
    print "</body></html>\n";
  }

  function tableHead()
  {
    print "<table><tr><td>trade</td><td>who</td><td>qty</td><td>price</td></tr>";
  }
  function tableRow($o, $id, $x)
  {
    print "<tr><td>$o</td><td>$id</td><td>".$x['qty']."</td><td>".$x['price']."</td></tr>";
  }
  
  function makeData()
  {
    global $olyData, $itemNames;
    mergeData("data.xml");
    print "<h1>Market Report</h1>";
    // walk buys and sells; figure out who wants what
    foreach ($olyData['market'] as $item => $stuff)
    {
      $i = substr($item, 1);
      print "<h2>".$olyData['market'][$item]['name']." [$i]</h2>";
      // print buy/sell, location, qty, price -- list sell first
      unset ($temp);
      $temp = array();
      foreach ($stuff as $city => $y)
      {
        if ($city != "name")
        {
          $o = ($y['buy'] ? 'buy' : 'sell');
          $temp[$o][$city]['qty'] = $y['qty'];
          $temp[$o][$city]['price'] = $y['price'];
        }
      }
      tableHead();
      if (isset($temp['sell']))
      {
        foreach ($temp['sell'] as $k => $d)
        {
          tableRow('sell', $k, $d);
        }
      }
      if (isset($temp['buy']))
      {
        foreach ($temp['buy'] as $k => $d)
        {
          tableRow('buy', $k, $d);
        }
      }
    }
  }
  
  makeHead();
  makeData();
  makeTail();
  

?>
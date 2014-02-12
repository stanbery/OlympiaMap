<?php

include_once("oly.php");
include_once("parser.php");
//include_once("mdfparse.php");

$olyData = array();

// get all text file reports
foreach (glob("../data/000[6-8]*.txt") as $fn)
{
  debug(1, "file: ".$fn."\n");
  parseFile($fn);
}

// get all city data
//foreach (glob("../data/city*txt") as $fn)
//{
//  debug(1, "file: ".$fn."\n");
//  $data = file_get_contents($fn);
//  parse(0, "or4", $data);
//}

// get all MDF reports
//foreach (glob("../data/0*.mdf") as $fn)
//{
//  debug(1, "file: ".$fn."\n");
//  parseMDF($fn);
//}

writeXml("ndata.xml");

?>

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
  print <<< EOF
<form action="edit.php" method="post">
Turn Number: <input type="text" name="turnNum" size="5" maxlength="5" /><br />
Faction ID: <input type="text" name="facId" size="5" maxlength="5" /><br />
Data:<br />
<textarea name="data" rows="10" cols="70"></textarea><br />
<input type="submit" name="action" value="Add" />
</form>
EOF;
}

function handle_action()
{
  global $debug;
  $debug = 5;
  if ($_REQUEST["action"])
  {
    print "Parsing...<br />";
    // save a copy for later use
    $fn = sprintf("%04d%s-%s.txt", $_REQUEST['turnNum'], $_REQUEST['facId'], date("YmdHis"));
    $f = fopen($fn, "w");
    $s = fwrite($f, $_REQUEST['data']);
    fclose($f);
    loadData("hand.xml");
    parse($_REQUEST['turnNum'], $_REQUEST['facId'], $_REQUEST['data']);
    writeXml("hand.xml");
    print "Done!<br />";
  } else {
    print <<< EOD
Insert Turn Number, Faction ID, and paste in data from your turn report.<br />
<b>DO:</b> Copy multiple sections and paste them all at once; even delete daily events if desired.<br />
<b>DO NOT:</b> Delete Routes, Include Lore or New Factions - just the real data.<br />
Example:<br />
<pre>
Desert [bh43], desert, in Albea, civ-1

30: The fog has cleared.

Routes leaving Desert:
   North, to Ocean [bg43], Great Sea, 2 days
   East, to Forest [bh44], 8 days
   South, to Forest [bj43], 8 days
   West, to Forest [bh42], 8 days
</pre>
EOD;
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
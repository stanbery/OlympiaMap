OlympiaMap
==========

PHP code that generates maps for the Olympia Play By eMail (PBEM) game.
Written by Larry Stanbery.  Set free under the terms of GPL v3 (see
included license).


Introduction
============

This source code was intended to provide a map for handling Olympia PBEM
report data.  Graphical interfaces give ease of use - a picture is worth
a thousand words.  Reports for each turn are placed into a data directory,
they're parsed and rendered into a data file, and the map is generated on
the fly based on the data.  The map is in HTML so it is portable - no need
for specialized tools, just a web browser.

Setup
=====

Data is placed in the 'data' directory.  The file naming convention I use
is 'nnnnxxx.txt', where 'nnnn' is the turn number (with leading zeros), and
'xxx' is the faction id.  The main parsing script does file globbing, and
expects something like this.  The naming convention makes it pretty easy
to handle data by faction and turn.

Make sure the 'web' directory is visible to your web server, and that the
'data' directory can be written by the user running php for the web server.

Parsing Files
=============

I'll put in instructions on how to parse here.  I think the 'parserP.php'
script is used - will verify.  Output is a 'data.xml'.

Seeing the Map
==============

Point your web browser to the server location that is proving the 'web'
directory.  The 'index.html' will open a framed page that shows the map,
a navigation area, and an area for details.

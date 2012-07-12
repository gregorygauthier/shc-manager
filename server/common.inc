<?php
/** Copyright (c) 2012 Gregory Gauthier

Permission is hereby granted, free of charge, to any person obtaining a
copy of this software and associated documentation files (the
"Software"), to deal in the Software without restriction, including
without limitation the rights to use, copy, modify, merge, publish,
distribute, sublicense, and/or sell copies of the Software, and to
permit persons to whom the Software is furnished to do so, subject to
the following conditions:

The above copyright notice and this permission notice shall be included
in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS
OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY
CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT,
TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE
SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
*/
require_once("config.inc");

$text_rows = 3;
$clue_cols = 60;
$response_cols = 40;

function connect_mysql(){
	// Connect to the MySQL database using the current password
	// returns a mysqli instance or exits the script if unsuccessful
	global $mysql_host, $mysql_username, $mysql_password, $mysql_dbname;
	$mysqli = new mysqli($mysql_host, $mysql_username,
	    $mysql_password);

	if($mysqli->errno){
		printf("Unable to connect to the database:<br /> %s",
			$mysqli->errno);
		exit();
	}
	return $mysqli;
}


function copyright(){
	printf("Website design and source code copyright ".
	    "&copy; %d Gregory Gauthier <br />", date("Y"));
	echo "All clues and responses remain the property of their respective".
	    " authors.";
}

function displayError($error)
{
    echo "<p class=\"error\">$error</p>";
}

function navbar()
{
    $places = array();
    $places['Home'] = 'index.php';
    $places['Standings'] = 'standings.php';
    $places['Category list'] = 'category_list.php';
    $places['Write category'] = 'write_category.php';
    $places['Add user'] = 'new_player.php';
    $strings = array();
    foreach($places as $name => $href)
    {
        $strings[] = sprintf('<a href="%s">%s</a>', $href, $name);
    }
    echo implode(" &bull; ", $strings);
}

function disclaimer()
{
    echo 'The Summer Hiatus Challenge is an unofficial fan-run competition.
        Neither Sony Pictures Entertainment nor <i>Jeopardy!</i> have
        endorsed, sponsored, or affiliated with
        this competition or this website.  All trademarks
        are the property of their respective owners and are being used on
        this website for the purposes of identification and not for
        commercial use.';
}

function footer()
{
    echo '<hr />';
    navbar();
    echo '<hr />';
    copyright();
    echo '<br />';
    disclaimer();
}
?>

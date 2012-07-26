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
$pasteable_rows = 15;
$clue_cols = 60;
$response_cols = 40;

define("UNRESTRICTED", 0);
define("RESTRICTED", 1);

function startpage($access){
    global $logged_in_username;
    session_start();
    if(isset($_SESSION['username']))
    {
        $logged_in_username = $_SESSION['username'];
        return true;
    }
    else
    {
        if($access == RESTRICTED)
        {
            header('Location: unauthorized.php');
            exit;
        }
        return false;
    }
}


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
	printf("<p>Website design and source code copyright ".
	    "&copy; %d Gregory Gauthier</p>", date("Y"));
	echo "<p>All clues and responses remain the property of their respective".
	    " authors.</p>";
}

function displayError($error)
{
    echo "<p class=\"error\">$error</p>";
}

function loginbug()
{
    global $logged_in_username;
    if(!isset($logged_in_username))
    {
        echo '<div id="login" class="loginstatus">';
        echo '<p><a href="login.php">Log in.</a></p>';
    }
    else
    {
        echo '<div id="logout" class="loginstatus">';
        printf ('<p>Logged in as <span class="username">%s</span>.</p>',
            $logged_in_username);
        echo '<p><a href="logout.php">Log out.</a></p>';
    }
    echo '</div>';
}

function navbar()
{
    global $logged_in_username;
    $places = array();
    $places['Home'] = 'index.php';
    $places['Standings'] = 'standings.php';
    $places['Calendar'] = 'calendar.php';
    $places['Category list'] = 'category_list.php';
    $places['View/grade responses'] = 'responses.php';
    if(isset($logged_in_username))
    {
        $places['Write category'] = 'write_category.php';
        $places['Add user'] = 'new_player.php';
    }
    echo '<nav><ul class="navbar">';
    foreach($places as $name => $href)
    {
        printf('<li><a href="%s">%s</a></li>', $href, $name);
    };
    echo '</ul></nav>';
}

function disclaimer()
{
    echo '<p>The Summer Hiatus Challenge is an unofficial fan-run competition.
        Neither Sony Pictures Entertainment nor <i>Jeopardy!</i> have
        endorsed, sponsored, or affiliated with
        this competition or this website.  All trademarks
        are the property of their respective owners and are being used on
        this website for the purposes of identification and not for
        commercial use.</p>';
}

function footer()
{
    echo '<footer>';
    loginbug();
    navbar();
    copyright();
    disclaimer();
    echo '</footer>';
}

function auth($username, $password)
{
    global $salt, $mysql_dbname;
    $mysqli = connect_mysql();
    $mysqli->query("USE $mysql_dbname;");
    $query = "SELECT * FROM users WHERE username=? AND hashed_password=?
        LIMIT 1";
    $stmt = $mysqli->prepare($query);
    $hash = hash('sha512', $password.$salt);
    $stmt->bind_param('ss', $username, $hash);
    $stmt->execute();
    $stmt->store_result();
    if($stmt->num_rows == 0)
    {
        $stmt->close();
        $mysqli->close();
        return false;
    }
    else
    {
        $stmt->close();
        $mysqli->close();
        return true;
    }
}


?>

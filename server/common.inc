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
require_once("database.inc");

$text_rows = 3;
$pasteable_rows = 15;
$clue_cols = 60;
$response_cols = 40;

define("UNRESTRICTED", 0);
define("RESTRICTED", 1);
define("ADMIN", 2);

function startpage($access){
    global $logged_in_username;
    session_start();
    if(isset($_SESSION['username']))
    {
        $logged_in_username = $_SESSION['username'];
        if($access == ADMIN and $logged_in_username != 'admin')
        {
            header('Location: unauthorized_admin.php');
            exit;
            return false;
        }
        return true;
    }
    else
    {
        if($access == RESTRICTED or $access == ADMIN)
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
		throw new Exception(
		    sprintf("Unable to connect to the database:<br /> %s",
			$mysqli->errno));
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
    if(!isset($logged_in_username))
    {
        $places['View responses'] = 'responses.php';
    }
    else
    {
        $places['View/input responses'] = 'responses.php';
        $places['Write category'] = 'write_category.php';
        $places['Add player'] = 'new_player.php';
        $places['Ungraded responses'] = 'ungraded.php';
        if($logged_in_username == 'admin')
        {
            $places['Autofill'] = 'autofill.php';
        }
    }
    $places['Help'] = 'help.php';
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
        Neither Sony Pictures Entertainment, CBS Television Distribution
        nor <i>Jeopardy!</i> have
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
    try
    {
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
    catch (Exception $e)
    {
        die("An error occured.  Details of the error: $e");
    }
}

function get_standings($day_id, $round_id)
{
    global $mysql_dbname, $site_url;
    $buffer = array();
    $mysqli = connect_mysql();
    $mysqli->query("USE $mysql_dbname;");

    $overall_query = "SELECT username, teen_eligible, college_eligible,
        atb_eligible, rookie_eligible, overall_score, overall_ungraded,
        toc_berths.abbreviation, toc_berths.description
        FROM overall_scores
        INNER JOIN players ON overall_scores.player_id = players.id
        LEFT JOIN toc_berths ON players.toc=toc_berths.id
        ORDER BY overall_score DESC, username ASC";

    $round_query = "SELECT username, teen_eligible, college_eligible,
        atb_eligible, rookie_eligible, round_score, round_ungraded,
        toc_berths.abbreviation, toc_berths.description
        FROM round_scores
        INNER JOIN players ON round_scores.player_id = players.id
        LEFT JOIN toc_berths ON players.toc=toc_berths.id
        WHERE round_id = ? ORDER BY round_score DESC, username ASC";

    $day_query = "SELECT username, teen_eligible, college_eligible,
        atb_eligible, rookie_eligible, daily_score, daily_ungraded,
        toc_berths.abbreviation, toc_berths.description
        FROM daily_scores
        INNER JOIN players ON daily_scores.player_id = players.id
        LEFT JOIN toc_berths ON players.toc=toc_berths.id
        WHERE day_id = ?
        ORDER BY daily_score DESC, username ASC";

    if(!is_null($day_id) and $day_id > 0)
    {
        $stmt = $mysqli->prepare($day_query);
        $stmt->bind_param('i', $day_id);
    }
    elseif(!is_null($round_id) and $round_id > 0)
    {
        $stmt = $mysqli->prepare($round_query);
        $stmt->bind_param('i', $round_id);
    }
    else // overall standings
    {
        $stmt = $mysqli->prepare($overall_query);
    }
    $stmt->execute();
    $stmt->bind_result($username, $teen_eligible, $college_eligible,
        $atb_eligible, $rookie_eligible, $score, $num_ungraded,
        $toc_berth_abbr, $toc_berth_desc);

    $usernames = array();
    $teen_eligible_stats = array();
    $college_eligible_stats = array();
    $atb_eligible_stats = array();
    $rookie_eligible_stats = array();
    $scores = array();
    $ungradeds = array();
    $toc_berth_abbrs = array();
    $toc_berth_descs = array();

    while($stmt->fetch())
    {
        $usernames[] = $username;
        $teen_eligible_stats[] = $teen_eligible;
        $college_eligible_stats[] = $college_eligible;
        $atb_eligible_stats[] = $atb_eligible;
        $rookie_eligible_stats[] = $rookie_eligible;
        $scores[] = $score;
        $ungradeds[] = $num_ungraded;
        $toc_berth_abbrs[] = $toc_berth_abbr;
        $toc_berth_descs[] = $toc_berth_desc;
    }

    $stmt->close();
    if(!is_null($day_id) and $day_id > 0)
    {
        $query = "SELECT name FROM days WHERE id = ?";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param('i', $day_id);
        $stmt->execute();
        $stmt->bind_result($dayname);
        $stmt->fetch();
        $buffer[] = sprintf ('<h2>Day results for %s</h2>', $dayname);
        $stmt->close();
    }
    elseif(!is_null($round_id) and $round_id > 0)
    {
        $query = "SELECT name FROM rounds WHERE id = ?";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param('i', $round_id);
        $stmt->execute();
        $stmt->bind_result($roundname);
        $stmt->fetch();
        $buffer[] = sprintf ('<h2>Round results for %s</h2>', $roundname);
        $stmt->close();
    }
    else // overall standings
    {
        $buffer[] = '<h2>Overall results</h2>';
    }
    if(!isset($errortext))
    {
        $link = $site_url.'/standings.php';
        if(!is_null($day_id) and $day_id > 0)
        {
            $link .= sprintf('?dayid=%d', $day_id);
        }
        elseif(!is_null($round_id) and $round_id > 0)
        {
            $link .= sprintf('?roundid=%d', $round_id);
        }
        
        $buffer[] = sprintf(<<<HTML
<p>You can bookmark the following link to these particular results:
<a href="%s">%s</a>.</p>
HTML
, $link, $link);
        $buffer[] =<<<HTML
<table>
<tr><th>Rank</th><th>Player</th>
<th>In TOC?</th><th>Teen</th><th>College</th>
<th>ATB</th><th>Rookie</th><th>Score</th><th>Ungraded</th></tr>
HTML;
        $records_printed = 0;
        foreach($usernames as $key => $value)
        {
            $records_printed++;
            if(!isset($lastscore) or $scores[$key] != $lastscore)
            {
                $rank = $records_printed;
                $lastscore = $scores[$key];
            }
            $buffer[] = sprintf('<tr class="%s"><td class="rank">%d</td>'.
                '<td>%s</td><td class="marker">%s</td>'.
                '<td class="marker">%s</td><td class="marker">%s</td>'.
                '<td class="marker">%s</td><td class="marker">%s</td>'.
                '<td class="score">%d</td><td class="%scount">%s</td></tr>',
                $records_printed % 2 ? "odd" : "even", $rank,
                $value, is_null($toc_berth_abbrs[$key]) ? '' :
                sprintf('<span class="tocsymbol" title="%s">%s</span>',
                    $toc_berth_descs[$key], $toc_berth_abbrs[$key]),
                $teen_eligible_stats[$key] ? 'T' : '',
                $college_eligible_stats[$key] ? 'C' : '',
                $atb_eligible_stats[$key] ? 'A' : '',
                $rookie_eligible_stats[$key] ? 'R' : '',
                $scores[$key],
                $ungradeds[$key] ? 'alert ': '',
                $ungradeds[$key] ? $ungradeds[$key] : '');
        }
        $buffer[] = '</table>';
    }
    else
    {
        return sprintf('<p class="error">%s</p>', $errortext);
    }
    return implode($buffer);
}
?>

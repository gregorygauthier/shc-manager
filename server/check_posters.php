#!/usr/bin/php
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

// NOTE: not web-accessible; use Apache .htaccess to restrict access to
// this script

$time = microtime(true);
$report = '';

require_once('common.inc');
require_once('simple_html_dom.php');
startpage(ADMIN);

function report_error($err)
{
    global $admin_address, $report, $time;
    $message = <<<MESSAGE
An error occurred running the check_posters.php script.
The text of the error is as follows:

    %s

%sThe script ran in %0.3f seconds.
SHC Manager
MESSAGE;
    $body = sprintf($message, $err, (isset($report) and $report != '')
    ? sprintf(<<<REPORT
Additionally, the following report was made prior to exiting:

    %s

REPORT
, $report): '', microtime(true) - $time);
    echo "<pre>$body</pre>";
    mail($admin_address, "Error running check_posters.php", $body,
        "From:$admin_address\r\n");
    exit(1);
}

$mysqli = connect_mysql();
if(!$mysqli)
{
    report_error("Could not connect to database.");
}
$mysqli->query("USE $mysql_dbname;");
$query = "SELECT id, thread_url FROM days WHERE
    thread_url IS NOT NULL";
$stmt = $mysqli->prepare($query);
if(!$stmt)
{
    report_error(sprintf(
        "Could not prepare day selection statement (error %d)",
        $mysqli->errno));
}
$stmt->execute();
$stmt->bind_result($id, $url);
$urls = array();
while($stmt->fetch())
{
    $urls[$id] = $url;
}
$stmt->close();
foreach($urls as $id => $url)
{
    if(microtime(true) > $time + 165)
    {
        report_error("Out of time.");
    }
    $query = "SELECT id, sequence FROM categories WHERE day_id=?";
    $stmt = $mysqli->prepare($query);
    if(!$stmt)
    {
        report_error(sprintf(
            "Could not prepare category selection statement (error %d)",
            $mysqli->errno));
    }
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->bind_result($cat_id, $sequence);
    $cat_ids = array();
    while($stmt->fetch())
    {
        $cat_ids[$sequence] = $cat_id;
    }
    $stmt->close();
    if(!array_key_exists(1, $cat_ids) or !array_key_exists(2, $cat_ids))
    {
        $report .= sprintf(<<<REPORT
URL %s was skipped because not enough categories were provided.

REPORT
, $url);
        continue;
    }
    $clue_ids = array();
    
    $query = "SELECT id, point_value FROM clues WHERE category_id=?";
    for($i = 1; $i <= 2; $i++)
    {
        $stmt = $mysqli->prepare($query);
        if(!$stmt)
        {
            report_error(sprintf(
                "Could not prepare category selection statement (error %d)",
                $mysqli->errno));
        }
        $stmt->bind_param('i', $cat_ids[$i]);
        $stmt->execute();
        $stmt->bind_result($clue_id, $point_value);
        $clue_ids[$i] = array();
        while($stmt->fetch())
        {
            $clue_ids[$i][$point_value] = $clue_id;
        }
        $stmt->close();
    }
    $good = true;
    for($i = 1; $i <= 2; $i++)
    {
        for($j = 1; $j <= 5; $j++)
        {
            if(!array_key_exists(($i + 1) * $j, $clue_ids[$i]))
            {
                $good = false;
                break;
            }
        }
        if(!$good)
        {
            break;
        }
    }
    if(!$good)
    {
        $report .= sprintf(<<<REPORT
URL %s was skipped because the %d-point clue in the %s category was not present.

REPORT
, $url, ($i + 1) * $j, $i == 1 ? 'first' : 'second');
        continue;
    }
    
    $start = 0;
    $page = file_get_html($url . "&start=$start");
    if(!$page)
    {
        $report .= sprintf(<<<REPORT
URL %s was skipped because the URL could not be opened.

REPORT
, $url);
        continue;
    }
    $last_page_number = intval($page->find('div.pagination strong', 1)
        ->innertext);
    $highest_start = ($last_page_number - 1) * 20;
    $report .= sprintf(<<<REPORT
URL %s is being processed.

REPORT
, $url);
    while($start <= $highest_start)
    {
        if(microtime(true) > $time + 165)
        {
            report_error("Out of time.");
        }
        $current_post = $start - 1;
        foreach($page->find('div.post') as $element)
        {
            if(microtime(true) > $time + 165)
            {
                report_error("Out of time.");
            }
            $current_post++;
            $postbody = $element->find('div.postbody', 0);
            $authorlinks = $postbody->find('p.author a');
            $author = trim($authorlinks[count($authorlinks) - 1]->innertext);
            if($author == 'DadofTwins')
            {
                $report .= sprintf(<<<STR
Post #%d by %s is fine because it was made by DadofTwins.

STR
    , $current_post, $author);
                continue;
            }
            $query = "SELECT id FROM players WHERE username=?";
            $stmt = $mysqli->prepare($query);
            if(!$stmt)
            {
                report_error(sprintf(
                    "Could not prepare username selection statement (error %d)",
                    $mysqli->errno));
            }
            $stmt->bind_param('s', $author);
            $stmt->execute();
            $stmt->bind_result($player_id);
            if(!$stmt->fetch())
            {
                $report .= sprintf(<<<STR
Check post #%d by %s--%s is not a registered player.

STR
    , $current_post, $author, $author);
                $stmt->close();
                continue;
            }
            $stmt->close();
            
            /*printf('<p style="background-color: #CCCCFF">%s</p>',
                $posttext);*/
            
            $query = sprintf("SELECT COUNT(*) FROM player_responses
                WHERE player_id = ? AND clue_id IN
                ('%d', '%d', '%d', '%d', '%d',
                '%d', '%d', '%d', '%d', '%d')", $clue_ids[1][2],
                $clue_ids[1][4], $clue_ids[1][6], $clue_ids[1][8],
                $clue_ids[1][10], $clue_ids[2][3], $clue_ids[2][6],
                $clue_ids[2][9], $clue_ids[2][12], $clue_ids[2][15]);
            $stmt = $mysqli->prepare($query);
            if(!$stmt)
            {
                report_error(sprintf(
                    "Could not prepare user response ".
                    "selection statement (error %d)",
                    $mysqli->errno));
            }
            $stmt->bind_param('i', $player_id);
            $stmt->execute();
            $stmt->bind_result($count);
            $stmt->fetch();
            $stmt->close();
            if($count > 0)
            {
                $report .= sprintf(<<<STR
Post #%d by %s is fine; %s already has a registered response.

STR
    , $current_post, $author, $author);
            }
            else
            {
                $report .= sprintf(<<<STR
Check post #%d by %s--%s does not have a registered response.

STR
    , $current_post, $author, $author);
            }
        }
        $start += 20;
        $page = file_get_html($url . "&start=$start");
        if(!$page)
        {
            $report .= sprintf(<<<REPORT
URL %s was skipped because the URL could not be opened.

REPORT
, $url);
            break;
        }
    }
    $report .= sprintf(<<<REPORT
URL %s is finished.

REPORT
, $url);
}

$message = <<<MESSAGE
The script has successfully run!

The following report is available:

%s

The script ran in %0.3f seconds.

SHC Manager
MESSAGE;

$body = sprintf($message, $report, microtime(true) - $time);
echo "<pre>$body</pre>";
mail($admin_address, "Report from check_posters.php", $body,
    "From:$admin_address\r\n");
?>

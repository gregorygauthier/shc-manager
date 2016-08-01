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
$hostauthors = array('OntarioQuizzer', 'rjaguar3');

require_once('common.inc');
require_once('simple_html_dom.php');
startpage(ADMIN);

function report_error($err)
{
    global $admin_address, $report, $time;
    $message = <<<MESSAGE
An error occurred running the autofill_cron.php script.
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
    mail($admin_address, "Error running autofill_cron.php", $body,
        "From:$admin_address\r\n");
    exit(1);
}

function has_registered_responses($player_id, $clue_id_array)
{
    /* Returns the number of registered responses for the
    given player to clues in $clue_id_array. */
    $query_template = "SELECT COUNT(*) FROM player_responses
        WHERE player_id = ? AND clue_id IN (%s)";
    $tuple_text = implode(',', $clue_id_array);
    $query = sprintf($query_template, $tuple_text);
    $count = 0;
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
    return $count;
}

$mysqli = connect_mysql();
if(!$mysqli)
{
    report_error("Could not connect to database.");
}
$mysqli->query("USE $mysql_dbname;");
$query = "SELECT id, thread_url, highest_parsed_post FROM days WHERE
    thread_url IS NOT NULL";
$stmt = $mysqli->prepare($query);
if(!$stmt)
{
    report_error(sprintf(
        "Could not prepare day selection statement (error %d)",
        $mysqli->errno));
}
$stmt->execute();
$stmt->bind_result($id, $url, $highest);
$urls = array();
$highests = array();
while($stmt->fetch())
{
    $urls[$id] = $url;
    $highests[$id] = $highest;
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
    
    $query = "SELECT id, point_value FROM clues WHERE category_id=? ORDER BY
        id ASC LIMIT 5";
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
    
    $highest = (is_null($highests[$id]) ? -1 : $highests[$id]);
    $start = floor(($highest) / 20) * 20;
    if($start < 0)
    {
        $start = 0;
    }
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
            if($current_post <= $highest)
            {
                continue;
            }
            $postbody = $element->find('div.postbody', 0);
            $authorlinks = $postbody->find('p.author a');
            $author = trim($authorlinks[count($authorlinks) - 1]->innertext);
            if(in_array($author, $hostauthors))
            {
                $report .= sprintf(<<<STR
Post #%d by %s was skipped because it was made by a host.

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
Post #%d by %s was skipped because %s is not a registered player.

STR
    , $current_post, $author, $author);
                $stmt->close();
                continue;
            }
            $stmt->close();
            
            $posttext = $postbody->find('div.content', 0)->innertext;
            /*printf('<p style="background-color: #CCCCFF">%s</p>',
                $posttext);*/
            if(!preg_match("%(?:^|[^0-9])2[-.:) ](.*?)<br ?/?>.*?".
                "4[-.:) ](.*?)<br ?/?>.*?".
                "6[-.:) ](.*?)<br ?/?>.*?".
                "8[-.:) ](.*?)<br ?/?>.*?".
                "10[-.:) ](.*?)<br ?/?>.*?".
                "3[-.:) ](.*?)<br ?/?>.*?".
                "6[-.:) ](.*?)<br ?/?>.*?".
                "9[-.:) ](.*?)<br ?/?>.*?".
                "12[-.:) ](.*?)<br ?/?>.*?".
                "15[-.:) ](.*?)(<br ?/?>|$)%i", $posttext, $matches))
            {
                $report .= sprintf(<<<STR
Post #%d by %s was skipped because it did not match the regex.

STR
    , $current_post, $author);
                continue;
            }
            
            
            $clue_array = array($clue_ids[1][2], $clue_ids[1][4],
                $clue_ids[1][6], $clue_ids[1][8],
                $clue_ids[1][10], $clue_ids[2][3],
                $clue_ids[2][6], $clue_ids[2][9],
                $clue_ids[2][12], $clue_ids[2][15]);
            if(has_registered_responses($player_id, $clue_array))
            {
                $report .= sprintf(<<<STR
Post #%d by %s was skipped because %s already has a registered response.

STR
    , $current_post, $author, $author);
                continue;
            }
            $resps = array();
            foreach($matches as $number => $text)
            {
                if($number >= 1 and $number <= 10)
                {
                    $tmp = $text;
                    $tmp = html_entity_decode($tmp, ENT_QUOTES);
                    // Bold indicates a good part of an answer
                    if(1 == preg_match_all('/<b>(.*?)<\/b>/', $tmp, $matches2))
                    {
                        $tmp = $matches2[1][0];
                    }
                    $tmp = strip_tags($tmp);
                    $tmp = clean_response($tmp, true);
                    $resps[$number] = $tmp;
                    if($tmp == '')
                    {
                        $report .= sprintf(<<<REPORT
* Post #%d, response #%d (%s) treated as CLAM.

REPORT
, $current_post, $number, $text);
                    }
                    else
                    {
                        $report .= sprintf(<<<REPORT
* Post #%d, response #%d (%s) was treated as %s.

REPORT
, $current_post, $number, $text, $tmp);
                    }
                }
            }
            $query = "INSERT INTO player_responses (player_id, clue_id,
                response_text) VALUES (?, ?, ?), (?, ?, ?), (?, ?, ?),
                (?, ?, ?), (?, ?, ?), (?, ?, ?),
                (?, ?, ?), (?, ?, ?), (?, ?, ?),
                (?, ?, ?)";
            $stmt = $mysqli->prepare($query);
            if(!$stmt)
            {
                report_error(sprintf(
                    "Could not prepare insertion statement (error %d)",
                    $mysqli->errno));
            }
            $stmt->bind_param('iisiisiisiisiisiisiisiisiisiis',
                $player_id, $clue_ids[1][2], $resps[1],
                $player_id, $clue_ids[1][4], $resps[2],
                $player_id, $clue_ids[1][6], $resps[3],
                $player_id, $clue_ids[1][8], $resps[4],
                $player_id, $clue_ids[1][10], $resps[5],
                $player_id, $clue_ids[2][3], $resps[6],
                $player_id, $clue_ids[2][6], $resps[7],
                $player_id, $clue_ids[2][9], $resps[8],
                $player_id, $clue_ids[2][12], $resps[9],
                $player_id, $clue_ids[2][15], $resps[10]);
            $stmt->execute();
            if($stmt->errno)
            {
                $report .= sprintf(<<<STR
Post #%d by %s was skipped because an insertion error happened (number %d).
The error message was: %s

STR
    , $current_post, $author, $stmt->errno, $stmt->error);
            }
            elseif($stmt->affected_rows < 10)
            {
                $report .= sprintf(<<<STR
Post #%d by %s was partially processed (%d responses inserted).

STR
    , $current_post, $author, $stmt->affected_rows);
            }
            else
            {
                $report .= sprintf(<<<STR
Post #%d by %s was successfully processed.

STR
    , $current_post, $author);
            }
            $stmt->close();
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
    $query = "UPDATE days SET highest_parsed_post=? WHERE id=?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param('ii', $current_post, $id);
    $stmt->execute();
    if($stmt->errno)
    {
        $report .= sprintf(<<<REPORT
Error insert highest parsed post into database (error %d)

REPORT
, $stmt->errno);
    }
    $stmt->close();
    $report .= sprintf(<<<REPORT
URL %s is finished; highest post parsed is %d.

REPORT
, $url, $current_post);
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
mail($admin_address, "Report from autofill_cron.php", $body,
    "From:$admin_address\r\n");
?>

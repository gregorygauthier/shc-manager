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

/* Usage: id is a required get parameter containing the clue ID
whose responses are to be edited */

require_once('common.inc');

if(!isset($_GET['id']))
{
    $errortext="No category id was specified.";
}
else
{
    do
    {
        $id = $_GET['id'];
        $mysqli = connect_mysql();
        $mysqli->query("USE $mysql_dbname;");
        $query = "SELECT clue_text, name, explanatory_text
            FROM clues LEFT JOIN categories ON clues.category_id=categories.id
            WHERE clues.id=?";
        $stmt = $mysqli->prepare($query);
        if(!$stmt)
        {
            $errortext="Could not create a statement to query the".
            " categories table.";
            break;
        }
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->bind_result($clue, $catname, $expl);
        if(!$stmt->fetch())
        {
            $errortext="Invalid clue id specified.";
        }
        $stmt->close();
    } while (false);
}

// Grab all responses that either
// 1. exist in the responses table or
// 2. exist in player_responses and do not match any responses
if(!isset($errortext))
{
    $query = "SELECT id, response_text, correct FROM responses WHERE clue_id=?
        UNION SELECT 0, REPLACE(pr.response_text, '.', '\\\\.'), NULL FROM 
        (SELECT * FROM player_responses WHERE clue_id=?) AS pr
        LEFT JOIN responses AS r ON (pr.clue_id = r.clue_id AND
        pr.response_text REGEXP r.response_text)
        WHERE r.response_text IS NULL";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("ii", $id, $id);
    $stmt->execute();
    $stmt->bind_result($resp_id, $resp_text, $correct);
    $resp_texts = array();
    $corrects = array();
    $ungraded_resp_texts = array();
    while($stmt->fetch())
    {
        if($resp_id > 0)
        {
            $resp_texts[$resp_id] = $resp_text;
            $corrects[$resp_id] = $correct;
        }
        else
        {
            $ungraded_resp_texts[] = $resp_text;
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" type="text/css" href="theme.css" />
<link rel="icon" type="image/png" href="shcicon.png" />
<title>Editing responses...</title>
</head>
<body>
<?php
if(isset($errortext))
{
    displayError($errortext);
}
else
{
    printf ('<h1>Editing responses for clue %d</h1>', $id);
    echo '<table>';
    printf ('<tr><th>Category</th><td>%s</td></tr>', $catname);
    printf ('<tr><th>Explanatory text</th><td>%s</td></tr>',
        $expl == '' ? '<i>none</i>' : $expl);
    printf ('<tr><th>Clue text</th><td>%s</td></tr>',
        $clue);
    echo '</table>';
    echo '<form action="update_responses.php" method="post">';
    printf('<input type="hidden" name="id" value="%d" />', $id);
    echo '<table>';
    echo '<tr><th>Response</th><th>Grading</th></tr>';
    foreach($resp_texts as $resp_id => $resp_text)
    {
        echo '<tr>';
        printf('<td><input type="text" maxlength="65535" '.
            'name="responsetext%d" value="%s" /></td>', $resp_id, $resp_text);
        echo '<td>';
        printf('<input type="radio" name="responsegrade%d" value="correct"'.
            ' %s/>Correct', $resp_id, $corrects[$resp_id] == 1 ?
            'checked="checked"' : '');
        printf('<input type="radio" name="responsegrade%d" '.
            'value="incorrect" %s/>Incorrect',
            $resp_id, $corrects[$resp_id] == 0 ?
            'checked="checked"' : '');
        printf('<input type="radio" name="responsegrade%d" '.
            'value="delete"/>Delete this pattern',
            $resp_id);
        echo '</td>';
        echo '</tr>';
    }
    foreach($ungraded_resp_texts as $number => $resp_text)
    {
        echo '<tr>';
        printf('<td><input type="text" maxlength="65535" '.
            'name="newresponsetext%d" value="%s" /></td>', $number,
            $resp_text);
        echo '<td>';
        printf('<input type="radio" name="newresponsegrade%d" '.
            'value="correct"/>Correct', $number);
        printf('<input type="radio" name="newresponsegrade%d" '.
            'value="incorrect"/>Incorrect', $number);
        echo '</td>';
        echo '</tr>';
    }
    echo '</table>';
    echo '<button type="submit">Submit changes</button>';
    echo '</form>';
}
?>
<?php
footer();
?>
</body>
</html>

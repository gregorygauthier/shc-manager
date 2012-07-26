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

/* USAGE OF THIS PAGE: id is a required parameter that represents the
id of a clue to be edited. */

require_once('common.inc');
startpage(RESTRICTED);

if(isset($_GET['id']))
{
    $id = $_GET['id'];
    /* Check to see if ID matches that of a valid clue */
    $mysqli = connect_mysql();
    $mysqli->query("USE $mysql_dbname;");
    $query = "SELECT COUNT(*) FROM clues WHERE id=?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    if($count == 0)
    {
        $errortext = "Invalid clue id specified.";
    }
    $stmt->close();
}
else
{
    $errortext = "No clue id was specified.";
}
?>
<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" type="text/css" href="theme.css" />
<link rel="icon" type="image/png" href="shcicon.png" />
<title>
<?php
if(isset($errortext))
{
    echo "Error editing clue";
}
else
{
    echo "Editing clue $id";
}
?>
</title>
</head>
<body>
<?php
if(!isset($errortext))
{
    /* Get relevant information about the clue from database */
    $query = "SELECT clue_text, name, explanatory_text, point_value,
        wrong_point_value
        FROM clues LEFT JOIN categories ON clues.category_id=categories.id
        WHERE clues.id=?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->bind_result($clue_text, $category_name, $explanatory, $point_value,
        $wrong_point_value);
    $stmt->fetch();
    $stmt->close();
    $query = "SELECT response_text, correct FROM responses
        WHERE clue_id=?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->bind_result($response, $correct);
    $responses = array();
    $responses_correct = array();
    while($stmt->fetch())
    {
        $responses[] = $response;
        $responses_correct[] = $correct;
    }
    $stmt->close();
    echo "<h1>Editing clue $id</h1>";
    printf( '<form action="submit_edited_clue.php?id=%d" method="post">',
        $id);
    echo '<table><tr><th>Category</th>';
    echo "<td>$category_name</td></tr>";
    echo '<tr><th>Explanatory text</th>';
    printf('<td>%s</td></tr>', $explanatory == '' ?
        '<em>(none)</em>' : $explanatory);
    echo '<tr><th>Clue ID</th>';
    printf ('<td><input type="text" size="9" name="id" value="%d"'.
        ' readonly="true" /></td></tr>', $id);
    echo '<tr><th>Clue text</th>';
    printf('<td><textarea class="fullwidthinput"'.
        'rows="%d" cols="%d" name="clue">%s</textarea></td></tr>',
        $text_rows, $clue_cols, $clue_text);
    printf('<tr><th>Responses (<a href="edit_responses.php?id=%d">edit</a>)'.
        '</th><td>', $id);
    foreach($responses as $idx => $response)
    {
        $correct = $responses_correct[$idx];
        printf('<p class="%s">%s</p>', $correct ? 'responsecorrect' :
            'responseincorrect', htmlspecialchars($response));
    }
    echo '</tr><tr><th>Correct point value</th>';
    printf ('<td><input type="text" size="6" name="pointvalue" value="%d" />'.
        '</td></tr>',
        $point_value);
    echo '<tr><th>Incorrect point value</th>';
    printf ('<td><input type="text" size="6" name="wrongpointvalue"'.
        ' value="%d" /></td></tr>',
        $wrong_point_value);
    echo '</table>';
    echo '<button type="submit">Submit</button>';
}
else
{
    displayError($errortext);
}
footer();
?>
</body>
</html>

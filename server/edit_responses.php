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
startpage(RESTRICTED);


try
{
    if(!isset($_GET['id']))
    {
        throw new Exception("No clue id was specified");
    }
    $clue_id = $_GET['id'];
    $clue = Database::get_clue_by_id($clue_id);
    $responses = Database::get_responses_for_clue($clue_id, true);
    $title = "Editing responses";
}
catch(Exception $e)
{
    $title = "Error occured generating responses";
    $errortext=sprintf("An error occured while generating the responses: %s",
        $e->getMessage());
}
/*CHANGE EVERYTHING BELOW THIS! */
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/> 
<link rel="stylesheet" type="text/css" href="theme.css" />
<link rel="icon" type="image/png" href="shcicon.png" />
<title><?php echo $title;?></title>
<script>
function add_blanks()
{
    var x = document.getElementById('gradetable');
    var rows = x.getElementsByTagName('tr');
    var last_row = rows[rows.length - 1];
    var last_id = last_row.id;
    var id = 0;
    var result = /^addedresponse(\d+)$/i.exec(last_id);
    if(result)
    {
        id = parseInt(result[1]) + 1;
    }
    else
    {
        id = 1;
    }
    var new_row = document.createElement('tr');
    new_row.innerHTML = 
        '<td><input type="text" maxlength="65535" name="addedresponsetext' +
        id + '"' + ' value="" /' + '></td>' +
        '<input type="radio" name="addedresponsegrade' + id +
        '" value="correct"/' + '>Correct' +
        '<input type="radio" name="addedresponsegrade' + id +
        '" value="incorrect"/' + '>Incorrect' +
        '<input type="radio" name="addedresponsegrade' + id +
        '" value="ignore"' + 'checked="checked" /' + '>Do not add</' + 'td>';
    new_row.id = "addedresponse" + id;
    x.appendChild(new_row);
};
</script>
</head>
<body>
<?php
if(isset($errortext))
{
    displayError($errortext);
}
else
{
    printf ('<h1>Editing responses for clue %d</h1>', $clue->id);
    echo '<table>';
    printf ('<tr><th>Category</th><td>%s</td></tr>', $clue->category->name);
    $expl = $clue->category->explanatory_text;
    printf ('<tr><th>Explanatory text</th><td>%s</td></tr>',
        $expl == '' ? '<i>none</i>' : $expl);
    printf ('<tr><th>Clue text</th><td>%s</td></tr>',
        $clue->clue_text);
    echo '</table>';
    echo '<form action="update_responses.php" method="post">';
    printf('<input type="hidden" name="id" value="%d" />', $clue->id);
    echo '<table id="gradetable">';
    echo '<tr><th>Response</th><th>Grading</th></tr>';
    $ungraded_index = 0;
    foreach($responses as $response)
    {
        if($response->id)
        {
            printf('<tr id="response%d">', $response->id);
            printf('<td><input type="text" maxlength="65535" '.
                'name="responsetext%d" value="%s" /></td>',
                $response->id, $response->response_text);
            echo '<td>';
            printf('<input type="radio" name="responsegrade%d" value="correct"'.
                ' %s/>Correct', $response->id, $response->correct == 1 ?
                'checked="checked"' : '');
            printf('<input type="radio" name="responsegrade%d" '.
                'value="incorrect" %s/>Incorrect',
                $response->id, $response->correct == 0 ?
                'checked="checked"' : '');
            printf('<input type="radio" name="responsegrade%d" '.
                'value="delete"/>Delete this pattern',
                $response->id);
            echo '</td>';
            echo '</tr>';
        }
        else
        {
            $ungraded_index++;
            printf('<tr id="newresponse%d">', $ungraded_index);
            printf('<td><input type="text" maxlength="65535" '.
                'name="newresponsetext%d" value="%s" /></td>', $ungraded_index,
                isset($response->text)? $response->text : '');
            echo '<td>';
            printf('<input type="radio" name="newresponsegrade%d" '.
                'value="correct"/>Correct', $ungraded_index);
            printf('<input type="radio" name="newresponsegrade%d" '.
                'value="incorrect"/>Incorrect', $ungraded_index);
            printf('<input type="radio" name="newresponsegrade%d" '.
                'value="ignore" checked="checked" />Do not add',
                $ungraded_index);
            echo '</td>';
            echo '</tr>';
        }
    }
    echo '</table>';
    echo '<button type="button" onclick="add_blanks()">';
    echo 'Add more response blanks</button>';
    echo '<button type="submit">Submit changes</button>';
    echo '</form>';
}
?>
<?php
footer();
?>
</body>
</html>

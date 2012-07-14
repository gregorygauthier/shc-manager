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

/* USAGE OF THIS PAGE: This page takes
post data from edit_clue.php. */

require_once('common.inc');

if(!isset($_POST['id']))
{
    $errortext = "No clue id specified.";
}
else
{
    $id = $_POST['id'];
    $mysqli = connect_mysql();
    $mysqli->query("USE $mysql_dbname;");
    $update_query = "UPDATE responses SET response_text=?, correct=?
        WHERE id=?";
    $delete_query = "DELETE FROM responses WHERE id=?";
    $insert_query = "INSERT INTO responses (clue_id, response_text, correct)
        VALUES (?, ?, ?)";
    foreach($_POST as $key => $value)
    {
        if(preg_match('/^responsetext([0-9]+)$/', $key, $matches))
        {
            // update or delete an existing response
            $resp_id = $matches[1];
            // grab the corresponding grade
            $grade = $_POST["responsegrade$resp_id"];
            if($grade == 'correct' or $grade == 'incorrect')
            {
                $correct = ($grade == 'correct');
                $stmt = $mysqli->prepare($update_query);
                $stmt->bind_param('sii', $value, $correct, $resp_id);
                $stmt->execute();
                $stmt->close();
            }
            elseif($grade == 'delete')
            {
                $stmt = $mysqli->prepare($delete_query);
                $stmt->bind_param('i', $resp_id);
                $stmt->execute();
                $stmt->close();
            }
        }
        elseif(preg_match('/^newresponsetext([0-9]+)$/', $key, $matches))
        {
            $number = $matches[1];
            if(!isset($_POST["newresponsegrade$number"]))
                continue;
            $grade = $_POST["newresponsegrade$number"];
            if($grade == 'correct' or $grade == 'incorrect')
            {
                $correct = ($grade == 'correct');
                $stmt = $mysqli->prepare($insert_query);
                $stmt->bind_param('isi', $id, $value, $correct);
                $stmt->execute();
                $stmt->close();
            }
        }
    }
    $mysqli->close();
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
    echo "Error adding responses";
}
else
{
    echo "Successfully added responses";
}
?>
</title>
</head>
<body>
<?php
if(isset($errortext))
{
    printf('<p class="error">%s</p>', $errortext);
}
else
{
    echo '<p>Responses successfully updated!';
}
footer();
?>
</body>
</html>

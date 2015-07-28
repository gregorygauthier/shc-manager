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
startpage(ADMIN);

try
{
    if(!isset($_POST['id']))
    {
        throw new Exception("No clue id specified.");
    }
    $clue_id = $_POST['id'];
    foreach($_POST as $key => $value)
    {
        if(preg_match('/^responsetext([0-9]+)$/', $key, $matches))
        {
            /* Update or delete an existing response */
            $resp_id = $matches[1];
            $grade = $_POST["responsegrade{$resp_id}"];
            if($grade == 'correct' or $grade == 'incorrect')
            {
                $correct = ($grade == 'correct');
                Database::update_response($resp_id, $value, $correct);
            }
            elseif($grade == 'delete')
            {
                Database::delete_response($resp_id);
            }
        }
        elseif(preg_match('/^(new|added)responsetext([0-9]+)$/', $key, $matches))
        {
            $new_or_added = $matches[1];
            $number = $matches[2];
            if(!isset($_POST["{$new_or_added}responsegrade{$number}"]))
                continue;
            $grade = $_POST["{$new_or_added}responsegrade{$number}"];
            if($grade == 'correct' or $grade == 'incorrect')
            {
                $correct = ($grade == 'correct');
                Database::add_response($clue_id, $value, $correct);
            }
        }
    }
    $title = "Successfully edited responses";
    $message = "<p>Successfully edited responses!</p>";
}
catch (Exception $e)
{
    $title = "Error editing responses";
    $message = sprintf("<p class=\"error\">Error editing responses: %s</p>",
        $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/> 
<link rel="stylesheet" type="text/css" href="theme.css" />
<link rel="icon" type="image/png" href="shcicon.png" />
<title><?php echo $title;?></title>
</head>
<body>
<?php
echo $message;
footer();
?>
</body>
</html>

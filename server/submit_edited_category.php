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

require_once('common.inc');
startpage(RESTRICTED);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/> 
<link rel="stylesheet" type="text/css" href="theme.css" />
<link rel="icon" type="image/png" href="shcicon.png" />
<title>Submitting modified category</title>
</head>
<body>
<?php

$mysqli = connect_mysql();

$query = "USE $mysql_dbname;";

$mysqli->query($query);

$query = "UPDATE categories SET name=?, explanatory_text=?, day_id=?,
    sequence=? WHERE id=?";

$stmt = $mysqli->prepare($query);

if(!isset($_POST['dayid']) or is_null($_POST['dayid']) or $_POST['dayid'] == 0)
{
    $day_id = null;
}
else
{
    $day_id = $_POST['dayid'];
}

do
{
    $subquery = "SELECT sequence FROM categories WHERE id=? AND day_id=?";
    $substmt = $mysqli->prepare($subquery);
    $substmt->bind_param('ii', $_POST['id'], $day_id);
    $substmt->execute();
    $substmt->bind_result($sequence);
    if($substmt->fetch())
    {
        $substmt->close();
        break;
    }
    $substmt->close();
    $subquery = "SELECT 1 + IF(MAX(sequence) IS NULL, 0, MAX(sequence))
            FROM categories WHERE day_id = ?";
    $substmt = $mysqli->prepare($subquery);
    $substmt->bind_param('i', $day_id);
    $substmt->execute();
    $substmt->bind_result($sequence);
    $substmt->fetch();
    $substmt->close();
} while(false);

$stmt->bind_param("ssiii", $_POST['catname'], $_POST['explanatory'],
    $day_id, $sequence, $_POST['id']);

$stmt->execute();

if($stmt->affected_rows)
{
    echo "<p>Category updated successfully!</p>";
}
else
{
    if($stmt->errno)
    {
        displayError(sprintf("Error executing statement (error number %d).",
            $stmt->errno));
    }
    else
    {
        displayError("The category has not changed.");
    }
}

$stmt->close();

$mysqli->close();

footer();
?>
</body>
</html>

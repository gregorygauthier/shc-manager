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

// Check parameters to see if they are set, and set variables accordingly
if(!isset($_POST['newdayround']))
{
    $errortext = "No round specified.";
}
else
{
    $round_id = $_POST['newdayround'];
    if(!isset($_POST['newdayname']))
    {
        $name = '';
    }
    else
    {
        $name = $_POST['newdayname'];
    }
    if(!isset($_POST['newdaydate']) || is_null($_POST['newdaydate']))
    {
        $date = null;
    }
    else
    {
        $date = $_POST['newdaydate'];
    }
}
if(!isset($errortext))
{
    do
    {
        $mysqli = connect_mysql();
        if(!$mysqli)
        {
            $errortext = "Could not connect to database.";
            break;
        }
        $mysqli->query("USE $mysql_dbname;");
        $query = "INSERT INTO days (name, round_id, play_date, sequence)
            VALUES (?, ?, ?, ?)";
        $stmt = $mysqli->prepare($query);
        if(!$stmt)
        {
            $errortext = "Could not prepare statement.";
            $mysqli->close();
            break;
        }
        $stmt->bind_param('sisi', $name, $round_id, $date, $seq);
        $subquery = "SELECT IF(MAX(sequence) IS NULL, 0, MAX(sequence)) FROM
            days WHERE round_id = ?";
        $substmt = $mysqli->prepare($subquery);
        if(!$substmt)
        {
            $errortext = "Could not prepare substatement.";
            $stmt->close();
            $mysqli->close();
            break;
        }
        $substmt->bind_param('i', $round_id);
        $substmt->execute();
        $substmt->bind_result($seq);
        $substmt->fetch();
        $substmt->close();
        $seq++;
        
        $stmt->execute();
        if(!$stmt->affected_rows)
        {
            $errortext = "Could not insert new day into database.";
        }
        $stmt->close();
        $mysqli->close();
    } while (false);
}
?>
<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" type="text/css" href="theme.css" />
<link rel="icon" type="image/png" href="shcicon.png" />
<title><?php
if(isset($errortext))
{
    echo 'Error adding day';
}
else
{
    echo 'Day successfully added';
}
?>
</title>
</head>
</head>
<body>
<?php
if(isset($errortext))
{
    displayError($errortext);
}
else
{
    echo '<p>Day successfully added!</p>';
}
footer();
?>
</body>
</html>

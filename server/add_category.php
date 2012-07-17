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
?>
<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" type="text/css" href="theme.css" />
<link rel="icon" type="image/png" href="shcicon.png" />
<title>Adding category to database...</title>
</head>
<body>
<?php
$mysqli = connect_mysql();

$query = "USE $mysql_dbname;";

$mysqli->query($query);


/* Add the category to the categories table */
$query = "INSERT INTO categories (name, explanatory_text, sequence, day_id)
    VALUES (?, ?, ?, ?)";

$stmt = $mysqli->prepare($query);

$stmt->bind_param("ssii", $name, $explanatory_text, $sequence, $day_id);

$name = strip_tags($_POST["categoryname"], '<b><i><u>');
$explanatory_text = strip_tags($_POST["explanatory"], '<a><b><i><u>');
if(is_null($_POST["dayid"]))
{
    $day_id = null;
    $sequence = 1;
}
else
{
    $day_id = $_POST["dayid"];
    $subquery = "SELECT 1 + IF(MAX(sequence) IS NULL, 0, MAX(sequence))
        FROM categories WHERE day_id = ?";
    $substmt = $mysqli->prepare($subquery);
    $substmt->bind_param('i', $day_id);
    $substmt->execute();
    $substmt->bind_result($sequence);
    $substmt->fetch();
    $substmt->close();
}

$stmt->execute() or die ("Could not add category to database.");

$cat_id = $mysqli->insert_id;

$clue_ids = array();

$multiplier = ($_POST["point_scheme"] == "second" ? 3 : 2);

for($i = 1; $i <= 5; $i++)
{
    $query = "INSERT INTO clues".
        " (clue_text, category_id, point_value, wrong_point_value)".
        " VALUES (?, ?, ?, ?)";
    $stmt = $mysqli->prepare($query);
    $point_value = $multiplier * $i;
    $wrong_point_value = -$i;
    $stmt->bind_param("siii", strip_tags($_POST["clue$i"], '<a><b><i><u>'),
        $cat_id, $point_value, $wrong_point_value);
    $stmt->execute() or die("Could not add clue $i to database.");
    $clue_ids[$i] = $mysqli->insert_id;
    $stmt->close();
}

for($i = 1; $i <= 5; $i++)
{
    $query = "INSERT INTO responses".
        " (clue_id, response_text, correct) VALUES (?, ?, ?)";
    $stmt = $mysqli->prepare($query);
    $correct = 1;
    $stmt->bind_param("isi", $clue_ids[$i],
        $_POST["response$i"], $correct);
    $stmt->execute() or die("Could not add response $i to database.");
    $stmt->close();
}
$mysqli->close();
echo "Successfully added the category, clues, and responses!";
footer();
?>
</body>
</html>

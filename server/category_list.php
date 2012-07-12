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

/* USAGE OF THIS PAGE: start is an optional get parameter that indicates
the starting point of the listing */
$entries_per_page = 50;

require_once('common.inc');
?>
<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" type="text/css" href="theme.css" />
<link rel="icon" type="image/png" href="shcicon.png" />
<title>Category listing</title>
</head>
<body>
<h1>Category listing</h1>
<?php
$mysqli = connect_mysql();
if(isset($_GET['start']))
{
    $start = $_GET['start'];
}
else
{
    $start = 0;
}
$mysqli->query("USE $mysql_dbname;");
$query = "SELECT COUNT(*) FROM categories";
$stmt = $mysqli->prepare($query);
$stmt->execute();
$stmt->bind_result($num_categories);
$stmt->fetch();
$stmt->close();

$query = "SELECT id, name FROM categories ORDER BY id ASC ".
    "LIMIT ?,$entries_per_page";
$stmt = $mysqli->prepare($query);
if(!$stmt)
{
    $errortext="Could not create a statement to query the".
    " categories table.";
}
else
{
    $stmt->bind_param("i", $start);
    $stmt->execute();
    $stmt->bind_result($id, $name);
}
?>
<table>
<tr><th>ID</th><th>Category name</th></tr>
<?php
if(isset($errortext))
{
    echo "<p class=\"error\">$errortext</p>";
}
else
{
    $count = 1;
    while($stmt->fetch())
    {
        $row_class = ($count % 2 == 0 ? "even" : "odd");
        echo "<tr class=\"$row_class\"><td class=\"idnumber\">".
            "<a href=\"category.php?id=$id\">".
            "$id</a></td><td>$name</td></tr>";
        $count++;
    }
    $stmt->close();
}
$mysqli->close();
?>
</table>
Go to page: 
<?php
$num_pages = ceil($num_categories/$entries_per_page);
printf("(%d %s) ", $num_pages, ($num_pages == 1 ? "page" : "pages"));
$current_page = ($start % 50 == 0 ? ($start/$entries_per_page) + 1 : -1);
for($i = 1; $i <= $num_pages; $i++)
{
    if($i != $current_page)
    {
        printf('<a href="category_list.php?start=%d">%d</a> ',
            $entries_per_page * ($i - 1), $i);
    }
    else
    {
        printf('<b>%d</b> ', $i);
    }
}
?>
<?php
footer();
?>
</body>

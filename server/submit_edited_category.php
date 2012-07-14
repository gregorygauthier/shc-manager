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
<title>Submitting modified category</title>
</head>
<body>
<?php

$mysqli = connect_mysql();

$query = "USE $mysql_dbname;";

$mysqli->query($query);

$query = "UPDATE categories SET name=?, explanatory_text=?
    WHERE id=?";

$stmt = $mysqli->prepare($query);

$stmt->bind_param("ssi", $_POST['catname'], $_POST['explanatory'],
    $_POST['id']);

$stmt->execute();

if($stmt->affected_rows)
{
    echo "<p>Category updated successfully!</p>";
}
else
{
    displayError("The category has not changed or could not be updated.");
}

$stmt->close();

footer();
?>
</body>
</html>

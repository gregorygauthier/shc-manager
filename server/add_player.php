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
do
{
    $mysqli = connect_mysql();
    if(!$mysqli)
    {
        $errortext = "Could not connect to database.";
        break;
    }

    $mysqli->query("USE $mysql_dbname;");

    $query = "SELECT COUNT(*) FROM players WHERE username=?";

    $stmt = $mysqli->prepare($query);
    
    if(!$stmt)
    {
        $errortext = "Could not create prepared statement.";
        break;
    }

    $stmt->bind_param('s', $_POST['name']);

    $stmt->execute();

    $stmt->bind_result($in_use);
    
    $stmt->fetch();
    
    if($in_use)
    {
        $errortext = "Username is already in use.";
        break;
    }
    
    $stmt->close();
    
    $query = "INSERT INTO players (username, teen_eligible, college_eligible,
        atb_eligible) VALUES (?, ?, ?, ?)";
    
    $stmt = $mysqli->prepare($query);
    
    if(!$stmt)
    {
        $errortext = "Could not create prepared statement.";
        break;
    }
    
    $teen = (isset($_POST["teen"]));
    $college = (isset($_POST["college"]));
    $atb = (isset($_POST["atb"]));
    
    $stmt->bind_param('siii', $_POST['name'], $teen, $college, $atb);
    
    $stmt->execute();
    
    $stmt->close();
    
    $mysqli->close();
}
while(false);
?>
<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" type="text/css" href="theme.css" />
<link rel="icon" type="image/png" href="shcicon.png" />
<title><?php
if(isset($errortext))
{
    echo "Error adding user";
}
else
{
    echo "User added successfully";
}
?></title>
</head>
<body>
<?php
if(isset($errortext))
{
    echo "<p class=\"error\">$errortext</p>";
}
else
{
    echo "<p>Successfully added user ". $_POST['name']." to database!</p>";
}
footer();
?>
</body>
</html>

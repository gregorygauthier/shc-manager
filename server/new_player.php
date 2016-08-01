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
<title>Add new player</title>
</head>
<body>
<form action="add_player.php" method="post">
<p><label for="namefield">Player name: </label>
<input type="text" id="namefield" maxlength="50" name="name" /></p>
<p><input type="checkbox" id="teencheck" name="teen" value="yes" />
<label for="teencheck">Teen eligible</label></p>
<p><input type="checkbox" id="collegecheck" name="college" value="yes" />
<label for="collegecheck">College eligible</label></p>
<p><input type="checkbox" id="atbcheck" name="atb" value="yes" />
<label for="atbcheck">ATB eligible</label></p>
<p><input type="checkbox" id="rookiecheck" name="rookie" value="yes" />
<label for="rookiecheck">Rookie eligible</label></p>
<p><input type="checkbox" id="seniorcheck" name="senior" value="yes" />
<label for="seniorcheck">Senior eligible</label></p>
<button type="submit">Submit</button>
</form>
<?php footer();?>
</body>
</html>

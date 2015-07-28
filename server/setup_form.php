<?php
/** Copyright (c) 2015 Gregory Gauthier

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
?>
<!DOCTYPE html>
<html>
<head>
<title>Setup form</title>
</head>
<body>
<h1>SHC Manager setup form</h1>
This setup form will help you get SHC Manager up and running.
<form action="setup.php" method="post">
<h2>MySQL database information</h2>
<label for="mysqlhostfield">Host name:</label>
<input id="mysqlhostfield" type="text" maxlength="255" name="mysqlhost" />
<label for="mysqlusernamefield">Username:</label>
<input id="mysqlusernamefield" type="text" maxlength="255" name="mysqlusername" />
<label for="mysqlpasswordfield">Password:</label>
<input id="mysqlpasswordfield" type="password" maxlength="255" name="mysqlpassword" />
<label for="mysqldbnamefield">Database name:</label>
<input id="mysqldbnamefield" type="text" maxlength="255" name="mysqldbname" />
<h2>Site information</h2>
<label for="siteurlfield">Website URL:</label>
<input id="siteurlfield" type="text" maxlength="255" name="siteurl" />
<label for="saltfield">Salt (enter a random string of about 10 to 16 characters):
<input id="saltfield" type="text" maxlength="255" name="mysqlhost" />
<h2>Admin account information</h2>
<label for="adminaddressfield">E-mail address</label>
<input id="adminaddressfield" type="text" maxlength="255" name="adminaddress"/>
<label for="adminpasswordfield">Password</label>
<input id="adminpasswordfield" type="password" maxlength="255" name="adminpassword"/>
<button type="submit">Setup</button>
</form>
</body>
</html>

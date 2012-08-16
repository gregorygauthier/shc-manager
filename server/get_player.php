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

/* USAGE OF THIS PAGE: This is an internal page used only by edit_player.php.

id is an required parameter that autoselects
the player whose responses are to be filled in.

The result is not well-formed html, as it is intended for inclusion in
the edit_player.php form.  */

do
{
    if(!isset($_GET['id']) or is_null($_GET['id']))
    {
        $errortext = "No user id provided";
        break;
    }
    $id = $_GET['id'];
    $mysqli = connect_mysql();
    if(!$mysqli)
    {
        $errortext = "Could not connect to database.";
        break;
    }
    $mysqli->query("USE $mysql_dbname;");
    $query = "SELECT username, teen_eligible, college_eligible, atb_eligible,
        rookie_eligible FROM players WHERE id=?";
    $stmt = $mysqli->prepare($query);
    if(!$stmt)
    {
        $errortext = "Could not prepare statement.";
        $mysqli->close();
        break;
    }
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->bind_result($username, $teen, $college, $atb, $rookie);
    if(!$stmt->fetch())
    {
        $errortext = "Invalid user id provided.";
    }
    $stmt->close();
    $mysqli->close();
} while (false);

if(isset($errortext))
{
    displayError($errortext);
}
else
{
    echo '<p><label for="namefield">Player name: </label>';
    printf(<<<INPUT
<input type="text" id="namefield" maxlength="50" name="name"
value="%s" />
INPUT
, $username);
    echo '</p>';
    printf(<<<INPUT
<p><input type="checkbox" id="teencheck" name="teen" value="yes"
%s />
<label for="teencheck">Teen eligible</label></p>
INPUT
, $teen ? 'checked="checked"' : '');
    printf(<<<INPUT
<p><input type="checkbox" id="collegecheck" name="college" value="yes"
%s />
<label for="collegecheck">College eligible</label></p>
INPUT
, $college ? 'checked="checked"' : '');
    printf(<<<INPUT
<p><input type="checkbox" id="atbcheck" name="atb" value="yes"
%s />
<label for="atbcheck">ATB eligible</label></p>
INPUT
, $atb ? 'checked="checked"' : '');
    echo '<button type="submit">Submit</button>';
    printf(<<<INPUT
<p><input type="checkbox" id="rookiecheck" name="rookie" value="yes"
%s />
<label for="rookiecheck">Rookie eligible</label></p>
INPUT
, $rookie ? 'checked="checked"' : '');
    echo '<button type="submit">Submit</button>';
}
?>

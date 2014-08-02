<?php
/** Copyright (c) 2014 Gregory Gauthier

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

/*
This file contains classes with the database operations.
This should facilitate unit testing.
*/

require_once("config.inc");

class Database
{
    public function add_round($name, $sequence = null, $is_regular)
    {
        global $mysql_dbname;
        try
        {
            $mysqli = connect_mysql();
            if(!$mysqli)
            {
                throw new Exception("Could not connect to database.");
            }
            
            if(!$mysqli->query("USE $mysql_dbname;"))
            {
                throw new Exception($mysqli->error);
            }
            $query = "INSERT INTO rounds (name, sequence, is_regular) VALUES (?, ?, ?)";
            
            $stmt = $mysqli->prepare($query);
            if(!$stmt)
            {
                throw new Exception($stmt->error);
            }
            $stmt->bind_param('sii', $name, $sequence, $is_regular);
            
            if (is_null($sequence))
            {
                /* By default, make the sequence number of the new round
                one more than the highest sequence number assigned,
                or zero if no sequence numbers are assigned */
                $subquery = "SELECT IF(MAX(sequence) IS NULL, 0, MAX(sequence)) FROM rounds";
                $substmt = $mysqli->prepare($subquery);
                if(!$substmt)
                {
                    throw new Exception($substmt->error);
                }
                $substmt->execute();
                $substmt->bind_result($sequence);
                $substmt->fetch();
                $substmt->close();
                $sequence++;
            }
            
            $stmt->execute();
            if(!$stmt->affected_rows)
            {
                throw new Exception("Could not add round to database: {$stmt->error}");
            }
            $stmt->close();
            $mysqli->close();
        }
        catch (Exception $e)
        {
            if($mysqli)
            {
                $mysqli->close();
            }
            throw $e;
        }
    }
}

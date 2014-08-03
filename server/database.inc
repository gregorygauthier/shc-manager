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
            $return_val = $mysqli->insert_id;
            $mysqli->close();
            return $return_val;
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
    
    public function add_day($round_id, $name, $date, $url)
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
            
            $query = "INSERT INTO days (name, round_id, play_date,
                sequence, thread_url)
                VALUES (?, ?, ?, ?, ?)";
            $stmt = $mysqli->prepare($query);
            if(!$stmt)
            {
                throw new Exception($stmt->error);
            }
            $stmt->bind_param('sisis', $name, $round_id, $date, $seq, $url);
            $subquery = "SELECT IF(MAX(sequence) IS NULL, 0, MAX(sequence)) FROM
                days WHERE round_id = ?";
            $substmt = $mysqli->prepare($subquery);
            if(!$substmt)
            {
                throw new Exception($substmt->error);
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
                throw new Exception("Could not add day to database: {$stmt->error}");
            }
            $return_val = $mysqli->insert_id;
            $mysqli->close();
            return $return_val;
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
    
    public function add_category($name, $explanatory_text, $seq = null, $day_id)
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
            
            $query = "INSERT INTO categories
                (name, explanatory_text, sequence, day_id)
                VALUES (?, ?, ?, ?)";
            $stmt = $mysqli->prepare($query);
            if(!$stmt)
            {
                throw new Exception($stmt->error);
            }
            $stmt->bind_param('ssii', $name, $explanatory_text, $seq, $day_id);
            if(is_null($day_id))
            {
                $seq = 1;
            }
            elseif(is_null($seq))
            {
                $subquery = "SELECT IF(MAX(sequence) IS NULL, 0, MAX(sequence)) FROM
                    categories WHERE day_id = ?";
                $substmt = $mysqli->prepare($subquery);
                if(!$substmt)
                {
                    throw new Exception($substmt->error);
                }
                $substmt->bind_param('i', $day_id);
                $substmt->execute();
                $substmt->bind_result($seq);
                $substmt->fetch();
                $substmt->close();
                $seq++;
            }
            
            $stmt->execute();
            if(!$stmt->affected_rows)
            {
                throw new Exception("Could not add category to database: {$stmt->error}");
            }
            $stmt->close();
            $return_val = $mysqli->insert_id;
            $mysqli->close();
            return $return_val;
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
    
    public function add_clue($clue_text, $category_id, $point_value, $wrong_point_value)
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
            
            $query = "INSERT INTO clues
                (clue_text, category_id, point_value, wrong_point_value)
                VALUES (?, ?, ?, ?)";
            $stmt = $mysqli->prepare($query);
            if(!$stmt)
            {
                throw new Exception($stmt->error);
            }
            $stmt->bind_param('siii', $clue_text, $category_id, $point_value, $wrong_point_value);
            
            $stmt->execute();
            if(!$stmt->affected_rows)
            {
                throw new Exception("Could not add clue to database: {$stmt->error}");
            }
            $stmt->close();
            $return_val = $mysqli->insert_id;
            $mysqli->close();
            return $return_val;
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
    
    public function add_response($clue_id, $response_text, $correct)
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
            
            $query = "INSERT INTO responses
                (clue_id, response_text, correct)
                VALUES (?, ?, ?)";
            $stmt = $mysqli->prepare($query);
            if(!$stmt)
            {
                throw new Exception($stmt->error);
            }
            $stmt->bind_param('isi', $clue_id, $response_text, $correct);
            
            $stmt->execute();
            if(!$stmt->affected_rows)
            {
                throw new Exception("Could not add response to database: {$stmt->error}");
            }
            $stmt->close();
            $return_val = $mysqli->insert_id;
            $mysqli->close();
            return $return_val;
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

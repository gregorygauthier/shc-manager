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
                throw new Exception($mysqli->error);
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
                    throw new Exception($mysqli->error);
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
                throw new Exception("Could not add round to database: {$mysqli->error}");
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
                throw new Exception($mysqli->error);
            }
            $stmt->bind_param('sisis', $name, $round_id, $date, $seq, $url);
            $subquery = "SELECT IF(MAX(sequence) IS NULL, 0, MAX(sequence)) FROM
                days WHERE round_id = ?";
            $substmt = $mysqli->prepare($subquery);
            if(!$substmt)
            {
                throw new Exception($mysqli->error);
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
                throw new Exception("Could not add day to database: {$mysqli->error}");
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
                throw new Exception($mysqli->error);
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
                    throw new Exception($mysqli->error);
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
                throw new Exception("Could not add category to database: {$mysqli->error}");
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
                throw new Exception($mysqli->error);
            }
            $stmt->bind_param('siii', $clue_text, $category_id, $point_value, $wrong_point_value);
            
            $stmt->execute();
            if(!$stmt->affected_rows)
            {
                throw new Exception("Could not add clue to database: {$mysqli->error}");
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
                throw new Exception($mysqli->error);
            }
            $stmt->bind_param('isi', $clue_id, $response_text, $correct);
            
            $stmt->execute();
            if(!$stmt->affected_rows)
            {
                throw new Exception("Could not add response to database: {$mysqli->error}");
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
    
    public function add_player($username, $teen, $college, $atb, $rookie)
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
            
            $query = "SELECT COUNT(*) FROM players WHERE username=?";
            $stmt = $mysqli->prepare($query);
            if(!$stmt)
            {
                throw new Exception($mysqli->error);
            }
            $stmt->bind_param('s', $username);
            $stmt->execute();
            $stmt->bind_result($in_use);
            $stmt->fetch();
            $stmt->close();
            if($in_use)
            {
                return 0;
            }
            
            $query = "INSERT INTO players
                (username, teen_eligible, college_eligible,
                atb_eligible, rookie_eligible) VALUES (?, ?, ?, ?, ?)";
            $stmt = $mysqli->prepare($query);
            if(!$stmt)
            {
                throw new Exception($mysqli->error);
            }
            $stmt->bind_param('siiii', $username, $teen, $college, $atb, $rookie);
            
            $stmt->execute();
            if(!$stmt->affected_rows)
            {
                throw new Exception("Could not add player to database: {$mysqli->error}");
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
    
    public function add_player_response($player_id, $clue_id, $response_text)
    {
        if(is_null($player_id) or is_null($clue_id))
        {
            return 0;
        }
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
            
            /* Check if there is already a response recorded */
            $check_query = "SELECT COUNT(*) FROM player_responses WHERE
                player_id = ? AND clue_id = ?";
            $stmt = $mysqli->prepare($check_query);
            if(!$stmt)
            {
                throw new Exception($mysqli->error);
            }
            $stmt->bind_param('ii', $player_id, $clue_id);
            $stmt->execute();
            $stmt->bind_result($response_recorded);
            $stmt->fetch();
            $stmt->close();
            
            $insert_query = "INSERT INTO player_responses (player_id, clue_id,
                response_text) VALUES (?, ?, ?)";
            $update_query = "UPDATE player_responses SET response_text = ?
                WHERE player_id = ? AND clue_id = ?";
            if($response_recorded)
            {
                $stmt = $mysqli->prepare($update_query);
                if(!$stmt)
                {
                    throw new Exception($mysqli->error);
                }
                $stmt->bind_param('sii', $response_text, $player_id, $clue_id);
                $stmt->execute();
                $return_val = 1;
            }
            else
            {
                $stmt = $mysqli->prepare($insert_query);
                if(!$stmt)
                {
                    throw new Exception($mysqli->error);
                }
                $stmt->bind_param('iis', $player_id, $clue_id, $response_text);

                $stmt->execute();
                if(!$stmt->affected_rows)
                {
                    throw new Exception("Could not add player response to database: {$mysqli->error}");
                }
                $stmt->close();
                $return_val = $mysqli->insert_id;
            }
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
    
    public function update_response($response_id, $response_text, $correct)
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
            
            $query = "UPDATE responses SET response_text = ?, correct = ?
                WHERE id=?";
            $stmt = $mysqli->prepare($query);
            if(!$stmt)
            {
                throw new Exception($mysqli->error);
            }
            $stmt->bind_param('sii', $response_text, $correct, $response_id);
            
            $stmt->execute();
            $stmt->close();
            $mysqli->close();
            return 1;
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
    
    public function delete_response($response_id)
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
            
            $query = "DELETE FROM responses WHERE id=?";
            $stmt = $mysqli->prepare($query);
            if(!$stmt)
            {
                throw new Exception($mysqli->error);
            }
            $stmt->bind_param('i', $response_id);
            
            $stmt->execute();
            $stmt->close();
            $mysqli->close();
            return 1;
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
    
    public function get_responses_for_clue($clue_id, $include_ungraded = false)
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
            if($include_ungraded)
            {
                $query = "SELECT r.id, r.response_text, r.correct
                    FROM responses AS r
                    WHERE r.clue_id = ?
                    UNION
                    SELECT 0, REPLACE(pr.response_text, '.', '\\\\.'), NULL
                    FROM player_responses AS pr WHERE
                    pr.clue_id = ? AND
                    pr.grade IS NULL AND
                    pr.response_text IS NOT NULL AND
                    pr.response_text != ''";
                $stmt = $mysqli->prepare($query);
                if(!$stmt)
                {
                    throw new Exception($mysqli->error);
                }
                $stmt->bind_param('ii', $clue_id, $clue_id);
            }
            else
            {
                $query = "SELECT id, response_text, correct FROM responses
                    WHERE clue_id = ?";
                $stmt = $mysqli->prepare($query);
                if(!$stmt)
                {
                    throw new Exception($mysqli->error);
                }
                $stmt->bind_param('i', $clue_id);
            }
            
            $stmt->execute();
            $results = array();
            $stmt->bind_result($response_id, $response_text, $correct);
            $clue = Database::get_clue_by_id($clue_id);
            while($stmt->fetch())
            {
                $response = new Response();
                $response->id = $response_id;
                $response->response_text = $response_text;
                $response->correct = $correct;
                $response->clue = $clue;
                $results[] = $response;
            }
            $stmt->close();
            $mysqli->close();
            return $results;
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
    
    public function get_round_by_id($id)
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
            
            $query = "SELECT name, sequence, is_regular FROM rounds
                WHERE id=?";
            $stmt = $mysqli->prepare($query);
            if(!$stmt)
            {
                throw new Exception($mysqli->error);
            }
            $stmt->bind_param('i', $id);
            
            $stmt->execute();
            $stmt->bind_result($name, $sequence, $is_regular);
            if($stmt->fetch())
            {
                $result = new Round();
                $result->id = $id;
                $result->name = $name;
                $result->sequence = $sequence;
                $result->is_regular = $is_regular;
            }
            else
            {
                $result = null;
            }
            $stmt->close();
            $mysqli->close();
            return $result;
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
    
    public function get_day_by_id($id)
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
            
            $query = "SELECT name, round_id, play_date, sequence,
                thread_url, highest_parsed_post FROM days
                WHERE id=?";
            $stmt = $mysqli->prepare($query);
            if(!$stmt)
            {
                throw new Exception($mysqli->error);
            }
            $stmt->bind_param('i', $id);
            
            $stmt->execute();
            $stmt->bind_result($name, $round_id, $play_date,
                $sequence, $thread_url, $highest_parsed_post);
            if($stmt->fetch())
            {
                $result = new Day();
                $result->id = $id;
                $result->name = $name;
                $result->round = Database::get_round_by_id($round_id);
                $result->play_date = $play_date;
                $result->sequence = $sequence;
                $result->thread_url = $thread_url;
                $result->highest_parsed_post = $highest_parsed_post;
            }
            else
            {
                $result = null;
            }
            $stmt->close();
            $mysqli->close();
            return $result;
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
    
    public function get_category_by_id($id)
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
            
            $query = "SELECT name, explanatory_text, day_id, sequence
                FROM categories
                WHERE id=?";
            $stmt = $mysqli->prepare($query);
            if(!$stmt)
            {
                throw new Exception($mysqli->error);
            }
            $stmt->bind_param('i', $id);
            
            $stmt->execute();
            $stmt->bind_result($name, $explanatory_text, $day_id, $sequence);
            if($stmt->fetch())
            {
                $result = new Category();
                $result->id = $id;
                $result->name = $name;
                $result->explanatory_text = $explanatory_text;
                $result->day = Database::get_day_by_id($day_id);
                $result->sequence = $sequence;
            }
            else
            {
                $result = null;
            }
            $stmt->close();
            $mysqli->close();
            return $result;
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
    
    public function get_clue_by_id($id)
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
            
            $query = "SELECT clue_text, category_id,
                point_value, wrong_point_value FROM clues
                WHERE id=?";
            $stmt = $mysqli->prepare($query);
            if(!$stmt)
            {
                throw new Exception($mysqli->error);
            }
            $stmt->bind_param('i', $id);
            
            $stmt->execute();
            $stmt->bind_result($clue_text, $category_id, $point_value, $wrong_point_value);
            if($stmt->fetch())
            {
                $result = new Clue();
                $result->id = $id;
                $result->clue_text = $clue_text;
                $result->category = Database::get_category_by_id($category_id);
                $result->point_value = $point_value;
                $result->wrong_point_value = $wrong_point_value;
            }
            else
            {
                $result = null;
            }
            $stmt->close();
            $mysqli->close();
            return $result;
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

class Response
{
    public $id;
    public $clue; /*a Clue struct */
    public $response_text;
    public $correct;
}

class Clue
{
    public $id;
    public $clue_text;
    public $category; /* a Category struct */
    public $point_value;
    public $wrong_point_value;
}

class Category
{
    public $id;
    public $name;
    public $explanatory_text;
    public $day; /* a Day struct */
    public $sequence;
}

class Day
{
    public $id;
    public $name;
    public $round; /* a Round struct */
    public $play_date;
    public $sequence;
    public $thread_url;
    public $highest_parsed_post;
}

class Round
{
    public $id;
    public $name;
    public $sequence;
    public $is_regular;
}

<?php 
    if(!defined('SECURE'))
        die('Access denied');
    
    include "Database.php";
    
    function get_connection_status(){

        if (Database::getConnection()->connect_error)
            return 'Not Connected';
        else
            return 'Connected';

    }

    function get_random_game(){
        
        $conn = Database::getConnection();

        $query = 'SELECT * FROM '.constant('BOT_TABLE').' ORDER BY RAND() LIMIT 1';
        $result = $conn->query($query);
        if($result == false)
            echo 'Failed Query';
        return $result->fetch_assoc();

    }

    function get_single_game_record($gameNumber){
        $conn = Database::getConnection();
        $query = "SELECT * FROM " . constant('GAME_TABLE') . " WHERE game_number = ?";
        $query = $conn->prepare($query);
        $query->bind_param("i", $gameNumber);
        $query->execute();
        $result = $query->get_result();
        $row = $result->fetch_assoc();
        return $row;
        
    }

    function check_single_game_record($gameNumber){
        $conn = Database::getConnection();

        $rowCheck = "SELECT game_number FROM " . constant('GAME_TABLE') . " WHERE game_number = ?";
        $stmt = $conn->prepare($rowCheck);
        $stmt->bind_param("i", $gameNumber);
        $stmt->execute();


        $result=$stmt->get_result();
        $stmt->close();

        if($result->num_rows==0)
            return false;
        else
            return true;
    }

    function create_single_game_record($gameNumber){
        $conn = Database::getConnection();
        $conn->begin_transaction();
        try{
            $sql = "INSERT INTO " . constant('GAME_TABLE') . "(game_number, BotWins, UserWins) VALUES(".$gameNumber.", 0, 0);";
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $conn->commit();
        }catch(Exception $e){
            error_log("Game Record Update Fail");
            $conn->rollback();
            throw $e;
        }
    }

    function write_completed_game(){

        $gameNumber = (int)$_SESSION['current_game']['id'];
        update_total_record();
        update_game_record($gameNumber);
        $index = insert_user_data();
        $_SESSION['data_written'] = true;
        return $index;
    }

    function get_summary_start($index){
        $conn = Database::getConnection();

        $query = "SELECT game_number, user_won FROM " . constant('USER_TABLE') . " WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $index);
        $stmt->execute();


        $result=$stmt->get_result();
        $stmt->close();
        $rtn_val = $result->fetch_assoc();


        return $rtn_val;
    }


    function update_total_record(){
        $conn = Database::getConnection();

        $conn->begin_transaction();

        try{

            if($_SESSION['user_won'])
                $updateQuery = "UPDATE " . constant('TOTAL_TABLE') . " SET UserWins = UserWins + 1 WHERE id = 1";
            else
                $updateQuery = "UPDATE " . constant('TOTAL_TABLE') . " SET BotWins = BotWins + 1 WHERE id = 1";

            $stmt = $conn->prepare($updateQuery);
            $stmt->execute();
            $conn->commit();

        }catch(Exception $e){
            error_log("Failed to update Total Record");
            $conn->rollback();
            throw $e;
        }

        $stmt->close();

    }

    function get_total_record(){
        $conn = Database::getConnection();
        $query = "SELECT * FROM " . constant("TOTAL_TABLE");
        $result = $conn->query($query);
        return $result->fetch_assoc();
    }

    function update_game_record($gameNumber){
        $conn = Database::getConnection();

        $conn->begin_transaction();

        try{

            if(! check_single_game_record($gameNumber))
                create_single_game_record($gameNumber);
            

            if($_SESSION['user_won'])
                $updateQuery = "UPDATE " . constant('GAME_TABLE') . " SET UserWins = UserWins + 1 WHERE game_number = ?";
            else
                $updateQuery = "UPDATE " . constant('GAME_TABLE') . " SET BotWins = BotWins + 1 WHERE game_number = ?";

            $stmt = $conn->prepare($updateQuery);
            $stmt->bind_param("i", $gameNumber);
            $stmt->execute();

            $conn->commit();

        }catch(Exception $e){
            error_log("Game Record Update Fail");
            $conn->rollback();
            throw $e;
        }

        $stmt->close();
    }

    function insert_user_data(){
        $conn = Database::getConnection();

        $conn->begin_transaction();

        try{
            $sql = "INSERT INTO ".constant('USER_TABLE')." 
                        (game_number, date, user_solved, user_won, num_guesses, guess1, guess1_state, guess2, guess2_state, guess3, guess3_state, guess4, guess4_state, guess5, guess5_state, guess6, guess6_state)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                    $date = date('Y-m-d');
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("isiiissssssssssss",
                        $_SESSION['current_game']['id'], 
                        $date, 
                        $_SESSION['user_solved'], 
                        $_SESSION['user_won'], 
                        $_SESSION['current_guess_num'], 
                        $_SESSION['user_guess_array'][0], $_SESSION['user_state_array'][0], 
                        $_SESSION['user_guess_array'][1], $_SESSION['user_state_array'][1], 
                        $_SESSION['user_guess_array'][2], $_SESSION['user_state_array'][2], 
                        $_SESSION['user_guess_array'][3], $_SESSION['user_state_array'][3], 
                        $_SESSION['user_guess_array'][4], $_SESSION['user_state_array'][4], 
                        $_SESSION['user_guess_array'][5], $_SESSION['user_state_array'][5]
                    );
            $stmt->execute();
            $index = $conn->insert_id;
            $conn->commit();
            $stmt->close();
            return $index;
        }catch(Exception $e){
            error_log("Failed to Update User Data");
            $conn->rollback();
            $stmt->close();
            throw $e;
        }
        
        
    }

    function get_last_10_challenges(){
        $conn = Database::getConnection();
        
        $sql = "SELECT u.game_number, 
                    u.guess1_state, u.guess2_state, u.guess3_state, 
                    u.guess4_state, u.guess5_state, u.guess6_state, u.user_won, 
                    b.guess1_state AS bot_guess1_state, b.guess2_state AS bot_guess2_state, 
                    b.guess3_state AS bot_guess3_state, b.guess4_state AS bot_guess4_state, 
                    b.guess5_state AS bot_guess5_state, b.guess6_state AS bot_guess6_state,
                    w.BotWins, w.UserWins
                FROM ".constant('USER_TABLE')." u
                JOIN ".constant('BOT_TABLE')." b ON u.game_number = b.id
                JOIN ".constant('GAME_TABLE')." w ON u.game_number = w.game_number
                ORDER BY u.id DESC 
                LIMIT 10";
        try{
            $result = $conn->query($sql);
            return $result->fetch_all(MYSQLI_ASSOC);

        }catch(Exception $e){
            throw $e;
        }
    }

    function get_summary_data($gameNumber){
        $conn = Database::getConnection();
        $sql = "SELECT u.game_number, 
                    u.guess1_state, u.guess2_state, u.guess3_state, 
                    u.guess4_state, u.guess5_state, u.guess6_state, u.user_won, b.goal AS goal,
                    b.guess1_state AS bot_guess1_state, b.guess2_state AS bot_guess2_state, 
                    b.guess3_state AS bot_guess3_state, b.guess4_state AS bot_guess4_state, 
                    b.guess5_state AS bot_guess5_state, b.guess6_state AS bot_guess6_state,
                    w.BotWins, w.UserWins
                FROM ".constant('USER_TABLE')." u
                JOIN ".constant('BOT_TABLE')." b ON u.game_number = b.id
                JOIN ".constant('GAME_TABLE')." w ON u.game_number = w.game_number
                WHERE u.game_number = ? AND b.id = ?";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $gameNumber, $gameNumber);
        try{
            $stmt->execute();
            $result = $stmt->get_result();
            return $result->fetch_assoc();

        }catch(Exception $e){
            throw $e;
        }
    }
?>



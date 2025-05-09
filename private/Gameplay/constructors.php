<?php 

    
    define('TOTAL_ROWS', 6);
    define('TOTAL_COLS', 5); 

    const KEYBOARD_LAYOUT = [
        ['Enter', 'Back'],
        ['Q', 'W', 'E', 'R', 'T', 'Y', 'U', 'I', 'O', 'P'],
        ['A', 'S', 'D', 'F', 'G', 'H', 'J', 'K', 'L'],
        ['Z', 'X', 'C', 'V', 'B', 'N', 'M']];

    function build_answer_area(){

        for($i = 0; $i < constant('TOTAL_ROWS'); $i++){
            for($j = 0; $j < constant('TOTAL_COLS'); $j++)
                echo echo_letter_square_arena($i, $j);
        }
        
    }

    function echo_letter_square_arena($row, $column){
        if(isset($_SESSION['user_state_array'][$row])){
            if($_SESSION['user_state_array'][$row][$column] == 'g')
                return '<div class="green-letter-square">'.strtoupper($_SESSION['user_guess_array'][$row][$column]).'</div>';
            elseif($_SESSION['user_state_array'][$row][$column] == 'y')
                return '<div class="yellow-letter-square">'.strtoupper($_SESSION['user_guess_array'][$row][$column]).'</div>';
            elseif($_SESSION['user_state_array'][$row][$column] == '?')
                return '<div class="invalid-letter-square">'.strtoupper($_SESSION['user_guess_array'][$row][$column]).'</div>';
        }
        else
            return '<div class="letter-square"></div>';
            
    }

    function build_keyboard() {
        echo '<div class="bkrow"><div class="backspace">Enter</div><div class="backspace">←</div></div>';
        
        for ($i = 1; $i < count(KEYBOARD_LAYOUT); $i++) {
            echo '<div class="keyboard-row">';
            for ($j = 0; $j < count(KEYBOARD_LAYOUT[$i]); $j++) {
                $location = [$i, $j]; // Current key location
    
                if (isset($_SESSION['gLetters']) && in_array($location, $_SESSION['gLetters'] ?? [])) {
                    error_log('Green Letter: ' . $i . ':' . $j);
                    echo '<div class="key-square" id="green">' . KEYBOARD_LAYOUT[$i][$j] . '</div>';
                } 
                elseif (isset($_SESSION['yLetters']) && in_array($location, $_SESSION['yLetters'] ?? [])) {
                    error_log('Yellow Letter: ' . $i . ':' . $j);
                    echo '<div class="yellow-key-square" id="yellow">' . KEYBOARD_LAYOUT[$i][$j] . '</div>';
                } 
                elseif (isset($_SESSION['missingLetters']) && in_array($location, $_SESSION['missingLetters'] ?? [])) {
                    error_log('Invalid Letter: ' . $i . ':' . $j);
                    echo '<div class="key-square" id="invalid">' . KEYBOARD_LAYOUT[$i][$j] . '</div>';
                } 
                else {
                    echo '<div class="key-square">' . KEYBOARD_LAYOUT[$i][$j] . '</div>';
                }
            }
            echo '</div>'; // Close the row
        }
    }

    function echo_letter_square_home($word, $letter){
        if($word == null)
            return '<div class="home-letter-square"></div>';
        if($word[$letter] == 'g')
            return '<div class="home-green-letter-square"></div>';
        elseif($word[$letter] == 'y')
            return '<div class="home-yellow-letter-square"></div>';
        elseif($word[$letter] == '?')
            return '<div class="home-invalid-letter-square"></div>';

    }   

    function create_recent_user_results($state1, $state2, $state3, $state4, $state5, $state6){
        $allStates = [$state1, $state2, $state3, $state4, $state5, $state6];
        echo '<div class="recent-user-state">';
        echo '<h3 class="full-row">User</h3>';
        for($i=0; $i<6; $i++)
            for($j=0; $j<5; $j++)
                echo echo_letter_square_home($allStates[$i], $j);
        echo '</div>';

    }

    function create_recent_bot_results($state1, $state2, $state3, $state4, $state5, $state6){
        $allStates = [$state1, $state2, $state3, $state4, $state5, $state6];
        echo '<div class="recent-bot-state">';
        echo '<h3 class="full-row">Bot</h3>';
        for($i=0; $i<6; $i++)
            for($j=0; $j<5; $j++)
                echo echo_letter_square_home($allStates[$i], $j);
        echo '</div>';
    }

    function create_share_text($gameData){

        $copyText = $gameData['user_won'] == 1 ? "I Beat The Bot\n" : "The Bot Won\n\n(Me/Bot)\n";
        $copyText .= get_share_state_line($gameData['guess1_state'], $gameData['bot_guess1_state']);
        $copyText .= get_share_state_line($gameData['guess2_state'], $gameData['bot_guess2_state']);
        $copyText .= get_share_state_line($gameData['guess3_state'], $gameData['bot_guess3_state']);
        $copyText .= get_share_state_line($gameData['guess4_state'], $gameData['bot_guess4_state']);
        $copyText .= get_share_state_line($gameData['guess5_state'], $gameData['bot_guess5_state']);
        $copyText .= get_share_state_line($gameData['guess6_state'], $gameData['bot_guess6_state']);

        $copyText .= "\nbeatmybot.ai";
        return $copyText;
    }

    function get_share_state_line($userState, $botState){

        $rtnVal = "";

        if($userState != null)
            for($i = 0; $i<strlen($userState); $i++){
                if($userState[$i] == 'g')
                    $rtnVal .= "\u{1F7E9}";
                elseif($userState[$i] == 'y')
                    $rtnVal .= "\u{1F7E8}";
                elseif($userState[$i] == '?')
                    $rtnVal .= "\u{2B1B}";
            }
        else
            $rtnVal .= "\u{2B1C}\u{2B1C}\u{2B1C}\u{2B1C}\u{2B1C}";

        $rtnVal .= "\t";

        if($botState != null)
            for($i = 0; $i<strlen($botState); $i++){
                if($botState[$i] == 'g')
                    $rtnVal .= "\u{1F7E9}";
                elseif($botState[$i] == 'y')
                    $rtnVal .= "\u{1F7E8}";
                elseif($botState[$i] == '?')
                    $rtnVal .= "\u{2B1B}";
            }
            else
                $rtnVal .= "\u{2B1C}\u{2B1C}\u{2B1C}\u{2B1C}\u{2B1C}";

        $rtnVal .= "\n";

        return $rtnVal;
    }

    function build_summary_page($gameNumber){

        $gameData = get_summary_data($gameNumber);


        echo '<div class="single-game">';
        echo    "<h3>Game Number: " . $gameData['game_number'] . "</h3>";
        echo    "<h3>Goal Word: " . $gameData['goal'] . "</h3>";
        echo    "<h3>Challenge Outcome: " . ($gameData['user_won'] == 1 ? "User Won" : "Bot Won") . "</h3>";
        echo    "<h3>Bot Record This Game: " . $gameData['BotWins'] . ":" . $gameData['UserWins'] . "</h3><br>";
        echo    '<div class="recent-game-state">';
                    create_recent_user_results( $gameData['guess1_state'], $gameData['guess2_state'], $gameData['guess3_state'], 
                                                $gameData['guess4_state'], $gameData['guess5_state'], $gameData['guess6_state']);
                    create_recent_bot_results(  $gameData['bot_guess1_state'], $gameData['bot_guess2_state'], $gameData['bot_guess3_state'],
                                                $gameData['bot_guess4_state'], $gameData['bot_guess5_state'], $gameData['bot_guess6_state']);
        echo    '</div>';
        echo "</div>";

        return create_share_text($gameData);
    }

    function build_home_page_recent_games(){
        $games = get_last_10_challenges();

        echo '<div class="recent-games">';
        for($i=0; $i<count($games); $i++){
            echo '<div class="single-game">';
            echo    "<h3>Game Number: " . $games[$i]['game_number'] . "</h3>";
            echo    "<h3>Challenge Outcome: " . ($games[$i]['user_won'] == 1 ? "User Won" : "Bot Won") . "</h3>";
            echo    "<h3>Bot Record This Game: " . $games[$i]['BotWins'] . ":" . $games[$i]['UserWins'] . "</h3><br>";
            echo    '<div class="recent-game-state">';
                        create_recent_user_results( $games[$i]['guess1_state'], $games[$i]['guess2_state'], $games[$i]['guess3_state'], 
                                                    $games[$i]['guess4_state'], $games[$i]['guess5_state'], $games[$i]['guess6_state']);
                        create_recent_bot_results(  $games[$i]['bot_guess1_state'], $games[$i]['bot_guess2_state'], $games[$i]['bot_guess3_state'],
                                                    $games[$i]['bot_guess4_state'], $games[$i]['bot_guess5_state'], $games[$i]['bot_guess6_state']);
            echo    '</div>';
            echo "</div>";
        }
        echo "</div>";
    }

?>
<?php

    
    function start_game(){
        $_SESSION['user_state_array'] = [0=>null, 1=>null, 2=>null, 3=>null, 4=>null, 5=>null];
        $_SESSION['user_guess_array'] = [0=>null, 1=>null, 2=>null, 3=>null, 4=>null, 5=>null];
        $_SESSION['required_letters'] = array();
        $_SESSION['current_guess_num'] = 1;
        $_SESSION['game_over'] = false;
        $_SESSION['user_won'] = null;
        $_SESSION['gLetters'] = [];
        $_SESSION['yLetters'] = [];
        $_SESSION['missingLetters'] = [];
        if(!isset($_SESSION['shareable']))
            $_SESSION['shareable'] = [$_SESSION['current_game']['id']];
            
    }

    function create_bot_state_array(){
        $_SESSION['bot_state_array'] = [    0=>$_SESSION['current_game']['guess1_state'],
                                            0=>$_SESSION['current_game']['guess2_state'],
                                            0=>$_SESSION['current_game']['guess3_state'],
                                            0=>$_SESSION['current_game']['guess4_state'],
                                            0=>$_SESSION['current_game']['guess5_state'],
                                            0=>$_SESSION['current_game']['guess6_state']];
    }

    function get_key_index($letter){

        for($i = 1; $i < count(constant('KEYBOARD_LAYOUT')); $i++)
            for($j = 0; $j < count(constant('KEYBOARD_LAYOUT')[$i]); $j++){
                if (constant('KEYBOARD_LAYOUT')[$i][$j] == strtoupper($letter)){
                    $row = $i;
                    $column = $j;
                    return array($row, $column);
                }
            }
    }

    function add_state_user_data($state){
        if($_SESSION['current_guess_num'] == 1)
            $_SESSION['user_state_array'][0] = $state;
        elseif($_SESSION['current_guess_num'] == 2)
            $_SESSION['user_state_array'][1] = $state;
        elseif($_SESSION['current_guess_num'] == 3)
            $_SESSION['user_state_array'][2] = $state;
        elseif($_SESSION['current_guess_num'] == 4)
            $_SESSION['user_state_array'][3] = $state;
        elseif($_SESSION['current_guess_num'] == 5)
            $_SESSION['user_state_array'][4] = $state;
        elseif($_SESSION['current_guess_num'] == 6)
            $_SESSION['user_state_array'][5] = $state;
    }

    function check_state($guess){

        $state = '';
        $goal = $_SESSION['current_game']['goal'];
        $index = -1;

        if(!isset($_SESSION['required_letters']))
            $_SESSION['required_letters'] = [];
        if(!isset($_SESSION['yLetters']))
            $_SESSION['yLetters'] = [];
        if(!isset($_SESSION['gLetters']))
        $_SESSION['gLetters'] = [];
        if(!isset($_SESSION['missingLetters']))
        $_SESSION['missingLetters'] = [];

        for($i = 0; $i < strlen($goal); $i++){
            if($guess[$i] == $goal[$i]){
                $state .= 'g';
                array_push($_SESSION['gLetters'], get_key_index($guess[$i]));

                if(!in_array($guess[$i], $_SESSION['required_letters'])){
                    array_push($_SESSION['required_letters'], $guess[$i]);
                    error_log("Adding To Required Letters");
                }
            }
            elseif(strpos($goal, $guess[$i]) !== false){
                $state .= 'y';
                array_push($_SESSION['yLetters'], get_key_index($guess[$i]));
                if(!in_array($guess[$i], $_SESSION['required_letters'])){
                    array_push($_SESSION['required_letters'], $guess[$i]);
                    error_log("Adding To Required Letters");
                }
            }
            else{
                $state .= '?';
                array_push($_SESSION['missingLetters'], get_key_index($guess[$i]));
            }
        }

        add_state_user_data($state);

        if($state == 'ggggg' or $_SESSION['current_guess_num'] >= 6)
        {
            $_SESSION['game_over'] = true;
            $_SESSION['data_written'] = false;
            set_user_finsih($state);
            determin_if_bot_won();
            $index = write_completed_game();
        }


        return array('state' => $state, 
                'green_letters' => $_SESSION['gLetters'], 
                'yellow_letters' => $_SESSION['yLetters'], 
                'missing_letters' => $_SESSION['missingLetters'],
                'current_guess_num' => $_SESSION['current_guess_num'] + 1,
                'game_over' => $_SESSION['game_over'],
                'required_letters' => $_SESSION['required_letters'],
                'index' => $index);
    }

    function process_guess($json){
        
        $data = json_decode($json, true);

        if(isset($_SESSION['required_letters']) && is_array($_SESSION['required_letters']))
            for($i=0; $i < count($_SESSION['required_letters']); $i++)
                if(strpos($data['guess'], $_SESSION['required_letters'][$i]) === false)
                    return array('invalid_entry'=> true);


        if (!isset($_SESSION['user_guess_array'][0])) {
            $_SESSION['user_guess_array'][0] = $data['guess'];
            $_SESSION['current_guess_num'] = 1;
        } elseif (!isset($_SESSION['user_guess_array'][1])) {
            $_SESSION['user_guess_array'][1] = $data['guess'];
            $_SESSION['current_guess_num'] = 2;
        } elseif (!isset($_SESSION['user_guess_array'][2])) {
            $_SESSION['user_guess_array'][2] = $data['guess'];
            $_SESSION['current_guess_num'] = 3;
        } elseif (!isset($_SESSION['user_guess_array'][3])) {
            $_SESSION['user_guess_array'][3] = $data['guess'];
            $_SESSION['current_guess_num'] = 4;
        } elseif (!isset($_SESSION['user_guess_array'][4])) {
            $_SESSION['user_guess_array'][4] = $data['guess'];
            $_SESSION['current_guess_num'] = 5;
        } elseif (!isset($_SESSION['user_guess_array'][5])) {
            $_SESSION['user_guess_array'][5] = $data['guess'];
            $_SESSION['current_guess_num'] = 6;
        }

        $colors = check_state($data['guess']);

        error_log('Session state: ' . print_r($_SESSION, true));
        return $colors;
    }

    function set_user_finsih($state){
        if($state != 'ggggg')
            $_SESSION['user_solved'] = false;
        else
            $_SESSION['user_solved'] = true;
    }

    function determin_if_bot_won(){
        if($_SESSION['current_guess_num'] < $_SESSION['current_game']['num_guesses']){
            $_SESSION['user_won'] = true; 
        }
        else if($_SESSION['current_guess_num'] == $_SESSION['current_game']['num_guesses'] && !($_SESSION['current_game']['bot_solved']))
            $_SESSION['user_won'] = true;
        else{
            $_SESSION['user_won'] = false;
        }
    }

    function clear_current_game_data(){

        start_game();
        $_SESSION['current_game'] = array();
        $_SESSION['gLetters'] = array();
        $_SESSION['yLetters'] = array();
        $_SESSION['missingLetters'] = array();
        $_SESSION['data_written'] = null;
    }
?>
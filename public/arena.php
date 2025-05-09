<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Bot Arena</title>
        <link rel="icon" href="./img/flav.png">
        <link rel="stylesheet" href="./css/arena.css">
        <link rel="stylesheet" href="./css/global.css">
    </head>
    <body>

    <?php
        error_reporting(E_ALL); // Report all PHP errors
        ini_set('display_errors', 1); // Display errors in the browser
        ini_set('display_startup_errors', 1); // Display startup errors
        ?>
        <?php
        
            define('SECURE', true);
            include '../private/Database/db_utils.php';
            include '../private/Gameplay/constructors.php';
            include '../private/Gameplay/GameManager.php';

            if (session_status() == PHP_SESSION_NONE) {
                session_start();
            }
            if(isset($_SESSION['data_written']) && $_SESSION['data_written'] == true)
                clear_current_game_data();
            
            if(!isset($_SESSION['current_game']) or $_SESSION['current_game'] == null){
                $_SESSION['current_game'] = get_random_game();
            }

            $gameNumber = $_SESSION['current_game']['id'];
            if(!isset($_SESSION['shareable']))
                $_SESSION['shareable'] = [$gameNumber];
            if(!in_array($gameNumber, $_SESSION['shareable']))
                array_push($_SESSION['shareable'], $gameNumber);
        ?>
        <h3>Game Number: <?php echo $gameNumber; ?></h3>
        <h3>Bot Record:         <?php
            if(!check_single_game_record($gameNumber))
                create_single_game_record($gameNumber);
            $record = get_single_game_record($gameNumber);
            echo $record['BotWins'] . ":" . $record['UserWins'];
        ?></h3>
        <br>
        <div class="answer-area"><?php build_answer_area() ?></div>
        <div class="error-message">
            <p>Must Use Known Letters</p>
        </div>
        <div class="keyboard"><?php build_keyboard() ?></div>
        <?php 
            if($_SERVER['REQUEST_METHOD'] === 'POST'){
                header('Content-Type: application/json');
                $data = file_get_contents('php://input');

                $result = process_guess($data);

                $unexpectedOutput = ob_get_clean();
                
                if(count($result)==1){
                    echo json_encode(['invalid_entry'=>$result['invalid_entry']]);
                    exit();
                }
                
                echo json_encode([  'result' => $result['state'], 
                                    'green_letters' => $result['green_letters'], 
                                    'yellow_letters' => $result['yellow_letters'],
                                    'missing_letters' => $result['missing_letters'],
                                    'current_guess_num' => $result['current_guess_num'],
                                    'game_over'=> $result['game_over'],
                                    'required_letters' => $result['required_letters'],
                                    'id' => $result['index']]);

                exit();
            }
        ?>
        <script src="./js/arenaNew.js"></script>
    </body>
</html>
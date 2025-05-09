<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Beat The Bot</title>
        <link rel="icon" href="./img/flav.png">
        <link rel="stylesheet" href="./css/index.css">
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
            


            $record = get_total_record();


        ?>
        <h1>Beat My Bot</h1>
        <h2>Current Record<br></h2>
        <?php echo "<h2>" . $record['BotWins'] . " : " . $record['UserWins'] . "</h2>"; ?>
        <br><br>

        <form action="./arena.php" method="get">
            <button type="submit" name="challenge" value="random">Challenge Random</button>
        </form>
        <p></p>
        <h3>Welcome to my Statistical Model that Solves the Wordle of the day by The New York Times.
            <br>Green = Correct Letter Correct Spot<br>Yellow = Letter Exists in Wrong Spot<br>Black = Letter Doesn't Exist
        </h3>
        
        <h1 class='recent'>Recent Games</h1>
        <?php build_home_page_recent_games();?>
    </body>
</html>
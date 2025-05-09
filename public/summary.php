<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Summary</title>
        <link rel="icon" href="./img/flav.png">
        <link rel="stylesheet" href="./css/global.css">
        <link rel="stylesheet" href="./css/summary.css">
    </head>
    <body>

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
            
            $index = intval($_GET['index']);

            $startData = get_summary_start($index);
            if($startData['user_won'])
                echo "<h2>Congragulations You Beat The Bot</h2>";
            else
                echo "<h2>Appologies The Bot Beat You</h2>";


                    
            $copyText = build_summary_page($startData['game_number']);
        ?>



        <form action="./index.php" method="get">
            <button type="submit" name="go-home">Return Home</button>
        </form>
        
        <?php
            if(in_array($startData['game_number'], $_SESSION['shareable']))
                echo '<button id="share" data-share-text="'.$copyText.'">Share</button>';
        ?>

        <script src="./js/summarypage.js"></script>
    </body>
</html>
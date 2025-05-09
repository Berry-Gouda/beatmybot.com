<?php

if(!defined('SECURE'))
    die('Access denied');

define('SERVER_NAME', 'localhost');
define('USER_NAME', '');
define('PASSWORD', '');
define('DATABASE', 'BeatMyBot_Data');
define('BOT_TABLE', 'BotData');
define('TOTAL_TABLE', 'TotalRecord');
define('GAME_TABLE', 'WinLoss');
define('USER_TABLE', 'UserData');

class Database
{
    private static $connection = null;

    // Private constructor to prevent multiple instances
    private function __construct() {

    }

    // Public static method to get the instance of the connection
    public static function getConnection()
    {
        if (self::$connection === null) {
            // Initialize the connection if it hasn't been created yet
            self::$connection = new mysqli(SERVER_NAME, USER_NAME, PASSWORD, DATABASE);

            // Check if the connection failed
            if (self::$connection->connect_error) {
                die("Connection failed: " . self::$connection->connect_error);
            }
        }
        return self::$connection;
    }
}
?>

#!/bin/bash

DB_NAME="BeatMyBot_Data"
DB_USER="root"
DB_NEWUSER="srv"
DB_PASS="Killawu123!"
DB_NEWUSERPASS="GGmfEZmidn00b$"
DB_HOST="localhost"
DB_BOT_TABLE="BotData"
DB_USER_TABLE="UserData"
DB_SCORE_TABLE="WinLoss"
DB_TOTAL_TABLE="TotalRecord"
FILE="./db_data.csv"


DB_EXISTS=$(mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" -e "SHOW DATABASES LIKE '$DB_NAME';" | grep "$DB_NAME" > /dev/null; echo "$?")

if [ "$DB_EXISTS" -eq 0 ]; then
    echo "Database '$DB_NAME' exists. Deleting..."
    mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" -e "DROP DATABASE $DB_NAME;"

    if [ $? -eq 0 ]; then
        echo "Database '$DB_NAME' deleted successfully"
    else
        echo "Failed to delete database '$DB_NAME'."
        exit 1
    fi
else
    echo "Database '$DB_NAME' does not exists. Proceeding to creation."
fi


mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" -e "CREATE DATABASE $DB_NAME;"

if [ $? -eq 0 ]; then
    echo "Database '$DB_NAME' created successfully."
else
    echo "Failed to create database '$DB_NAME'."
    exit 1
fi

mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" <<SQL
CREATE USER IF NOT EXISTS '$DN_NEWUSER'@'$DB_HOST' IDENTIFIED BY '$DB_NEWUSERPASS';
GRANT SELECT, INSERT, UPDATE ON $DB_NAME.* TO '$DN_NEWUSER'@'$DB_HOST';
FLUSH PRIVILEGES;
SQL

mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" <<EOF
USE $DB_NAME;
CREATE TABLE $DB_BOT_TABLE(
    id int NOT NULL AUTO_INCREMENT PRIMARY KEY,
    date DATE NOT NULL,
    goal CHAR(5) NOT NULL,
    bot_solved TINYINT(1) NOT NULL,
    num_guesses int NOT NULL,
    guess1 CHAR(5) DEFAULT NULL,
    guess1_state CHAR(5) DEFAULT NULL,
    guess2 CHAR(5) DEFAULT NULL,
    guess2_state CHAR(5) DEFAULT NULL,
    guess3 CHAR(5) DEFAULT NULL,
    guess3_state CHAR(5) DEFAULT NULL,
    guess4 CHAR(5) DEFAULT NULL,
    guess4_state CHAR(5) DEFAULT NULL,
    guess5 CHAR(5) DEFAULT NULL,
    guess5_state CHAR(5) DEFAULT NULL, 
    guess6 CHAR(5) DEFAULT NULL,
    guess6_state CHAR(5) DEFAULT NULL
);

CREATE TABLE $DB_USER_TABLE(
    id int NOT NULL AUTO_INCREMENT PRIMARY KEY,
    game_number int NOT NULL,
    date DATE NOT NULL,
    user_solved TINYINT(1) NOT NULL,
    user_won TINYINT(1) NOT NULL,
    num_guesses int NOT NULL,
    guess1 CHAR(5) DEFAULT NULL,
    guess1_state CHAR(5) DEFAULT NULL,
    guess2 CHAR(5) DEFAULT NULL,
    guess2_state CHAR(5) DEFAULT NULL,
    guess3 CHAR(5) DEFAULT NULL,
    guess3_state CHAR(5) DEFAULT NULL,
    guess4 CHAR(5) DEFAULT NULL,
    guess4_state CHAR(5) DEFAULT NULL,
    guess5 CHAR(5) DEFAULT NULL,
    guess5_state CHAR(5) DEFAULT NULL,
    guess6 CHAR(5) DEFAULT NULL,
    guess6_state CHAR(5) DEFAULT NULL
);

CREATE TABLE $DB_SCORE_TABLE(
    game_number int NOT NULL PRIMARY KEY,
    BotWins int,
    UserWins int
);

CREATE TABLE $DB_TOTAL_TABLE(
    id int NOT NULL PRIMARY KEY,
    BotWins int,
    UserWins int
);

INSERT INTO $DB_TOTAL_TABLE(id, BotWins, UserWins) VALUES(1, 0, 0);

EOF




if [ -f "$FILE" ]; then
    echo "$FILE exists Adding To Database"
else
    echo "$FILE does not exist. Running program to generate Data."
    echo "Checking file path: $FILE"
    python ../Bot/NewDBDataGather.py
fi

while IFS=',' read -r game_number date goal bot_solved num_guesses guess1 guess1_state guess2 guess2_state guess3 guess3_state guess4 guess4_state guess5 guess5_state guess6 guess6_state
do
    # Insert the row into the database
    mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" -e "
    USE $DB_NAME;
    INSERT INTO $DB_BOT_TABLE (date, goal, bot_solved, num_guesses, guess1, guess1_state, guess2, guess2_state, guess3, guess3_state, guess4, guess4_state, guess5, guess5_state, guess6, guess6_state)
    VALUES ('$date', '$goal', $bot_solved, $num_guesses, '$guess1', '$guess1_state', '$guess2', '$guess2_state', '$guess3', '$guess3_state', '$guess4', '$guess4_state', '$guess5', '$guess5_state', '$guess6', '$guess6_state');
    "

    # Check if the insertion was successful
    if [ $? -eq 0 ]; then
        echo "Record inserted successfully: $date"
    else
        echo "Failed to insert record: $date"
    fi



done < "$FILE"
#!/bin/bash

# Define the path to the Python script and the desired time for the cron job
PYTHON_SCRIPT_PATH="/home/BeatMyBot/private/Bot/latest_word.py"
CRON_TIME="0 15 * * *"  # This is for 3:00 PM daily

# Write the cron job to a temporary file
(crontab -l 2>/dev/null; echo "$CRON_TIME /usr/bin/python3 $PYTHON_SCRIPT_PATH") | crontab -

echo "Cron job set to run $PYTHON_SCRIPT_PATH daily at 3:00 PM."
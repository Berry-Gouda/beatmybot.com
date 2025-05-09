from bs4 import BeautifulSoup
from urllib.request import urlopen
import pandas as pd
import ssl
import certifi
from datetime import datetime
from datetime import timedelta
from Bot import WordleBot as wb
import mysql.connector

WEBSITE_URL = 'https://www.stadafa.com/2021/09/every-worlde-word-so-far-updated-daily.html'
column_headers = ['game_number', 'date', 'goal_word', 'bot_solved', 'num_guesses', 'guess1', 'guess1_state', 'guess2', 'guess2_state', 'guess3', 'guess3_state', 'guess4', 'guess4_state', 'guess5', 'guess5_state', 'guess6', 'guess6_state']
listOfMonths = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December']

def get_site_html():
    """"""
    context = ssl.create_default_context(cafile=certifi.where())
    return urlopen(WEBSITE_URL, context=context)

def get_spoiler_info(bs:BeautifulSoup)->str:
   """"""
   return bs.find('div', {'class':'spoiler'}).get_text().lower().strip()

def main():

    html = get_site_html()
    bs = BeautifulSoup(html.read(), 'html.parser')

    goal = get_spoiler_info(bs)

    bot = wb(False)
    s_data = bot.SolveWord(goal)

    if len(s_data[1]) < 6:
        for i in range(6-len(s_data[1])):
            s_data[1].append(None)
    if len(s_data[2]) < 6:
        for i in range(6-len(s_data[2])):
            s_data[2].append(None)

    new_row = { 'date': datetime.today().strftime('%Y-%m-%d'), 'goal_word': goal, 'bot_solved': s_data[3], 
                'num_guesses': s_data[0], 'guess1': s_data[1][0], 'guess1_state': s_data[2][0],'guess2': s_data[1][1], 'guess2_state': s_data[2][1],
                'guess3': s_data[1][2], 'guess3_state': s_data[2][2], 'guess4': s_data[1][3], 'guess4_state': s_data[2][3], 'guess5': s_data[1][4],
                'guess5_state': s_data[2][4], 'guess6': s_data[1][5], 'guess6_state': s_data[2][5]}
    
    connection = mysql.connector.connect(
        host="localhost",
        user="serv",
        password="GGmfEZmidn00b$",
        database="BeatMyBot_Data"
    )

    cursor = connection.cursor()

    sql_insert = """
                    INSERT INTO BotData (date, goal, bot_solved, num_guesses, guess1, guess1_state, 
                                        guess2, guess2_state, guess3, guess3_state, guess4, 
                                        guess4_state, guess5, guess5_state, guess6, guess6_state)
                    VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)"""
    
    data = (new_row['date'], new_row['goal_word'], new_row['bot_solved'], new_row['num_guesses'], new_row['guess1'], new_row['guess1_state'],
            new_row['guess2'], new_row['guess2_state'], new_row['guess3'], new_row['guess3_state'], new_row['guess4'],
            new_row['guess4_state'], new_row['guess5'], new_row['guess5_state'], new_row['guess6'], new_row['guess6_state'])
    
    try:
        cursor.execute(sql_insert, data)
        connection.commit()
        print("Record inserted successfully.")
    except mysql.connector.Error as error:
        print("Failed to insert record into MySQL table: {}".format(error))
    finally:
        # Close the cursor and connection
        cursor.close()
        connection.close()

    with open('./logs.txt', 'a') as file:
        file.write(new_row['date'] + ': Written')



if __name__ == '__main__':
    main()
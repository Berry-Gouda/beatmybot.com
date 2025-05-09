from bs4 import BeautifulSoup
from urllib.request import urlopen
import pandas as pd
import ssl
import certifi
import datetime
from datetime import timedelta
from Bot import WordleBot as wb


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

def get_raw_information(bs:BeautifulSoup)->list:
    """"""
    rtn_data =[]
    data = bs.find_all('p')
    for d in data:
        currentWord = d.get_text()
        if len(currentWord) < 23 or len(currentWord) > 150:
            continue
        if not currentWord[0].isnumeric():
            continue
        if not any(month in currentWord for month in listOfMonths):
            continue

        counter = 0
        final = ''
        for char in currentWord:
            if char == '-':
                counter += 1
                if counter == 2:
                    break
            else:
                final = final + char

        rtn_data.append(final)

    return rtn_data

def get_game_number(data:str)->int:
    """"""
    rtn_val = ''
    for char in data:
        if char.isnumeric():
            rtn_val += char
        else:
            break
    return int(rtn_val) + 1

def get_word(data:str)->str:
    """"""
    for i, char in enumerate(data):
        if char == '.':
            return data[i+1:i+7]


def get_date(data:str)->str:
    """"""
    for i, char in enumerate(data):
        if char == '.':
            return data[i+9:]

def add_words_dataframe(data: list)->pd.DataFrame:
    """"""
    df = pd.DataFrame(columns=column_headers)
    for d in data:
        new_row = { 'game_number': None, 'date': None, 'goal_word': get_word(d).lower().strip(), 'bot_solved': None, 
                    'num_guesses': None, 'guess1': None, 'guess1_state': None,'guess2': None, 'guess2_state': None,
                    'guess3': None, 'guess3_state': None, 'guess4': None, 'guess4_state': None, 'guess5': None,
                    'guess5_state': None, 'guess6': None, 'guess6_state': None}
        df = df._append(new_row, ignore_index=True)
    return df

def add_dates_dataframe(data: pd.DataFrame, date:str)->pd.DataFrame:
    """"""
    data.iloc[0, data.columns.get_loc('date')] = get_date(date)
    data.iloc[0, data.columns.get_loc('date')] = pd.to_datetime(data.iloc[0, data.columns.get_loc('date')], format='%d %B %Y').strftime('%B %d, %Y')
    start_date = pd.to_datetime(data.iloc[0, data.columns.get_loc('date')], format='%B %d, %Y')

    for i in range(1, len(data)):
        start_date += timedelta(days=1)
        data.iloc[i, data.columns.get_loc('date')] = start_date.strftime('%B %d, %Y')
    
    return data

def update_date_format(data:pd.DataFrame)->pd.DataFrame:
    """"""
    data['date'] = pd.to_datetime(data['date'], format='%B %d, %Y')
    data['date'] = data['date'].dt.strftime('%Y-%m-%d')
    return data


def add_solved_data(data:pd.DataFrame, solved:tuple, index:int)->pd.DataFrame:
    """(4, ['balky', 'irate', 'dinar', 'cigar'], ['?????', ?????, ....]  True)"""

    if len(solved[1]) < 6:
        for i in range(6-len(solved[1])):
            solved[1].append(None)
    if len(solved[2]) < 6:
        for i in range(6-len(solved[2])):
            solved[2].append(None)

    data_dict = {   'bot_solved': solved[3], 'num_guesses': solved[0], 'guess1': solved[1][0], 'guess1_state': solved[2][0], 
                    'guess2': solved[1][1], 'guess2_state': solved[2][1], 'guess3': solved[1][2], 'guess3_state': solved[2][2],
                    'guess4': solved[1][3], 'guess4_state': solved[2][3], 'guess5': solved[1][4], 'guess5_state': solved[2][4],
                    'guess6': solved[1][5], 'guess6_state': solved[2][5]}
    data.loc[index, list(data_dict.keys())] = list(data_dict.values())
    return data

def main():
    """"""
    html = get_site_html()
    bs = BeautifulSoup(html.read(), 'html.parser')
    data = pd.DataFrame(columns=column_headers)
    raw_data = get_raw_information(bs)
    raw_data.pop()
    raw_data.reverse()
    data = add_words_dataframe(raw_data)

    new_row = { 'game_number': None, 'date': None, 'goal_word': get_spoiler_info(bs), 'bot_solved': None, 
                'num_guesses': None, 'guess1': None, 'guess1_state': None,'guess2': None, 'guess2_state': None,
                'guess3': None, 'guess3_state': None, 'guess4': None, 'guess4_state': None, 'guess5': None,
                'guess5_state': None, 'guess6': None, 'guess6_state': None}
    
    data = data._append(new_row, ignore_index=True)

    data['game_number'] = range(1, len(data)+1)
    data = add_dates_dataframe(data, raw_data[0])

    bot = wb(False)
    for i in range(len(data)):
        solved_data = bot.SolveWord(data.iloc[i, data.columns.get_loc('goal_word')])
        data = add_solved_data(data, solved_data, i)
        bot.ResetBot()

    data = update_date_format(data)
    
    fileName = "../Database/db_data.csv"

    data.to_csv(fileName, index=False)

    print("File Successfully written")

if __name__ == '__main__':
    main()
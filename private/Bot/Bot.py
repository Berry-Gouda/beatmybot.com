import BotData as bd

class WordleBot():


    def __init__(self, dm: bool):
        self.bGameIsFinished = False
        self.goalWord = ''
        self.yellowLetters = {}
        self.greenLetters = {}       
        self.invalidLetters = []
        self.wordChoiceHistory = []
        self.stateHistory = []

        self.bGameIsFinished = False
        self.currentWordOptions = bd.TotalDictionary
        self.debugMode = dm
        self.currentWord = ''
        self.solution = '?????'
        self.state = '?????'
        self.currentGuess = 0
        self.bGameWon = None

    def SetWord(self, word:str):
        """Sets the goal word to solve a new puzzle"""
        self.goalWord = word

    def SetDebug(self, dm:bool):
        """Sets Debug mode"""
        self.debugMode = dm

    def ResetBot(self):
        """Resets the bot to solve a new puzzle"""
        self.bGameIsFinished = False
        self.goalWord = ''
        self.yellowLetters = {}
        self.greenLetters = {}       
        self.invalidLetters = []
        self.wordChoiceHistory = []
        self.stateHistory = []

        self.bGameIsFinished = False
        self.currentWordOptions = bd.TotalDictionary
        self.debugMode = False
        self.currentWord = ''
        self.solution = '?????'
        self.state = '?????'
        self.currentGuess = 0
        self.bGameWon = None

    def SolveWord(self, goal_word:str):
        """Main Function for solving the Wordle"""
        
        self.goalWord = goal_word
        #Main Loop Where Guess Number gets updated the choose word function gets called and 
        while not self.bGameIsFinished:
            
            #End game if 6 Guesses have been made already, or the correct word has been chosen.
            if self.currentGuess > 5 or not('?' in self.solution):
                self.bGameIsFinished = True
                continue

            if not self.currentGuess == 0:
                self.RebuildWordList()
            
            

            if self.debugMode:
                self.PrintCurrentPossibleWords()

            #Choose The Next Word
            self.wordChoiceHistory.append(self.ChooseWord())
            self.CompareWords(self.wordChoiceHistory[self.currentGuess])

            

            self.stateHistory.append(self.state)
            self.state = '?????'
            self.currentGuess += 1

            if self.debugMode:
                print("Current Guess: " + str(self.currentGuess) + "--Game finished: " + str(self.bGameIsFinished))

        self.FinishGame()

        return self.currentGuess, self.wordChoiceHistory, self.stateHistory, self.bGameWon


    ################################# Word Choosing Functions ##############################

    def ChooseWord(self) -> str:
        """Starting Function for choosing a word."""

        if self.currentGuess == 0:
            return self.ChooseMediumWordWeight()

        if len(self.currentWordOptions) == 1:
            return self.currentWordOptions[0]

        if self.solution == '?????':
            return self.ChooseSimpleWeightWord()
        
        elif len(self.currentWordOptions) < 5:
            return self.ChooseSimpleWeightWord()
        
        else:
            return self.ChooseComplexeWeightWord()
        

    def ChooseMediumWordWeight(self) -> str:
        
        for word in self.currentWordOptions:

            if(self.FiveLetters(word)):
                tempWeight = self.GetWordSimpleWeight(word)
                if tempWeight < 70 and tempWeight > 50:
                    return word


        
    def ChooseSimpleWeightWord(self) -> str:
        """Choose The heaviest word if no green letters"""
        tempWeight = 0
        heaviestWord = ''
        heviestWeight = 0

        for word in self.currentWordOptions:

            if len(self.currentWordOptions) > 100:
                if self.FiveLetters(word):
                    tempWeight = self.GetWordSimpleWeight(word)
                    if tempWeight > heviestWeight:
                        heviestWeight = tempWeight
                        heaviestWord = word

        if heaviestWord == '':
            for word in self.currentWordOptions:
                tempWeight = self.GetWordSimpleWeight(word)
                if tempWeight > heviestWeight:
                    heviestWeight = tempWeight
                    heaviestWord = word


        if self.debugMode:
            print("Simple Weight Word Choosen: " + heaviestWord + "\n")
            input("Press any key to continue")
           

        return heaviestWord
    
    def ChooseComplexeWeightWord(self) -> str:
        """Choose the heaviest word if green letters exist"""
        tempWeight = 0
        heaviestWord = ''
        heaviestWeight = 0

        for word in self.currentWordOptions:
            tempWeight = self.GetWordComplexWeight(word)
            if tempWeight > heaviestWeight:
                heaviestWeight = tempWeight
                heaviestWord = word

        if self.debugMode:
            print("Complexe Weight Word Choosen: " + heaviestWord + "--With Weight: " + str(heaviestWeight))
            input("Press any key to continue")

        return heaviestWord
           
            
    def GetWordSimpleWeight(self, word:str)->int:
        """retrieves the weight of the word just based on total occurances"""

        weight = 0

        for c in word:
            weight += bd.RawLetterWeights[c]
    
        return weight
    

    def GetWordComplexWeight(self, word:str)->int:
        """retrieves the weight of the word just based on propabilities of surrounding green letters"""

        weight = 0
        hash = ''

        #To Get the word Weight we need to just calaculate around the green letters.
        for key in self.greenLetters:
            for i in self.greenLetters[key]:
                if i > 0 and i < len(word)-1:
                    
                    hash = str(key) + str(i) + str(0) + word[i-1]
                    if hash in bd.TotalWeights:
                        weight += bd.TotalWeights[hash]
                        #if self.debugMode:
                            #print(hash + ":" + str(bd.TotalWeights[hash]))

                    hash = str(key) + str(i) + str(1) + word[i+1]
                    if hash in bd.TotalWeights:
                        weight += bd.TotalWeights[hash]
                        #if self.debugMode:
                            #print(hash + ":" + str(bd.TotalWeights[hash]))

                elif i == 0:
                    hash = str(key) + str(i) + str(1) + word[i+1]
                    if hash in bd.TotalWeights:
                        weight += bd.TotalWeights[hash]
                        #if self.debugMode:
                            #print(hash + ":" + str(bd.TotalWeights[hash]))

                elif i == len(word)-1:
                    hash = str(key) + str(i) + str(0) + word[i-1]
                    if hash in bd.TotalWeights:
                        weight += bd.TotalWeights[hash]
                        #if self.debugMode:
                            #print(hash + ":" + str(bd.TotalWeights[hash]))


        return weight

   
    def FiveLetters(self, word:str)->bool:
        """returns true if the word has 5 different letters"""
        diversity = {}

        for c in word:
            if c not in diversity:
                diversity[c] = 1
            else:
                return False

        return True
    
    ################################# Word List Culling Functions ##########################
    
    def RebuildWordList(self):

        tempWordList = []
        #see if a word contains invalid
        #see if a word contains green letters in right locations
        #check if a word contains a yellow letter in a non invalid location.
        for word in self.currentWordOptions:
            
            #returns true if all green letters match with proper locations
            if not self.CheckForGreenLetters(word):
                if self.debugMode:
                    print("We Found Invalid Green Letters in: " + word)
                continue

            #returns true if an invalid letter is found
            if self.CheckForInvalidLetters(word):
                if self.debugMode:
                    print("We Found invalid Letters in: " + word)
                continue
            

            #returns true if all yellow letters match with proper locations.
            if self.CheckForYellowLetters(word):
                if self.debugMode:
                    print("We Found invalid yellow Letters in: " + word)
                continue

            tempWordList.append(word)

        self.currentWordOptions = tempWordList

    def CheckForInvalidLetters(self, word:str)->bool:

        for char in self.invalidLetters:
            if char in word:
                return True

        return False  

    def CheckForGreenLetters(self, word:str)->bool:

        for key in self.greenLetters:
            if key in word:
                for i in self.greenLetters[key]:
                    if word[i] == key:
                        continue
                    else:
                        return False
            else:
                return False

        return True
    
    def CheckForYellowLetters(self, word:str)->bool:

        for key in self.yellowLetters:
            if key in word:
                for i in self.yellowLetters[key]:
                    if word[i] == key:
                        return True
            else:
                return True
        
        return False

    ################################## Word Comparison Functions ###########################

    

    def CompareWords(self, word:str):
        
        humanRtn = '?????'

        for i in range(len(word)):
            #look for invalid letters
            if word[i] not in self.goalWord:
                self.AddInvalidLetter(word[i])
            #look for exact matches
            if word[i] == self.goalWord[i]:
                humanRtn = humanRtn[:i] + 'g' + humanRtn[i+1:]
                self.AddGreenLetter(word[i], i)
            #look for yellow letters
            if word[i] in self.goalWord and word[i] != self.goalWord[i]:
                humanRtn = humanRtn[:i] + 'y' + humanRtn[i+1:]
                self.AddYellowLetter(word[i], i)

        
            
        if '?' not in self.solution:
            return humanRtn
        else:
            return humanRtn
        


    def AddGreenLetter(self, c, i:int):

        if c in self.greenLetters:
            if not(i in self.greenLetters[c]):
                temp = self.greenLetters[c]
                temp.append(i)
                self.greenLetters[c] = temp
        else:
            self.greenLetters[c] = [i]

        self.solution = self.solution[:i]+c+self.solution[i+1:]
        self.state = self.state[:i]+'g'+self.state[i+1:]

        if self.debugMode:
            print("Added a Green Letter: " + c + "\nCurrent Solution Progress: " + self.solution)
            print("Green Letter List: ")
            print(self.greenLetters)
            input("Press any key to continue:")

        if not('?' in self.solution):
            self.bGameIsFinished = True

    def AddYellowLetter(self, c, i:int):
        """Adds the invalid location to a dict of yellow letters Then checks if it should be a green letter"""
        if c in self.yellowLetters:
            if not(i in self.yellowLetters[c]):
                temp = self.yellowLetters[c]
                temp.append(i)
                self.yellowLetters[c] = temp
        else:
            self.yellowLetters[c] = [i]
        self.state = self.state[:i]+'y'+self.state[i+1:]
        if len(self.yellowLetters[c]) == 4:
            self.ConvertYellowLetterToGreen(c)

        if self.debugMode:
            print("Added Yellow Letter: " + c + "--In location: " + str(i))
            print("Yellow Letter List: ")
            print(self.yellowLetters)
            input("Press any key to continue:")

    def AddInvalidLetter(self, c):
        self.invalidLetters.append(c)

        if self.debugMode:
            print("Added invalid Letter: " + c)
            input("Press any key to continue")

    def ConvertYellowLetterToGreen(self, c):
        """Changes a Yellow Letter to green based on only one possible valid location left"""

        if 0 not in self.yellowLetters[c]:
            self.AddGreenLetter(c, 0)
        elif 1 not in self.yellowLetters[c]:
            self.AddGreenLetter(c, 1)        
        elif 2 not in self.yellowLetters[c]:
            self.AddGreenLetter(c, 2)
        elif 3 not in self.yellowLetters[c]:
            self.AddGreenLetter(c, 3)
        elif 4 not in self.yellowLetters[c]:
            self.AddGreenLetter(c, 4)

        self.yellowLetters.pop(c)
        if self.debugMode:
            print("We removed a yellow letter because only one valid location left: " + c)
            input("Press any key to continue:")
        


    ################################## Game Ending Functions ###############################

    def FinishGame(self):
        """Finishes the Game Win or loose"""

        if self.currentGuess > 5:
            self.GameLost()
        
        if not('?' in self.solution):
            self.GameWon()

    def GameLost(self):
        """Function For Ending Game On A Loss"""
        self.bGameWon = False

        if self.debugMode:
            print("We Lost :( \nHere is the word choices I made: ")
            for word in self.wordChoiceHistory:
                print(word + "\t")

    def GameWon(self):
        """Function For Ending Game On A Win"""
        self.bGameWon = True
        
        if self.debugMode:
            print("We Won!!!\nHere is the word choices I made: ")
            for word in self.wordChoiceHistory:
                print(word + "\t")

    





    ################################## Debug Functions #######################################
    def PrintCurrentPossibleWords(self):
        """Prints the Words Currently Available in the Dictionary"""

        print("Current Possible Words List Size:" + str(len(self.currentWordOptions)))
        input("Press any key to continue")
        counter = 0
        for word in self.currentWordOptions:
            print(word + "\t", end="")
            counter += 1
            if(counter % 12 == 0):
                print()
                counter += 1

        print()
        print()

    ################################## Data Output Functions #######################################
    def GetWordChoices(self) -> list[str]:
        """returns the word choices the bot made for a problem"""
        return self.wordChoiceHistory
    
    def GetGuessCount(self) -> int:
        """returns the guess count"""
        return self.currentGuess
    
    def GetGoalWord(self) -> str:
        """returns the goal word"""
        return self.goalWord
    
    def ReturnAllData(self) ->list[str]:
        """returns all the data"""
        rtnVal = []

        rtnVal.append(self.bGameWon)
        rtnVal.append(str(self.GetGuessCount()))

        for word in self.wordChoiceHistory:
            rtnVal.append(word)

        if self.GetGuessCount() < 6:
            for i in range(self.GetGuessCount(), 6):
                rtnVal.append('N/A')

        return rtnVal

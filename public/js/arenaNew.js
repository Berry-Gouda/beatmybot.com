let answerArea;
current_column = 0;
current_row = 0;
grid = [];
currentAnswer = '';
keyBoard = [[]];
let required_letters = [];
let errorDiv;


document.addEventListener('DOMContentLoaded', function() {
    console.log('loaded');
    
    let answerArea = document.getElementsByClassName("letter-square");

    let specButtons = document.getElementsByClassName('backspace');

    errorDiv = document.getElementsByClassName("error-message");

    if (specButtons.length >= 2) {
        specButtons[0].onclick = Submit;
        keyBoard[0] = [specButtons[0], specButtons[1]]
        specButtons[1].onclick = BackSpace;
    }

    keyboardRow = []

    Array.from(document.getElementsByClassName('key-square')).forEach(key => {

        keyboardRow.push(key)

        if(key.innerHTML == 'P'){
            keyBoard.push(keyboardRow);
            keyboardRow = [];
        }
        else if(key.innerHTML == 'L'){
            keyBoard.push(keyboardRow)
            keyboardRow = []
        }
        if(key.id != 'invalid'){
            key.onclick = function() {
                KeyClick(this);
        };}
    });

    keyBoard.push(keyboardRow)

    row = [];
    Array.from(answerArea).forEach(function(e, i){

        if(i % 5 == 0 && i != 0){
            grid.push(row)
            row = []
        }
        row.push(e)
    })
    grid.push(row)
});


function KeyClick(div)
{
    if (current_column > 4)
        return
    grid[current_row][current_column].textContent = div.textContent;
    currentAnswer += div.textContent;
    current_column++;
}

function BackSpace()
{
    if (current_column == 0)
        return
    grid[current_row][current_column-1].textContent = '';
    current_column--;
    currentAnswer = currentAnswer.slice(0, current_column);
    console.log(currentAnswer)

}

function Submit()
{
    HideError();
    if(required_letters.length > 0){
        for(let i = 0; i < required_letters.length; i++)
            if(!currentAnswer.toLowerCase().includes(required_letters[i])){
                console.log("Invalid Letter", required_letters[i])
                DisplayError();
                ErrorDataReset();
                return;
            }
    }
    let data = {'guess_number': current_row, 'guess': currentAnswer.toLowerCase()}
    console.log(data)
    if(current_column > 4){
        fetch('../arena.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data)
        })
        .then(response => response.text())
        .then(text =>{

            return JSON.parse(text);
        })
        .then(data => {

            console.log(data);
            if(data.invalid_entry){
                DisplayError();
                ErrorDataReset();
                return;
            }

            required_letters = data.required_letters;

            ChangeColors(data);

            setTimeout( ()=>{
                if(data.id > 0){
                    url = `../summary.php?index=${data.id}`;
                    window.location.href = url;
                }
            }, 1000)
        })
    }
}

function DisplayError(){
    if(errorDiv)
        errorDiv[0].style.display = "block";
}

function HideError(){
    if(errorDiv)
        errorDiv[0].style.display = "none";
}

function ErrorDataReset(){
    BackSpace();
    BackSpace();
    BackSpace();
    BackSpace();
    BackSpace();
}

function ChangeColors(data)
{
    console.log(data)

    for(let i = 0; i < data.result.length; i++){
        if(data.result[i] == '?'){
            if (grid[current_row][i] instanceof HTMLElement)
                grid[current_row][i].className = 'invalid-letter-square'
            else
                console.log("Not DOM Element")
        }
        else if(data.result[i] == 'y')
            grid[current_row][i].className = 'yellow-letter-square'
        else
            grid[current_row][i].className = 'green-letter-square'
    }


    for(let i = 0; i < data.green_letters.length; i++)
        keyBoard[data.green_letters[i][0]][data.green_letters[i][1]].id = 'green'
    for(let i = 0; i < data.yellow_letters.length; i++)
        keyBoard[data.yellow_letters[i][0]][data.yellow_letters[i][1]].id = 'yellow'
    for(let i = 0; i < data.missing_letters.length; i++){
        keyBoard[data.missing_letters[i][0]][data.missing_letters[i][1]].onclick = null;
        keyBoard[data.missing_letters[i][0]][data.missing_letters[i][1]].id = 'invalid'
    }


    current_row += 1;
    current_column = 0;
    currentAnswer = '';
}
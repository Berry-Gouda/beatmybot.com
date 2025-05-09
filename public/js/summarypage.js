window.onload = function(){
    const button = document.getElementById("share");
    const text = button.getAttribute("data-share-text");

    button.addEventListener("click", function(){
        copyToClipboard(text);
    })
};

function copyToClipboard(text) {
    console.log(text);
    navigator.clipboard.writeText(text).then(() => {
        alert("Copied to clipboard");
    }).catch(err => {
        console.error("Could not copy text: ", err);
    });
}
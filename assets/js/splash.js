function splash() {
    myVar = setTimeout(showPage, 2200);
}

function showPage() {
    document.getElementById("loader").style.display = "none";
    document.getElementById("login").style.display = "flex";
}
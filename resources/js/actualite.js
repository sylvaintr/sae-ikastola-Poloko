import { marked } from "marked";

window.AfficherMarkdownfromBalise = function(idinput, idoutput) {
    const md = document.getElementById(idinput).value;
    document.getElementById(idoutput).innerHTML = marked.parse(md);
}

window.AfficherMarkdownfromTexte = function(texte, idoutput) {
    document.getElementById(idoutput).innerHTML = marked.parse(texte);
}



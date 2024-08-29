window.addEventListener("load", kreirajDogadaje);

var greska = false;
var greskeTekst = "";

function kreirajDogadaje() {
    document.getElementById("btnregistriraj").addEventListener("click", function () {
        provjeraSvihUnosa();
        provjeriDuljinuImena();
        provjeriDuljinuPrezimena();
        provjeriDatum();
        provjeriEmail();
        provjeriLozinke();
             
        if (greska === true && greskeTekst !== "") {
            alert(greskeTekst);
            event.preventDefault();
            greskeTekst = "";
        }
    });
}


function provjeriDuljinuImena() { //1/5 validacija klijent
    var dohvacenoIme = document.getElementById("ime").value; 
    if (dohvacenoIme.length > 25) {
        greska = true;
        greskeTekst += "Duljina imena ne smije biti veća od 25 znakova!\n";
        //alert("Duljina imena ne smije biti veća od 25 znakova!");
    }
}
        
function provjeriDuljinuPrezimena() {//1/5 validacija klijent
    var dohvacenoPrezime = document.getElementById("prez").value;  
    if (dohvacenoPrezime.length > 25) {
        greska = true;
        greskeTekst += "Duljina prezimena ne smije biti veća od 25 znakova!\n"
        //alert("Duljina prezimena ne smije biti veća od 25 znakova!");
    }
}

function provjeraSvihUnosa(){ //2/5 validacija klijent
    var unesenoIme = document.getElementById("ime");
    var unesenoPrezime = document.getElementById("prez");
    var uneseniDatum = document.getElementById("datumRodenja");
    var uneseniEmail = document.getElementById("email");
    var unesenoKorisnickoIme = document.getElementById("korime");
    var unesenaLozinka = document.getElementById("lozinka1");
    var unesenaPonovljenaLozinka = document.getElementById("lozinka2");
    if (jePrazno(unesenoIme)) {
        greska = true;
        greskeTekst += "Potrebno je unijeti ime!\n";
    }
    if (jePrazno(unesenoPrezime)) {
        greska = true;
        greskeTekst += "Potrebno je unijeti prezime!\n";
    }
    if (jePrazno(uneseniDatum)) {
        greska = true;
        greskeTekst += "Potrebno je unijeti datum rođenja!\n";
    }
    if (jePrazno(uneseniEmail)) {
        greska = true;
        greskeTekst += "Potrebno je unijeti email!\n";
    }
    if (jePrazno(unesenoKorisnickoIme)) {
        greska = true;
        greskeTekst += "Potrebno je unijeti korisničko ime!\n";
    }
    if (jePrazno(unesenaLozinka)) {
        greska = true;
        greskeTekst += "Potrebno je unijeti lozinku!\n";
    }
    if (jePrazno(unesenaPonovljenaLozinka)) {
        greska = true;
        greskeTekst += "Potrebno je unijeti ponovljenu lozinku!\n";
    }
}

function jePrazno(tekst) {//2/5 validacija klijent
    if (tekst.value === "") {
        return true;
    } else {
        return false;
    }
}

function provjeriDatum() {//3/5 validacija klijent
    let uneseniDatum = document.getElementById("datumRodenja");
    vrijednostDatuma = uneseniDatum.value;
    if ((vrijednostDatuma.length !== 11) || (vrijednostDatuma[0] < 0) || (vrijednostDatuma[0] > 3) || (vrijednostDatuma[1] < 0) || (vrijednostDatuma[1] > 9) || (vrijednostDatuma[2] !== ".") || (vrijednostDatuma[3] < 0) ||
       (vrijednostDatuma[3] > 1) || (vrijednostDatuma[4] < 0) || (vrijednostDatuma[4] > 9) || (vrijednostDatuma[5] !== ".") || (vrijednostDatuma[6] < 0) || (vrijednostDatuma[6] > 9) || (vrijednostDatuma[7] < 0) || (vrijednostDatuma[7] > 9) ||
       (vrijednostDatuma[8] < 0) || (vrijednostDatuma[8] > 9) || (vrijednostDatuma[9] < 0) || (vrijednostDatuma[9] > 9) || (vrijednostDatuma[10] !== ".") || ((vrijednostDatuma[0] >= 3) && (vrijednostDatuma[1] > 1)) || 
       ((vrijednostDatuma[0] == 0) && (vrijednostDatuma[1] == 0)) || ((vrijednostDatuma[3] == 0) && (vrijednostDatuma[4] == 0)) || (vrijednostDatuma[6] == 0) || ((vrijednostDatuma[3] == 1) && (vrijednostDatuma[4] > 2))) {
        greska = true;
        greskeTekst += "Datum je krivog formata!\n";
    }
}

function provjeriLozinke(){//4/5 validacija klijent
    let unesenaLozinka = document.getElementById("lozinka1").value;
    let ponovljenaLozinka = document.getElementById("lozinka2").value;
    if (unesenaLozinka !== ponovljenaLozinka ) {
        greska = true;
        greskeTekst += "Lozinke se ne podudaraju!\n";
    }
}

function provjeriEmail(){//5/5 validacija klijent
    let uneseniEmail = document.getElementById("email").value;
    let zadnjeDvijeZnamenke = uneseniEmail.length - 3; // npr. .hr
    let zadnjeTriZnamenke = uneseniEmail.length - 4; // npr. .com
    var dobarEmail = false;
    var zapamtiEt = false;
    var zapamtiTocku = false;

    if (uneseniEmail[zadnjeDvijeZnamenke] === "." || uneseniEmail[zadnjeTriZnamenke] === ".") {
        dobarEmail = true;
    } else {
        dobarEmail = false;
    }

    for (var i = 0; i < uneseniEmail.length; i++) {
        if (uneseniEmail[i] === "@") {
            zapamtiEt = true;
        }
        if (uneseniEmail[i] === ".") {
            zapamtiTocku = true;
        }
    }

    if (zapamtiEt) {
        dobarEmail = true;
    }else{
        dobarEmail = false;
    }
    
    if (zapamtiTocku) {
         dobarEmail = true;
    }else{
         dobarEmail = false;
    }
    
    if (zapamtiEt === false  && zapamtiTocku === true) { //ima tocke nema @
        dobarEmail = false;
    }
    
    if (!dobarEmail) {
        greska = true;
        greskeTekst += "Email nije ispravan!\n";
    }
}
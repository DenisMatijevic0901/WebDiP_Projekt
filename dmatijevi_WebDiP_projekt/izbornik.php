<?php
/*
  + Neregistrirani korisnik može pristupiti stranicama: prijava.php, registracija.php, index.php
  + Registrirani korisnik može pristupiti svemu kao i neregistrirani korisnik plus: popis.php
  + Voditelj može pristupiti svemu kao i registrirani korisnik plus:multimedija.php
  + Administrator može pristupiti svim stranicama.
 */

/* NEREGISTRIRANI */
echo "<nav><ul>
        <li><a href=\"$putanja/index.php\">Početna stranica</a></li>
        <li><a href=\"$putanja/obrasci/registracija.php\">Registracija</a></li>
        <li><a href=\"$putanja/rang_lista.php\">Rang lista</a></li>
        <li><a href=\"$putanja/galerija.php\">Galerija</a></li>
        <li><a href=\"$putanja/o_autoru.html\">O autoru</a></li>
        <li><a href=\"$putanja/dokumentacija.html\">Dokumentacija</a></li>     
    ";

/* REGISTRIRANI */
if (isset($_SESSION["uloga"]) && $_SESSION["uloga"] >= 2) {
    echo "<li><a href=\"$putanja/prijava_utrke.php\">Kreiranje/Pregledavanje/Ažuriranje prijava</a></li>";
    echo "<li><a href=\"$putanja/popis_buducih_utrka.php\">Popis budućih utrka</a></li>";
    echo "<li><a href=\"$putanja/popis_etapa_prijavljenih_utrka.php\">Popis etapa prijavljenih utrka</a></li>";
    
}
/* MODERATOR */
if (isset($_SESSION["uloga"]) && $_SESSION["uloga"] >= 3) {
    echo "<li><a href=\"$putanja/etape.php\">Kreiranje/Pregledavanje/Ažuriranje etapa</a></li>";
    echo "<li><a href=\"$putanja/popis_utrka_i_zakljucavanje.php\">Popis utrka i zaključavanje utrka</a></li>";
    echo "<li><a href=\"$putanja/popis_prijavljenih_korisnika_info.php\">Popis prijavljenih korisnika info</a></li>";
    echo "<li><a href=\"$putanja/evidentiranje_vremena.php\">Evidentiranje vremena korisnika</a></li>";     
}

/* ADMINISTRATOR */
if (isset($_SESSION["uloga"]) && $_SESSION["uloga"] === "4") {
    echo "<li><a href=\"$putanja/drzave.php\">Kreiranje/Pregledavanje/Ažuriranje država</a></li>";
    echo "<li><a href=\"$putanja/utrke.php\">Kreiranje/Pregledavanje/Ažuriranje utrka</a></li>";
    echo "<li><a href=\"$putanja/pregled_blokiranih_korisnika.php\">Pregled blokiranih korisnika</a></li>";
    
}

/*PRIJAVA*/
if (!isset($_SESSION["uloga"])) {  
    echo "<li><a href=\"$putanja/obrasci/prijava.php\">Prijava</a></li>";    
}

/* ODJAVA */
if (isset($_SESSION["uloga"])) {  
    echo "<li><a href=\"$putanja/index.php?ukloni=true\">Odjava</a></li>";    
}

 if (isset($_GET['ukloni'])) {
    ukloniKolacic();
}

function ukloniKolacic() {
    global $putanja;
    unset($_COOKIE['autenticiran']);
    setcookie('autenticiran', null, -1, '/');

    Sesija::obrisiSesiju();
    header("Location: $putanja/index.php");
}

/*PRIJAVLJENI KORISNIK*/
if (isset($_COOKIE["autenticiran"])) {
    if ($_SESSION["uloga"] == 1) {
        $ulogaKorisnika = "neregistrirani";
    }
    if ($_SESSION["uloga"] == 2) {
        $ulogaKorisnika = "registrirani";
    }
    if ($_SESSION["uloga"] == 3) {
        $ulogaKorisnika = "moderator";
    }
    if ($_SESSION["uloga"] == 4) {
        $ulogaKorisnika = "administrator";
    }
    echo "Prijavljeni korisnik je " . $_COOKIE["autenticiran"]. "<br>";
    echo "Uloga korisnika je " . $ulogaKorisnika;
}

echo "</ul></nav>";

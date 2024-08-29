<?php
error_reporting(E_ALL ^ E_NOTICE);
$direktorij = dirname(getcwd());
$putanja = dirname($_SERVER['REQUEST_URI'],2);

include '../zaglavlje.php';

provjeriProtokol();

function provjeriProtokol() {
    if ($_SERVER['HTTPS'] != "on") {
        $protokol = "https://";
        $host = $_SERVER["HTTP_HOST"];
        $putanja = $_SERVER["REQUEST_URI"];
        $putanjaSecured = $protokol . $host . $putanja;
        header("Location: $putanjaSecured");
    }
}

//popuni prijavu ako postoji kolacic pamtiKorisnika
$zapamtiKorime = isset($_COOKIE['pamtiKorisnika']) ? $_COOKIE['pamtiKorisnika'] : "";

if (isset($_POST['btnPrijaviSe'])) {

    $baza = new Baza();
    $baza->spojiDB();

    $korime = $_POST['korime'];
    $lozinka = $_POST['lozinka'];

    $upit = "SELECT * FROM `korisnik` WHERE "
            . "`korisnicko_ime`='{$korime}'";

    $rezultat = $baza->selectDB($upit);
    $autenticiran = false;

    $red = mysqli_fetch_array($rezultat);
    $uloga = $red["tip_korisnika"];

    if ($korime != "" || $lozinka != "") {
        $mogucaPrijava = true;
    }
    if ($mogucaPrijava) {
        if ($korime != $red['korisnicko_ime']) {
            $poruka = "Korisnik nije registriran!";
        } else {
            if ($red['status'] != 1 && $red['korisnicko_ime'] == $korime) {
                if ($red['lozinka'] == $lozinka) {
                    if ($red['aktiviran'] == 1) {
                        $poruka = "Uspješna prijava!"; //resetiranje na 0
                        $sqlUpit = "UPDATE korisnik SET broj_neuspjesnih_prijava = 0 WHERE korisnik_id = $red[0]";
                        $rezultat = $baza->selectDB($sqlUpit);
                        // kreiranje kolačića prijavljenog korisnika
                        setcookie("autenticiran", $korime, false, '/', false);

                        //popuni prijavu ako postoji kolacic pamtiKorisnika
                        if (isset($_POST['zapamtiMe'])) {
                            if ($korime != $_COOKIE['pamtiKorisnika']) {
                                setcookie("pamtiKorisnika", $korime, false, '/');
                            }
                        } else if (!isset($_POST['zapamtiMe']) && ($korime == $_COOKIE['pamtiKorisnika'])) {
                            unset($_COOKIE['pamtiKorisnika']);
                            setcookie('pamtiKorisnika', null, -1, '/');
                        } else {
                            unset($_COOKIE['pamtiKorisnika']);
                            setcookie('pamtiKorisnika', null, -1, '/');
                        }

                        // kreiranje sesije
                        Sesija::kreirajKorisnika($korime, $uloga);                      

                        //preusmjeravanje na početnu stranicu                                           
                        header("Location: ../index.php");
                        exit();
                    }else{
                        $poruka = "Niste aktivirali korisnički račun!";
                    }
                } else {
                    $povecajPokusaj = $red['broj_neuspjesnih_prijava'] + 1;
                    $preostaloPokusaja = 3 - $red['broj_neuspjesnih_prijava'] - 1;
                    $poruka = "Kriva lozinka, preostalo vam je $preostaloPokusaja pokušaja";
                    $sqlUpit = "UPDATE korisnik SET broj_neuspjesnih_prijava = $povecajPokusaj WHERE korisnik_id = $red[0]";
                    $rezultat = $baza->selectDB($sqlUpit);

                    $sqlUpitProvjeraBlokiran = "SELECT * FROM `korisnik` WHERE `korisnicko_ime`='{$korime}'";
                    $rezultatProvjeraBlokiran = $baza->selectDB($sqlUpitProvjeraBlokiran);
                    $redProvjeraBlokiran = mysqli_fetch_array($rezultatProvjeraBlokiran);

                    if ($redProvjeraBlokiran['broj_neuspjesnih_prijava'] == 3) {
                        $poruka = "Pogriješili ste lozinku 3 ili više puta, blokirani ste!";
                        $sqlUpitUpdateBlokiran = "UPDATE korisnik SET status = 1 WHERE korisnik_id = $red[0]";
                        $rezultatUpdateBlokiran = $baza->selectDB($sqlUpitUpdateBlokiran);
                    }
                }
            } else {
                $poruka = "Nije moguća prijava, korisnik je blokiran!";
            }
        }
    } else {
        if ($korime == "") {
            $poruka = "Niste unijeli korisničko ime!";
        }
    }

    $baza->zatvoriDB();
}

if (isset($_POST['btnZaboravljenaLozinka'])) {
    if ($_POST['korime'] != "") {

        $baza = new Baza();
        $baza->spojiDB();

        $korime = $_POST['korime'];

        $upit = "SELECT * FROM `korisnik` WHERE `korisnicko_ime`='{$korime}'";

        $rezultat = $baza->selectDB($upit);
        $red = mysqli_fetch_array($rezultat);

        GenerirajLozinku();
        $upitUpdateLozinke = "UPDATE korisnik SET lozinka = '$lozinka', lozinka_sha256 = '$lozinkaSha256' WHERE korisnik_id = $red[0]";
        $rezultatUpdateLozinke = $baza->selectDB($upitUpdateLozinke);
        
        //slanje maila
        $primaMail = $red['email'];
        $saljeMail = "From: dmatijevi@foi.hr";
        $mailSubject = "Zaboravljena lozinka";
        $mailPoruka = "Generirana Vam je nova lozinka: $lozinka";
        
        mail($primaMail, $mailSubject, $mailPoruka, $saljeMail);
        $poruka = "Nova lozinka pristigla Vam je na mail!";
        $baza->zatvoriDB();
    }else{
        $poruka = "Niste unijeli korisničko ime!";
    }
}

function GenerirajLozinku(){
    $znakovi = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";
    global $lozinka;
    global $lozinkaSha256;
    $lozinka = "";
    
    for ($i = 0; $i < 8; $i++){
        $lozinka .= $znakovi[rand(0, strlen($znakovi)-1)];
    }         
    $lozinkaSha256 = hash('sha256', $lozinka);  
}

?>
<!DOCTYPE html>
<html lang="hr">
    <head>
        <title>Prijava - Trčanje</title>
        <meta charset="utf-8">
        <meta name="author" content="Denis Matijević">
        <meta name="description" content="24.8.2022.">
        <meta name="viewport" content="width=device-width, minimum-scale=1, maximum-scale=1">
        <link href="../css/dmatijevi.css" rel="stylesheet" type="text/css">
    </head>
    <body>
        <header>

            <div>
                <input id="toggle" type="checkbox"></input>

                <label for="toggle" class="hamburger">
                    <div class="top-bun"></div>
                    <div class="meat"></div>
                    <div class="bottom-bun"></div>
                </label>

                <div class="nav">
                    <div class="nav-wrapper">
                        <nav>
                            <?php
                            include '../izbornik.php';
                            ?>
                        </nav>
                    </div>
                </div>

            </div>  
            
            <a href="../index.php">
                <img class="logo" src="../materijali/logo.png" alt="Logo trčanje" width="140" height="120">
            </a>
            
            <h1 class="naslovStranice">PRIJAVA</h1>
            <?php 
            echo "<p style = 'color:red; font-weight:bold; text-align:center;'>$poruka</p>";
            ?>
        </header>         
        <hr>
        <section>
            <form novalidate class="form" method="post" id="formPrijava" name="formPrijava" action="prijava.php">
                <label style="margin-bottom: 5px;" for="korime">Korisničko ime: </label>
                <input class="tbKorimePrijava" type="text" id="korime" name="korime" value= "<?php print($zapamtiKorime); ?>" size="30" maxlength="30" placeholder="korisničko ime" autofocus="autofocus" required="required"><br>

                <label style="margin-bottom: 5px;" for="lozinka">Lozinka: </label>
                <input class="tbLozinkaPrijava" type="password" id="lozinka" name="lozinka" size="30" maxlength="30" placeholder="lozinka" required="required"><br>
                
                <label style="margin-bottom: 5px;" for="zapamtiMe">Zapamti me</label>
                <input style="margin-bottom: 5px; margin-left:-83px;" type="checkbox" id="zapamtiMe" name="zapamtiMe" value="1"><br>     

                <input form="formPrijava" name="btnZaboravljenaLozinka" type="submit" class="btnZaboravljenaLozinka" value=" Zaboravljena lozinka? ">          
            </form>
            <div style="text-align:center;">
                <input form="formPrijava" name="btnPrijaviSe" type="submit" class="submit" value=" Prijavi se ">
            </div>
        </section>
        <footer style="position: fixed; bottom:0">
            <address>Kontakt: 
                <a href="mailto:dmatijevi@foi.hr" style="color:black; text-decoration: none;">
                    Denis Matijević</a></address>
            <p>&copy; 2022 D. Matijević</p>         
                <img style="position:relative; top:-7px;" src="../materijali/HTML5.png" alt="HTML" height="62" width="62">         
                <img style="position:relative; top:0px;" src="../materijali/CSS3.png" alt="CSS3" height="75" width="75">
        </footer>
        
    </body>
</html>

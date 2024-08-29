<?php
error_reporting(E_ALL ^ E_NOTICE);
$direktorij = dirname(getcwd());
$putanja = dirname($_SERVER['REQUEST_URI'], 2);

include '../zaglavlje.php';

global $mogucaRegistracija;
if (isset($_GET['btnRegistrirajSe']) && $_GET['g-recaptcha-response'] != "") {
    $secretKey = '6Lf4y3QgAAAAABjHFbhrLdEcAgnOnsVXe7sm-lfb';
    $verifikacijaOdgovora = file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret=' . $secretKey . '&response=' . $_GET['g-recaptcha-response']);
    $podaciOdgovora = json_decode($verifikacijaOdgovora);
    if ($podaciOdgovora->success) {
        $mogucaRegistracija = true;
        $porukaRecaptcha = "Potvrđena reCAPTCHA!";
    } else {
        $mogucaRegistracija = false;
        $porukaRecaptcha = "Greška prilikom potvrde reCAPTCHE!";
    }
}
if (isset($_GET['btnRegistrirajSe']) && $_GET['g-recaptcha-response'] == "" && $_GET['ime']=="") {
    $porukaRecaptcha = "Potrebno je potvrditi reCAPTCHA te unijeti sve podatke ispravno!";
}

if (isset($_GET['btnRegistrirajSe']) && $mogucaRegistracija == true) {
    $porukaRecaptcha = "Potvrđena reCAPTCHA, potrebno je unijeti sve podatke ispravno!";
}else{
    $porukaRecaptcha = "Potrebno je potvrditi reCAPTCHA te unijeti sve podatke ispravno!";
}



if (isset($_GET['btnRegistrirajSe'])) {
    if ($_GET['korime'] != "") {
        global $greska;
        $baza = new Baza();
        $baza->spojiDB();

        $korime = $_GET['korime'];

        $upitProvjera = "SELECT * FROM `korisnik` WHERE `korisnicko_ime`='{$korime}'";

        $rezultatProvjera = $baza->selectDB($upitProvjera);
        $red = mysqli_fetch_array($rezultatProvjera);
        
        if ($korime != $red[5]) {
            $porukaProvjera = "Korisnik ne postoji u bazi, registracija s unesenim korisničkim imenom je moguća!";
            $greska = false;
            if ($_GET['g-recaptcha-response'] == "") {
                $porukaProvjera = "Korisnik ne postoji u bazi, registracija s unesenim korisničkim imenom je moguća, reCAPTCHA nije potvrđena!";
            }
        }else{
            $porukaProvjera = "Korisnik već postoji u bazi, registracija s unesenim korisničkim imenom nije moguća!";
            $greska = true;
        }
    }else{
        $greska = true;
    }
}


if (isset($_GET['btnRegistrirajSe']) && $greska != true && $mogucaRegistracija == true) {
    $greska = false;

    function provjeraSvihUnosa() { //1/5 validacija posluzitelj
        global $greska;
        global $greskaIme;
        global $greskaPrezime;
        global $greskaDatumRodenja;
        global $greskaEmail;
        global $greskaKorisnickoIme;
        global $greskaLozinka;
        global $greskaPonovljenaLozinka;
        global $greskaDatumGodine;
        
        global $bojajIme;
        global $bojajPrezime;
        global $bojajDatumRodenja;
        global $bojajEmail;
        global $bojajKorisnickoIme;
        global $bojajLozinka;
        global $bojajPonovljenaLozinka;
        
        global $bojajImeDuljina;
        global $bojajPrezimeDuljina;
        global $bojajLozinke;
        global $bojajNeispravanEmail;
        global $bojajDatumGodine;
        
        $unesenoIme = $_GET['ime'];
        $unesenoPrezime = $_GET['prez'];
        $uneseniDatum = $_GET['datumRodenja'];
        $uneseniEmail = $_GET['email'];
        $unesenoKorisnickoIme = $_GET['korime'];
        $unesenaLozinka = $_GET['lozinka1'];
        $unesenaPonovljenaLozinka = $_GET['lozinka2'];

        if (jePrazno($unesenoIme)) {
            $greska = true;
            $greskaIme = "Potrebno je unijeti ime!";
            $bojajIme = true;
        }
        if (jePrazno($unesenoPrezime)) {
            $greska = true;
            $greskaPrezime = "Potrebno je unijeti prezime!";
            $bojajPrezime = true;
        }
        if (jePrazno($uneseniDatum)) {
            $greska = true;
            $greskaDatumRodenja = "Potrebno je unijeti datum rođenja!";
            $bojajDatumRodenja = true;
        }
        if (jePrazno($uneseniEmail)) {
            $greska = true;
            $greskaEmail = "Potrebno je unijeti email!";
            $bojajEmail = true;
        }
        if (jePrazno($unesenoKorisnickoIme)) {
            $greska = true;
            $greskaKorisnickoIme = "Potrebno je unijeti korisničko ime!";
            $bojajKorisnickoIme = true;
        }
        if (jePrazno($unesenaLozinka)) {
            $greska = true;
            $greskaLozinka = "Potrebno je unijeti lozinku!";
            $bojajLozinka = true;
        }
        if (jePrazno($unesenaPonovljenaLozinka)) {
            $greska = true;
            $greskaPonovljenaLozinka = "Potrebno je unijeti ponovljenu lozinku!";
            $bojajPonovljenaLozinka = true;
        }
    }

    function jePrazno($tekst) {//1/5 validacija posluzitelj
        if ($tekst == "") {
            return true;
        } else {
            return false;
        }
    }

    provjeraSvihUnosa();

    function provjeriDuljinuImena() { //2/5 validacija posluzitelj
        global $greska;
        global $greskaImeDuljina;
        global $bojajImeDuljina;
        $dohvacenoIme = $_GET['ime'];
        if (strlen($dohvacenoIme) > 25) {
            $greska = true;
            $greskaImeDuljina= "Duljina imena ne smije biti veća od 25 znakova!";
            $bojajImeDuljina = true;
        }
    }

    provjeriDuljinuImena();

    function provjeriDuljinuPrezimena() {//2/5 validacija posluzitelj
        global $greska;
        global $greskaPrezimeDuljina;
        global $bojajPrezimeDuljina;
        $dohvacenoPrezime = $_GET['prez'];
        if (strlen($dohvacenoPrezime) > 25) {
            $greska = true;
            $greskaPrezimeDuljina = "Duljina prezimena ne smije biti veća od 25 znakova!";
            $bojajPrezimeDuljina = true;
        }
    }

    provjeriDuljinuPrezimena();

    function provjeriLozinke() {//3/5 validacija posluzitelj
        global $greska;
        global $greskaLozinkePodudaranje;
        global $bojajLozinke;
        $unesenaLozinka = $_GET['lozinka1'];
        $ponovljenaLozinka = $_GET['lozinka2'];
        if ($unesenaLozinka != $ponovljenaLozinka) {
            $greska = true;
            $greskaLozinkePodudaranje = "Lozinke se ne podudaraju!";
            $bojajLozinke = true;
        }
    }

    provjeriLozinke();

    function provjeriEmail() {//4/5 validacija posluzitelj
        $uneseniEmail = $_GET['email'];
        $zadnjeDvijeZnamenke = strlen($uneseniEmail) - 3; // npr. .hr
        $zadnjeTriZnamenke = strlen($uneseniEmail) - 4; // npr. .com
        $dobarEmail = false;
        $zapamtiEt = false;
        $zapamtiTocku = false;
        global $greskaEmailNeispravan;
        global $bojajNeispravanEmail;

        if ($uneseniEmail[$zadnjeDvijeZnamenke] == "." || $uneseniEmail[$zadnjeTriZnamenke] == ".") {
            $dobarEmail = true;
        } else {
            $dobarEmail = false;
        }

        for ($i = 0; $i < strlen($uneseniEmail); $i++) {
            if ($uneseniEmail[$i] == "@") {
                $zapamtiEt = true;
            }
            if ($uneseniEmail[$i] == ".") {
                $zapamtiTocku = true;
            }
        }

        if ($zapamtiEt) {
            $dobarEmail = true;
        } else {
            $dobarEmail = false;
        }

        if ($zapamtiTocku) {
            $dobarEmail = true;
        } else {
            $dobarEmail = false;
        }

        if ($zapamtiEt == false && $zapamtiTocku == true) { //ima tocke, a nema @
            $dobarEmail = false;
        }

        if (!$dobarEmail) {
            global $greska;
            $greska = true;
            $greskaEmailNeispravan= "Email nije ispravan!";
            $bojajNeispravanEmail = true;
        }
    }

    provjeriEmail();
    
    
     function provjeriGodine() { //5/5 validacija posluzitelj
        global $greska;
        global $bojajDatumGodine;
        global $greskaDatumGodine;
        //provjera 18 godina
        $datumRodenja = $_GET['datumRodenja'];
        $danasnjiDatum = date("d.m.Y.");
        $krajnjiDatum = date("d.m.Y.", strtotime(date("d.m.Y.", strtotime($danasnjiDatum)) . " - 18 year"));
        $datumRodenjaTimestamp = strtotime($datumRodenja);
        $krajnjiDatumTimestamp = strtotime($krajnjiDatum);

        if ($datumRodenjaTimestamp < $krajnjiDatumTimestamp) {
            echo "";
        } else {
            $greskaDatumGodine = "Za registraciju je potrebno biti stariji od 18 godina!";
            $greska = true;
            $bojajDatumGodine = true;
        }
    }
    
    provjeriGodine();
    
    
    if ($greska === false) {
        global $porukaRecaptcha;
        $porukaRecaptcha = "";
        
        $baza = new Baza();
        $baza->spojiDB();
        
        $unesenoIme = $_GET['ime'];
        $unesenoPrezime = $_GET['prez'];
        $uneseniDatum = $_GET['datumRodenja'];
        $datumFormatirano =  date("Y-m-d", strtotime($uneseniDatum));
        
        $uneseniEmail = $_GET['email'];
        $unesenoKorisnickoIme = $_GET['korime'];
        $unesenaLozinka = $_GET['lozinka1'];
        $sha256lozinka = hash('sha256', $unesenaLozinka);
        $vrijeme_registriranja = date('Y-m-d H:i:s');

        function GenerirajAktivacijskiKod() {
            $znakovi = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";
            global $aktivacijskiKod;
            $aktivacijskiKod = "";

            for ($i = 0; $i < 10; $i++) {
                $aktivacijskiKod .= $znakovi[rand(0, strlen($znakovi) - 1)];
            }          
        }
        GenerirajAktivacijskiKod();
        
        //slanje maila
        $poveznica = "https://barka.foi.hr/WebDiP/2021_projekti/WebDiP2021x071/aktivacija.php";
        $primaMail = $uneseniEmail;
        $saljeMail = "From: dmatijevi@foi.hr";
        $mailSubject = "Aktivacija racuna";
        $mailPoruka = "Vas aktivacijski kod za potvrdu registracije je : $aktivacijskiKod , poveznica na kojoj mozete izvrsiti aktivaciju je: $poveznica";
        
        mail($primaMail, $mailSubject, $mailPoruka, $saljeMail);
        
        $sqlUpit = "INSERT INTO korisnik (ime, prezime, datum_rodenja, email, korisnicko_ime, lozinka, lozinka_sha256, aktivacijski_kod, vrijeme_registriranja, tip_korisnika)"
                . "VALUES ('$unesenoIme', '$unesenoPrezime', '$datumFormatirano', '$uneseniEmail', '$unesenoKorisnickoIme','$unesenaLozinka','$sha256lozinka','$aktivacijskiKod','$vrijeme_registriranja',2)";
        
        $rezultat = $baza->insertDB($sqlUpit);
        
        if (!$rezultat) {
            $porukaTrcanje = "Greška prilikom registracije.";
            
        }else{
            $porukaTrcanje = "Potvrdite aktivaciju korisničkog računa.";
            header("Location: ../aktivacija.php?aktemail=$uneseniEmail");
        }
        
        $baza->zatvoriDB();
    }
}

?>
<!DOCTYPE html>
<html lang="hr">
    <head>
        <title>Registracija - Trčanje</title>
        <meta charset="utf-8">
        <meta name="author" content="Denis Matijević">
        <meta name="description" content="24.8.2022.">
        <meta name="viewport" content="width=device-width, minimum-scale=1, maximum-scale=1">
        <link href="../css/dmatijevi.css" rel="stylesheet" type="text/css">
        <script type="text/javascript" src="../javascript/dmatijevi.js"></script>
        <script src="https://www.google.com/recaptcha/api.js" async defer></script>

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

            <h1 class="naslovStranice">REGISTRACIJA</h1>
            <?php
            global $porukaProvjera;
            global $greskaIme;
            global $greskaPrezime;
            global $greskaDatumRodenja;
            global $greskaEmail;
            global $greskaKorisnickoIme;
            global $greskaLozinka;
            global $greskaPonovljenaLozinka;
            global $porukaRecaptcha;

            global $greskaImeDuljina;
            global $greskaPrezimeDuljina;
            global $greskaLozinkePodudaranje;
            global $greskaEmailNeispravan;
            global $greskaDatumGodine;
            
            echo "<p style = 'color:red; font-weight:bold; text-align:center;'>$porukaRecaptcha</p>";
            echo "<p style = 'color:red; font-weight:bold; text-align:center;'>$porukaProvjera</p>";
            echo "<p style = 'color:red; font-weight:bold; text-align:center;'>$greskaIme</p>";
            echo "<p style = 'color:red; font-weight:bold; text-align:center;'>$greskaPrezime</p>";
            echo "<p style = 'color:red; font-weight:bold; text-align:center;'>$greskaDatumRodenja</p>";
            echo "<p style = 'color:red; font-weight:bold; text-align:center;'>$greskaEmail</p>";
            echo "<p style = 'color:red; font-weight:bold; text-align:center;'>$greskaKorisnickoIme</p>";
            echo "<p style = 'color:red; font-weight:bold; text-align:center;'>$greskaLozinka</p>";
            echo "<p style = 'color:red; font-weight:bold; text-align:center;'>$greskaPonovljenaLozinka</p>";
            
            echo "<p style = 'color:red; font-weight:bold; text-align:center;'>$greskaImeDuljina</p>";
            echo "<p style = 'color:red; font-weight:bold; text-align:center;'>$greskaPrezimeDuljina</p>";
            echo "<p style = 'color:red; font-weight:bold; text-align:center;'>$greskaLozinkePodudaranje</p>";
            echo "<p style = 'color:red; font-weight:bold; text-align:center;'>$greskaEmailNeispravan</p>";
            echo "<p style = 'color:red; font-weight:bold; text-align:center;'>$greskaDatumGodine</p>";
            ?>
        </header>         
        <hr>
        <section>

            <form novalidate class="form" method="get" name="formRegistracija" id="formRegistracija" action="registracija.php">
                <label style="margin-bottom: 5px;" id="labelime" for="ime">Ime: </label>
                <input <?php 
                if ($bojajIme || $bojajImeDuljina) {
                    echo "class='crvenaBoja'";
                }
                ?> style="margin-bottom: 5px; margin-left: 112px;" type="text" id="ime" name="ime" size="35" placeholder="ime" required="required" autofocus="autofocus"><br>

                <label style="margin-bottom: 5px;" for="prez">Prezime: </label>
                <input <?php 
                if ($bojajPrezime || $bojajPrezimeDuljina) {
                    echo "class='crvenaBoja'";
                }
                ?> style="margin-bottom: 5px; margin-left: 80px;" type="text" id="prez" name="prez" size="35" placeholder="prezime" required="required"><br>

                <label style="margin-bottom: 5px;" for="danRod">Datum rođenja: </label>
                <input style="margin-bottom: 5px; margin-left: 34px;" type="text" id="datumRodenja" name="datumRodenja" size="35" required="required" placeholder="dd.mm.gggg."><br>

                <label style="margin-bottom: 5px;" for="email">Email adresa: </label>
                <input <?php 
                if ($bojajEmail || $bojajNeispravanEmail) {
                    echo "class='crvenaBoja'";
                }
                ?> style="margin-bottom: 5px; margin-left: 46px;" type="email" id="email" name="email" size="35" maxlength="35" placeholder="ldap@foi.hr" required="required"><br>

                <label style="margin-bottom: 5px;" for="korime">Korisničko ime: </label>
                <input <?php 
                if ($bojajKorisnickoIme) {
                    echo "class='crvenaBoja'";
                }
                ?> style="margin-bottom: 5px; margin-left: 35px;" type="text" id="korime" name="korime" size="35" maxlength="25" placeholder="korisničko ime" required="required"><br>

                <label style="margin-bottom: 5px;" for="lozinka1">Lozinka: </label>
                <input <?php 
                if ($bojajLozinka || $bojajLozinke) {
                    echo "class='crvenaBoja'";
                }
                ?> style="margin-bottom: 5px; margin-left: 84px;" type="password" id="lozinka1" name="lozinka1" size="35" maxlength="50" placeholder="lozinka" required="required"><br>

                <label style="margin-bottom: 5px;" for="lozinka2">Ponovi lozinku: </label>  
                <input <?php 
                if ($bojajPonovljenaLozinka || $bojajLozinke) {
                    echo "class='crvenaBoja'";
                }
                ?> style="margin-bottom: 5px; margin-left: 36px;" type="password" id="lozinka2" name="lozinka2" size="35" maxlength="50" placeholder="lozinka" required="required"><br>
                <div class="g-recaptcha" data-sitekey="6Lf4y3QgAAAAAMmXFi9uU2FTP5rZaRHVt75oqoSD"></div>

            </form>  
            <div style="text-align:center;">
                <input id="btnregistriraj" name="btnRegistrirajSe" form="formRegistracija" type="submit" class="submit" value="Registriraj se ">
            </div>

        </section>
        <footer>
            <address>Kontakt: 
                <a href="mailto:dmatijevi@foi.hr" style="color:black; text-decoration: none;">
                    Denis Matijević</a></address>
            <p>&copy; 2022 D. Matijević</p>            
                <img style="position:relative; top:-7px;" src="../materijali/HTML5.png" alt="HTML" height="62" width="62">            
                <img style="position:relative; top:0px;" src="../materijali/CSS3.png" alt="CSS3" height="75" width="75">
        </footer>
    </body>
</html>

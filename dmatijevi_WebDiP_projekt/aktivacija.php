<?php
error_reporting(E_ALL ^ E_NOTICE);  
$direktorij = getcwd();
$putanja = dirname($_SERVER['REQUEST_URI']);

include './zaglavlje.php';

if(isset($_GET['btnAktiviraj'])){
    
    $baza = new Baza();
    $baza->spojiDB();
    
    $email = $_GET['emailAktivacija'];
    $aktivacijskiKodAktivacija = $_GET['aktivacijskiKod'];
    
    $upit = "SELECT * FROM `korisnik` WHERE "
            . "`email`='{$email}'";

    $rezultat = $baza->selectDB($upit);
    
    $red = mysqli_fetch_array($rezultat);
      
    if ($email == ""|| $aktivacijskiKodAktivacija == "") {
        $porukaAktivacija = "Potrebno je popuniti sva polja!";
    }else{
        //sve je popunjeno
        if ($red['email'] != $email) {
            $porukaAktivacija = "Neispravan email!";
        }
        if ($red['email'] == $email && $aktivacijskiKodAktivacija != $red['aktivacijski_kod']) {
            $porukaAktivacija = "Neispravan aktivacijski kod!";
        }
        if ($red['email'] == $email && $aktivacijskiKodAktivacija == $red['aktivacijski_kod'] && $red['aktiviran'] == 0) {

            $vrijemeRegistriranja = $red['vrijeme_registriranja'];
            $vrijemeSada = date('Y-m-d H:i:s');
            
            $vrijemeSadaTimestamp = strtotime($vrijemeSada);
            $vrijemeRegistriranjaTimestamp = strtotime($vrijemeRegistriranja);
            
            $prošloVremena = $vrijemeSadaTimestamp - $vrijemeRegistriranjaTimestamp;
            $krajnjeVrijeme = $prošloVremena / 3600;
                       
            if ($krajnjeVrijeme < 7) {
                $sqlUpitAzurirajAktivacija = "UPDATE korisnik SET aktiviran = 1 WHERE `email`='{$email}'";
                $rezultatAzurirajAktivacija = $baza->updateDB($sqlUpitAzurirajAktivacija);
                if ($rezultatAzurirajAktivacija) {
                    $porukaAktivacija = "Račun je uspješno aktiviran!";
                    header("Location: obrasci/prijava.php");
                } else {
                    $porukaAktivacija = "Greška prilikom aktivacije!";
                }
            } else {
                $porukaAktivacija = "Istekao vam je kod za aktivaciju!";
            }
        } else if ($red['email'] == $email && $aktivacijskiKodAktivacija == $red['aktivacijski_kod'] && $red['aktiviran'] == 1) {
            $porukaAktivacija = "Račun je već aktiviran!";
        }
    }
    $baza->zatvoriDB();
}
?>
<!DOCTYPE html>
<html lang="hr">
    <head>
        <title>Aktivacija - Trčanje</title>
        <meta charset="utf-8">
        <meta name="author" content="Denis Matijević">
        <meta name="description" content="24.8.2022.">
        <meta name="viewport" content="width=device-width, minimum-scale=1, maximum-scale=1">
        <link href="css/dmatijevi.css" rel="stylesheet" type="text/css">
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
                            include 'izbornik.php';
                            ?>
                        </nav>
                    </div>
                </div>

            </div>      
            
            <a href="index.php">
                <img class="logo" src="materijali/logo.png" alt="Logo trčanje" width="140" height="120">
            </a>
            
            <h1 class="naslovStranice">AKTIVACIJA</h1>
            <?php
             echo "<p style = 'color:#c94f00; font-weight:bold; text-align:center;'>$porukaAktivacija</p>";      
            ?>
        </header>         
        <hr>
        <section>
            
            <form novalidate class="form" method="get" id="formAktivacija" name="formAktivacija" action="aktivacija.php">
                <label style="margin-bottom: 5px;" for="emailAktivacija">Email: </label>
                <input class="tbKorimePrijava" style="margin-left:98px;" 
                    <?php
                       if (isset($_GET['aktemail'])) {
                           $dohvaceniEmailAkt = $_GET['aktemail'];
                           echo "value='$dohvaceniEmailAkt'";
                       }
                       ?>type="text" id="emailAktivacija" name="emailAktivacija" size="30" maxlength="30" placeholder="ldap@foi.hr." autofocus="autofocus" required="required"><br>

                <label style="margin-bottom: 5px;" for="aktivacijskiKod">Aktivacijski kod: </label>
                <input class="tbKorimePrijava" type="password" id="aktivacijskiKod" name="aktivacijskiKod" size="30" maxlength="30" placeholder="xxxxxxxxxx" autofocus="autofocus" required="required"><br>

            </form>
            
            <div style="text-align:center;">
                <input form="formAktivacija" name="btnAktiviraj" type="submit" class="submit" value=" Aktiviraj ">
            </div>
            
            <hr>                           
            
        </section>
        <footer>
            <address>Kontakt: 
                <a href="mailto:dmatijevi@foi.hr" style="color:black; text-decoration: none;">
                    Denis Matijević</a></address>
            <p>&copy; 2022 D. Matijević</p>
                <img style="position:relative; top:-7px;" src="materijali/HTML5.png" alt="HTML" height="62" width="62">
                <img style="position:relative; top:0px;" src="materijali/CSS3.png" alt="CSS3" height="75" width="75">
        </footer>       
    </body>
</html>

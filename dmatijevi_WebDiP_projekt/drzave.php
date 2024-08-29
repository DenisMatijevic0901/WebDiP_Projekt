<?php
error_reporting(E_ALL ^ E_NOTICE);  
$direktorij = getcwd();
$putanja = dirname($_SERVER['REQUEST_URI']);

include './zaglavlje.php';

if (!isset($_SESSION["uloga"])) {
    header("Location: obrasci/prijava.php");
    Sesija::obrisiSesiju();
    exit();
}

if (isset($_SESSION["uloga"]) && ($_SESSION["uloga"] != 4)) {
    header("Location: obrasci/prijava.php");    
    unset($_COOKIE['autenticiran']); 
    setcookie('autenticiran', null, -1, '/');
    Sesija::obrisiSesiju();
    exit();
}

if (isset($_GET['btnKreirajDrzavu'])) {
    if ($_GET['naziv'] == "" || $_GET['glGrad'] == "") {
        $porukaDrzava = "Potrebno je popuniti sva polja!";
    } else {
        $naziv = $_GET['naziv'];
        $glavniGrad = $_GET['glGrad'];

        $baza = new Baza();
        $baza->spojiDB();
        $sqlUpit = "INSERT INTO drzava (naziv, glavni_grad)"
                . "VALUES ('$naziv', '$glavniGrad')";

        $rezultat = $baza->insertDB($sqlUpit);
        if ($rezultat) {
            $porukaDrzava = "Država kreirana.";
        }else{
            $porukaDrzava = "Greška prilikom kreiranja države.";
        }
        $baza->zatvoriDB();
    }
}

function dohvatiPodatke(){
    global $dohvaceniNaziv;
    global $dohvaceniGrad;
    global $dohvaceniId;
    $baza = new Baza();
    $baza->spojiDB();
    
    $dohvaceniId = $_GET['drzavaid'];
    
    $sqlUpit = "SELECT * FROM `drzava` WHERE drzava_id = '$dohvaceniId'";
    
    $rezultat = $baza->selectDB($sqlUpit);  
    $red = mysqli_fetch_array($rezultat);
    
    $baza->zatvoriDB();
    
    $dohvaceniId = $red['drzava_id'];
    $dohvaceniNaziv = $red['naziv'];
    $dohvaceniGrad = $red['glavni_grad'];
}
dohvatiPodatke();

if (isset($_GET['btnAzurirajDrzavu'])) {
    if ($_GET['nazivA'] == "" || $_GET['glGradA'] == "" || $_GET['idA'] == "") {
        $porukaDrzavaA = "Odaberite državu koju želite ažurirati!";
    } else {

        $noviId =  $_GET['idA'];
        $noviNaziv = $_GET['nazivA'];
        $noviGrad = $_GET['glGradA'];

        $baza = new Baza();
        $baza->spojiDB();

        $sqlUpitAzuriraj = "UPDATE drzava SET naziv = '$noviNaziv', glavni_grad = '$noviGrad' WHERE drzava_id = $noviId";

        $rezultatAzuriraj = $baza->updateDB($sqlUpitAzuriraj);

        if ($rezultatAzuriraj) {
            $porukaDrzavaA = "Država ažurirana.";
        }else{
            $porukaDrzavaA = "Greška prilikom ažuriranja države.";
        }
        $baza->zatvoriDB();
    }
}
?>
<!DOCTYPE html>
<html lang="hr">
    <head>
        <title>Države - Trčanje</title>
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

            <h1 class="naslovStranice">DRŽAVE</h1>
            <?php
            global $porukaDrzava;
            global $porukaDrzavaA;
            echo "<p style = 'color:red; font-weight:bold; text-align:center;'>$porukaDrzava</p>";
            echo "<p style = 'color:red; font-weight:bold; text-align:center;'>$porukaDrzavaA</p>";
            ?>
        </header>         
        <hr>
        <section>
            <h3 style="text-align: center; text-decoration: underline;">Kreiranje države</h3>
            <form novalidate class="form" method="get" id="formDrzave" name="formDrzave" action="drzave.php">

                <label style="margin-bottom: 5px;" for="naziv">Naziv: </label>
                <input class="tbKorimePrijava" style="margin-left: 72px;" type="text" id="naziv" name="naziv" size="30" maxlength="30" placeholder="naziv" autofocus="autofocus" required="required"><br>
                <label style="margin-bottom: 5px;" for="glGrad">Glavni grad: </label>
                <input class="tbKorimePrijava" type="text" id="glGrad" name="glGrad" size="30" maxlength="30" placeholder="glavni grad" autofocus="autofocus" required="required"><br>

            </form>

            <div style="text-align:center;">
                <input form="formDrzave" name="btnKreirajDrzavu" type="submit" class="submit" value=" Kreiraj ">
            </div>

            <hr>   
            
             <h3 style="text-align: center; text-decoration: underline;">Ažuriranje države</h3>
            <form novalidate class="form" method="get" id="formDrzaveA" name="formDrzaveA" action="drzave.php">
                
                <label style="margin-bottom: 5px;" for="idA">ID države: </label>
                <input class="tbKorimePrijava" style="margin-left: 44px;" 
                        <?php if (isset($_GET['drzavaid'])) {
                           global $dohvaceniId;
                           echo "value=$dohvaceniId";
                       } ?>
                       type="text" id="idA" name="idA" size="30" maxlength="30"  autofocus="autofocus" required="required"><br>
                <label style="margin-bottom: 5px;" for="nazivA">Naziv: </label>
                <input class="tbKorimePrijava" style="margin-left: 72px;" 
                        <?php if (isset($_GET['drzavaid'])) {
                           global $dohvaceniNaziv;
                           echo "value='$dohvaceniNaziv'";
                       } ?>
                       type="text" id="nazivA" name="nazivA" size="30" maxlength="30" autofocus="autofocus" required="required"><br>
                <label style="margin-bottom: 5px;" for="glGradA">Glavni grad: </label>
                <input class="tbKorimePrijava" <?php if (isset($_GET['drzavaid'])) {
                           global $dohvaceniGrad;
                           echo "value='$dohvaceniGrad'";
                       } ?>
                       type="text" id="glGradA" name="glGradA" size="30" maxlength="30" autofocus="autofocus" required="required"><br>
                
            </form>
            
            <div style="text-align:center;">
                <input form="formDrzaveA" name="btnAzurirajDrzavu" type="submit" class="submit" value=" Ažuriraj ">
            </div>
                     
            <hr>
            
            <?php
                echo "<table>
            <caption>Države</caption>
            <thead>
                <tr>
                    <th>ID Države</th>
                    <th>Naziv</th>
                    <th>Glavni grad</th>
                    <th>Dodjela moderatora</th>
                    <th>Ažuriranje</th>
                </tr>
            </thead>
            <tbody>";
            ?>
            
            <?php
            $baza = new Baza();
            $baza->spojiDB();
            $sqlUpit = "SELECT * FROM drzava";
            $rezultat = $baza->selectDB($sqlUpit);

            $baza->zatvoriDB();

            while ($zapis = mysqli_fetch_array($rezultat)) {
                echo "<tr>"
                . "<td>{$zapis['drzava_id']}</td>"
                . "<td>{$zapis['naziv']}</td>"
                . "<td>{$zapis['glavni_grad']}</td>"
                . "<td><a class='dodjelaModeratora' href='dodjela_moderatora.php?drzavaid={$zapis['drzava_id']}'>Dodijeli moderatora</a></td>"
                . "<td><a class='drzavaAzuriraj' href='drzave.php?drzavaid={$zapis['drzava_id']}'>Ažuriraj državu</a></td>"
                . "</tr>";
            }
            echo "</tbody>";
            echo "</table>";
            ?>
            
            <hr>

            <?php
            echo "<table>
            <caption>Države koje imaju dodijeljenog moderatora</caption>
            <thead>
                <tr>
                    <th>ID Države</th>
                    <th>Naziv</th>
                    <th>Glavni grad</th>
                    <th>Moderator</th>
                </tr>
            </thead>
            <tbody>";
            ?>

            <?php
            $baza = new Baza();
            $baza->spojiDB();
            $sqlUpit = "SELECT drzava_id, drzava.naziv, drzava.glavni_grad, concat(korisnik.ime,' ',korisnik.prezime) "
                    . "FROM drzava JOIN moderator_drzava ON drzava.drzava_id = moderator_drzava.drzava "
                    . "JOIN korisnik ON moderator_drzava.korisnik = korisnik.korisnik_id";
            $rezultat = $baza->selectDB($sqlUpit);

            $baza->zatvoriDB();

            while ($zapis = mysqli_fetch_array($rezultat)) {
                echo "<tr>"
                . "<td>{$zapis['drzava_id']}</td>"
                . "<td>{$zapis['naziv']}</td>"
                . "<td>{$zapis['glavni_grad']}</td>"
                . "<td>{$zapis[3]}</td>"
                . "</tr>";
            }
            echo "</tbody>";
            echo "</table>";
            ?>

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

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

if (isset($_SESSION["uloga"]) && ($_SESSION["uloga"] < 2)) {
    header("Location: obrasci/prijava.php");    
    unset($_COOKIE['autenticiran']); 
    setcookie('autenticiran', null, -1, '/');
    Sesija::obrisiSesiju();
    exit();
}

if (isset($_GET['etapaid']) && isset($_GET['korisnikid'])) {
    $IDEtape = $_GET['etapaid'];
    $IDPrijavljenogKorisnika = $_GET['korisnikid'];
    
    $baza = new Baza();
    $baza->spojiDB();
    
    $sqlUpitProvjeraRezultata = "SELECT * FROM rezultat_etape WHERE korisnik = $IDPrijavljenogKorisnika AND etapa = $IDEtape";
    $rezultatProvjeraRezultata = $baza->selectDB($sqlUpitProvjeraRezultata);
    $zapisProvjeraRezultata = mysqli_fetch_array($rezultatProvjeraRezultata);
    
    if ($zapisProvjeraRezultata != NULL) {
        $porukaPopisEtapaPrijavljenihUtrka = "Rezultat za tu etapu već postoji (ili ste ju završili ili ste odustali)!";
    } else {
        $porukaPopisEtapaPrijavljenihUtrka = "Odustali ste od etape pod rednim brojem $IDEtape";
        $sqlUpitOdustani = "INSERT INTO rezultat_etape (korisnik, etapa, odustao) VALUES ($IDPrijavljenogKorisnika, $IDEtape, 1)";
        $rezultatPopis = $baza->insertDB($sqlUpitOdustani);
    }
    $baza->zatvoriDB();
}
?>
<!DOCTYPE html>
<html lang="hr">
    <head>
        <title>Popis etapa prijavljenih utrka - Trčanje</title>
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
            
            <h1 class="naslovStranice" style="font-size:70px;">POPIS ETAPA PRIJAVLJENIH UTRKA</h1>
            <?php 
            global $porukaPopisEtapaPrijavljenihUtrka;
            echo "<p style = 'color:red; font-weight:bold; text-align:center;'>$porukaPopisEtapaPrijavljenihUtrka</p>";
            ?>
        </header>         
        <hr>
        <section>
            
            <?php          
            echo "<table>";
            echo "<caption>Popis svih prijavljenih etapa</caption>";
            echo "<thead>
                <tr>
                    <th>ID etape</th>
                    <th>Naziv utrke</th>
                    <th>Naziv etape</th>
                    <th>Država u kojoj se odvija utrka</th>
                    <th>Odustajanje</th>
                </tr>
            </thead>
            <tbody>"; 
            ?>
            <?php
            $baza = new Baza();
            $baza->spojiDB();
            
            $korisnickoImeKorisnikaPopis = $_SESSION['korisnik'];
            $sqlUpitDohvatiIDPopis = "SELECT korisnik_id FROM korisnik WHERE korisnicko_ime = '$korisnickoImeKorisnikaPopis'";
            $rezultatDohvatiIDPopis = $baza->selectDB($sqlUpitDohvatiIDPopis);
            $zapisIDPopis = mysqli_fetch_array($rezultatDohvatiIDPopis);
            $prijavljeniKorisnikIDPopis = $zapisIDPopis['korisnik_id'];
      
            $sqlUpitPopis = "SELECT etapa.etapa_id, utrka.naziv, etapa.naziv, drzava.naziv "
                    . "FROM utrka, prijava, etapa, drzava "
                    . "WHERE drzava.drzava_id = utrka.drzava "
                    . "AND utrka.utrka_id = prijava.utrka "
                    . "AND utrka.utrka_id = etapa.utrka "
                    . "AND prijava.korisnik = $prijavljeniKorisnikIDPopis";
            $rezultatPopis = $baza->selectDB($sqlUpitPopis);

            $baza->zatvoriDB();
            while ($zapis = mysqli_fetch_array($rezultatPopis)) {
                echo "<tr>";
                echo "<td>{$zapis[0]}</td>";
                echo "<td>{$zapis[1]}</td>";
                echo "<td>{$zapis[2]}</td>";
                echo "<td>{$zapis[3]}</td>";
                echo "<td><a class='etapaOdustani' href='popis_etapa_prijavljenih_utrka.php?etapaid={$zapis['etapa_id']}&korisnikid=$prijavljeniKorisnikIDPopis'>Odustani od etape</a></td>";
                echo "</tr>";
            }
            echo "</tbody>";
            echo "</table>";
            ?>
            
            <hr>
            
            <?php          
            echo "<table>";
            echo "<caption>Rezultati etapa prijavljenih utrka</caption>";
            echo "<thead>
                <tr>
                    <th>ID etape</th>
                    <th>Naziv utrke</th>
                    <th>Naziv etape</th>
                    <th>Država u kojoj se odvija utrka</th>
                    <th>Evidentirano vrijeme</th>
                    <th>Ostvareni bodovi</th>
                    <th>Odustao</th>
                </tr>
            </thead>
            <tbody>"; 
            ?>
            <?php
            $baza = new Baza();
            $baza->spojiDB();
            
            $korisnickoImeKorisnika = $_SESSION['korisnik'];
            $sqlUpitDohvatiID = "SELECT korisnik_id FROM korisnik WHERE korisnicko_ime = '$korisnickoImeKorisnika'";
            $rezultatDohvatiID = $baza->selectDB($sqlUpitDohvatiID);
            $zapisID = mysqli_fetch_array($rezultatDohvatiID);
            $prijavljeniKorisnikID = $zapisID['korisnik_id'];
      
            $sqlUpit = "SELECT utrka.naziv, etapa.naziv, drzava.naziv, rezultat_etape.evidentirano_vrijeme, rezultat_etape.ostvareni_bodovi, rezultat_etape.odustao, etapa.etapa_id "
                    . "FROM utrka, drzava, etapa, prijava, rezultat_etape "
                    . "WHERE utrka.utrka_id = etapa.utrka "
                    . "AND utrka.utrka_id = prijava.utrka "
                    . "AND utrka.drzava = drzava.drzava_id "
                    . "AND rezultat_etape.etapa = etapa.etapa_id "
                    . "AND prijava.korisnik = $prijavljeniKorisnikID "
                    . "AND rezultat_etape.korisnik = $prijavljeniKorisnikID";
            $rezultat = $baza->selectDB($sqlUpit);

            $baza->zatvoriDB();
            while ($zapis = mysqli_fetch_array($rezultat)) {
                echo "<tr>";
                echo "<td>{$zapis[6]}</td>";
                echo "<td>{$zapis[0]}</td>";
                echo "<td>{$zapis[1]}</td>";
                echo "<td>{$zapis[2]}</td>";
                echo "<td>{$zapis[3]}</td>";
                echo "<td>{$zapis[4]}</td>";
                if ($zapis[5] == 1){
                    echo "<td style='background-color:#D24D57;'>Odustao</td>";             
                }else {
                echo "<td style='background-color:#26A65B;;'>Nije odustao</td>";
                
                }
                echo "</tr>";
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

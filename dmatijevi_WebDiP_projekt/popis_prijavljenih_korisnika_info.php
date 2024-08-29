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

if (isset($_SESSION["uloga"]) && ($_SESSION["uloga"] < 3)) {
    header("Location: obrasci/prijava.php");    
    unset($_COOKIE['autenticiran']); 
    setcookie('autenticiran', null, -1, '/');
    Sesija::obrisiSesiju();
    exit();
}

?>
<!DOCTYPE html>
<html lang="hr">
    <head>
        <title>Popis prijavljenih korisnika info - Trčanje</title>
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
            
            <h1 class="naslovStranice" style="font-size: 64px;">POPIS PRIJAVLJENIH KORISNIKA INFO</h1>
        </header>         
        <hr>
        <section>
            <?php
            $baza = new Baza();
            $baza->spojiDB();
            
            $korisnickoImeKorisnika = $_SESSION['korisnik'];
            $sqlUpitDohvatiID = "SELECT korisnik_id FROM korisnik WHERE korisnicko_ime = '$korisnickoImeKorisnika'";
            $rezultatDohvatiID = $baza->selectDB($sqlUpitDohvatiID);
            $zapisID = mysqli_fetch_array($rezultatDohvatiID);
            $prijavljeniKorisnikID = $zapisID['korisnik_id'];
            
            $sqlUpitIDeviEtapaModeratora = "SELECT etapa.etapa_id FROM etapa, utrka, drzava, moderator_drzava "
                    . "WHERE etapa.utrka = utrka.utrka_id "
                    . "AND utrka.drzava = drzava.drzava_id "
                    . "AND drzava.drzava_id = moderator_drzava.drzava "
                    . "AND moderator_drzava.korisnik = $prijavljeniKorisnikID ORDER BY 1 ASC";
            $rezultatIDeviEtapaModeratora = $baza->selectDB($sqlUpitIDeviEtapaModeratora);
            
            echo "<p style='margin-left: 50px; margin-bottom: 50px; font-weight:bolder;'>ID-evi etapa trenutno prijavljenog moderatora:";
             while ($zapis = mysqli_fetch_array($rezultatIDeviEtapaModeratora)) {
                echo " {$zapis[0]} ";
             }
            echo "</p>";
            echo "<table>";
            echo "<caption>Popis prijavljenih korisnika s informacijom rezultata</caption>";
            echo "<thead>
                <tr>
                    <th>Ime</th>
                    <th>Prezime</th>
                    <th>Država u kojoj se odvija utrka</th>
                    <th>Naziv utrke</th>
                    <th>Naziv etape</th>
                    <th>ID etape</th>
                    <th>Odustao</th>
                </tr>
            </thead>
            <tbody>"; 
            $baza->zatvoriDB();
            ?>
            <?php
            $baza = new Baza();
            $baza->spojiDB();
            
            
        
            $sqlUpit = "SELECT korisnik.ime, korisnik.prezime, drzava.naziv, utrka.naziv, etapa.naziv, rezultat_etape.odustao,etapa.etapa_id "
                    . "FROM korisnik, rezultat_etape, etapa, utrka, drzava, moderator_drzava "
                    . "WHERE korisnik.korisnik_id = rezultat_etape.korisnik "
                    . "AND rezultat_etape.etapa = etapa.etapa_id "
                    . "AND etapa.utrka = utrka_id "
                    . "AND utrka.drzava = drzava.drzava_id "
                    . "AND drzava.drzava_id = moderator_drzava.drzava "
                    . "AND moderator_drzava.korisnik = $prijavljeniKorisnikID ORDER BY 1 ASC, utrka.naziv ASC, etapa.naziv ASC";
            $rezultat = $baza->selectDB($sqlUpit);

            $baza->zatvoriDB();
            while ($zapis = mysqli_fetch_array($rezultat)) {
                echo "<tr>";
                echo "<td>{$zapis[0]}</td>";
                echo "<td>{$zapis[1]}</td>";
                echo "<td>{$zapis[2]}</td>";
                echo "<td>{$zapis[3]}</td>";
                echo "<td>{$zapis[4]}</td>";
                echo "<td>{$zapis[6]}</td>";
                if ($zapis[5] == 1){
                    echo "<td style='background-color:#D24D57;'>Odustao</td>";             
                }else {
                echo "<td style='background-color:#26A65B;;'>Završio</td>";
                
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


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

if (isset($_GET['idblokiraj'])) {
    $baza = new Baza();
    $baza->spojiDB();
    $dohvaceniIdBlokiraj = $_GET['idblokiraj'];
    
    $sqlUpitBlokiraj = "UPDATE korisnik SET status = 1 WHERE korisnik_id = $dohvaceniIdBlokiraj";
    $rezultatBlokiraj = $baza->updateDB($sqlUpitBlokiraj);
    $baza->zatvoriDB();
}

if (isset($_GET['idodblokiraj'])) {
    $baza = new Baza();
    $baza->spojiDB();
    $dohvaceniIdOdblokiraj = $_GET['idodblokiraj'];
    
    $sqlUpitOdblokiraj = "UPDATE korisnik SET status = 0, broj_neuspjesnih_prijava = 0 WHERE korisnik_id = $dohvaceniIdOdblokiraj";
    $rezultatOdblokiraj = $baza->updateDB($sqlUpitOdblokiraj);
    $baza->zatvoriDB();
}
?>
<!DOCTYPE html>
<html lang="hr">
    <head>
        <title>Blokirani korisnici - Trčanje</title>
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
            
            <h1 class="naslovStranice" style="font-size: 70px;">PREGLED BLOKIRANIH KORISNIKA</h1>
           
        </header>               
        <section>          
                                                          
                     
            <hr>
            <?php
            echo "<table>
            <caption>Blokirani korisnici</caption>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Ime</th>
                    <th>Prezime</th>
                    <th>Datum rođenja</th>
                    <th>Email</th>
                    <th>Korisničko ime</th>
                    <th>Status</th>
                    <th>Aktiviran</th>
                    <th>Tip korisnika</th>
                    <th>Odblokiranje</th>
                </tr>
            </thead>
            <tbody>";
            ?>
            
              <?php 

                  $baza = new Baza();
                  $baza->spojiDB();
                  $sqlUpitBlokirani = "SELECT korisnik.korisnik_id, korisnik.ime, korisnik.prezime, korisnik.datum_rodenja, korisnik.email, korisnik.korisnicko_ime,
                          korisnik.status, korisnik.aktiviran, tip_korisnika.naziv 
                          FROM korisnik JOIN tip_korisnika
                          ON korisnik.tip_korisnika = tip_korisnika.tip_korisnika_id 
                          AND korisnik.status = 1 ORDER BY 1";
                  $rezultatBlokirani = $baza->selectDB($sqlUpitBlokirani);
                         
                  
                  
                  while ($zapis = mysqli_fetch_array($rezultatBlokirani)) {
                      echo "<tr>"
                      . "<td>{$zapis['korisnik_id']}</td>"
                      . "<td>{$zapis['ime']}</td>"
                      . "<td>{$zapis['prezime']}</td>"
                      . "<td>{$zapis['datum_rodenja']}</td>"
                      . "<td>{$zapis['email']}</td>"
                      . "<td>{$zapis['korisnicko_ime']}</td>"
                      . "<td>{$zapis['status']}</td>"
                      . "<td>{$zapis['aktiviran']}</td>"
                      . "<td>{$zapis['naziv']}</td>"
                      . "<td><a class='blokiranikorisnici' href='pregled_blokiranih_korisnika.php?idodblokiraj={$zapis['korisnik_id']}'>Odblokiraj korisnika</a></td>"     
                      . "</tr>";
                  }
                  echo "</tbody>";
                  echo "</table>";
                ?> 
            <hr>
                <?php 
                echo "<table>
                <caption>Neblokirani korisnici</caption>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Ime</th>
                        <th>Prezime</th>
                        <th>Datum rođenja</th>
                        <th>Email</th>
                        <th>Korisničko ime</th>
                        <th>Status</th>
                        <th>Aktiviran</th>
                        <th>Tip korisnika</th>
                        <th>Blokiranje</th>
                        </tr>
                </thead>
                <tbody>";
                ?>
                <?php
                $sqlUpitOdblokirani = "SELECT korisnik.korisnik_id, korisnik.ime, korisnik.prezime, korisnik.datum_rodenja, korisnik.email, korisnik.korisnicko_ime,
                          korisnik.status, korisnik.aktiviran, tip_korisnika.naziv 
                          FROM korisnik JOIN tip_korisnika
                          ON korisnik.tip_korisnika = tip_korisnika.tip_korisnika_id 
                          AND korisnik.status = 0 ORDER BY 1";
                $rezultatOdblokirani = $baza->selectDB($sqlUpitOdblokirani);
                $baza->zatvoriDB();
                
                while ($zapis = mysqli_fetch_array($rezultatOdblokirani)) {
                      echo "<tr>"
                      . "<td>{$zapis['korisnik_id']}</td>"
                      . "<td>{$zapis['ime']}</td>"
                      . "<td>{$zapis['prezime']}</td>"
                      . "<td>{$zapis['datum_rodenja']}</td>"
                      . "<td>{$zapis['email']}</td>"
                      . "<td>{$zapis['korisnicko_ime']}</td>"
                      . "<td>{$zapis['status']}</td>"
                      . "<td>{$zapis['aktiviran']}</td>"
                      . "<td>{$zapis['naziv']}</td>"
                      . "<td><a class='blokiranikorisnici' href='pregled_blokiranih_korisnika.php?idblokiraj={$zapis['korisnik_id']}'>Blokiraj korisnika</a></td>"     
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

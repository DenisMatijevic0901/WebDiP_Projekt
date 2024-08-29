<?php
error_reporting(E_ALL ^ E_NOTICE);
error_reporting(E_ERROR | E_PARSE);
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

if (isset($_GET['btnKreirajPrijavuUtrke'])) {
    if ($_GET['godinaRodenja'] == "" || $_GET['odaberiSliku'] == "" || $_GET['odaberiUtrku'] == "nijeOdabranaUtrka") {
        $porukaPrijavaUtrke = "Potrebno je popuniti sva polja!";
    } else {
        $godinaRodenjaKorisnika = $_GET['godinaRodenja'];
        $slika = $_GET['odaberiSliku'];
        $IDUtrke = $_GET['odaberiUtrku'];
        
        $baza = new Baza();
        $baza->spojiDB();
        
        $korisnickoImeKorisnika = $_SESSION['korisnik'];
        $sqlUpitDohvatiID = "SELECT korisnik_id FROM korisnik WHERE korisnicko_ime = '$korisnickoImeKorisnika'";
        $rezultatDohvatiID = $baza->selectDB($sqlUpitDohvatiID);
        $zapisID = mysqli_fetch_array($rezultatDohvatiID);
        $prijavljeniKorisnikID = $zapisID['korisnik_id'];
        
        $sqlUpitProvjeraPostojanja = "SELECT * FROM prijava WHERE korisnik = $prijavljeniKorisnikID AND utrka = $IDUtrke";
        $rezultatProvjeraPostojanja = $baza->selectDB($sqlUpitProvjeraPostojanja);
        $zapisProvjeraPostojanja = mysqli_fetch_array($rezultatProvjeraPostojanja);

        if ($zapisProvjeraPostojanja != NULL) {
            $porukaPrijavaUtrke = "Prijava za tu utrku već postoji!";
        } else {
            $sqlUpit = "INSERT INTO prijava (godina_rodenja, slika, utrka, korisnik)"
                    . "VALUES ('$godinaRodenjaKorisnika', '$slika', $IDUtrke, $prijavljeniKorisnikID)";

            $rezultat = $baza->insertDB($sqlUpit);
            if ($rezultat) {
                $porukaPrijavaUtrke = "Prijava za utrku kreirana.";
            } else {
                $porukaPrijavaUtrke = "Greška prilikom kreiranja prijave za utrku.";
            }
        }
        $baza->zatvoriDB();
    }
}

function dohvatiPodatke(){
    global $dohvaceniIDPrijave;
    global $dohvacenaGodinaRodenja;
    global $dohvacenaSlika;
    global $dohvaceniIDUtrke;
    global $dohvaceniIDKorisnika;
    
    $baza = new Baza();
    $baza->spojiDB();
    
    $dohvaceniIDPrijave = $_GET['prijavaid'];
    
    $sqlUpit = "SELECT * FROM prijava WHERE prijava_id = $dohvaceniIDPrijave";
    
    $rezultat = $baza->selectDB($sqlUpit);  
    $red = mysqli_fetch_array($rezultat);
    
    $danasnjiDatum = date("Y-m-d H:i:s");  
   
    $dohvaceniIDPrijave = $red[0];
    $dohvacenaGodinaRodenja = $red[1];
    $dohvacenaSlika = $red[2];
    $dohvaceniIDUtrke = $red[3];
    $dohvaceniIDKorisnika = $red[4];
    
    $sqlProvjera = "SELECT utrka.vrijeme_zavrsetka_prijava FROM utrka WHERE utrka_id = $dohvaceniIDUtrke";
    $rezultatProvjera = $baza->selectDB($sqlProvjera);
    $redProvjera = mysqli_fetch_array($rezultatProvjera);
    if (isset($_GET['prijavaid'])) {
        global $porukaPrijavaUtrkeA;
        global $nijeMoguceAzurirati;
        if ($redProvjera['vrijeme_zavrsetka_prijava'] < $danasnjiDatum) {
            $porukaPrijavaUtrkeA = "Ne možete ažurirati ovu prijavu jer je završeno vrijeme prijava.";
            $nijeMoguceAzurirati = true;
        } else {
            $porukaPrijavaUtrkeA = "Možete ažurirati ovu prijavu.";
        }
    }
    $baza->zatvoriDB();
}
dohvatiPodatke();

if (isset($_GET['btnAzurirajPrijavuUtrke'])) {
     if ($_GET['godinaRodenjaA'] == "" || $_GET['odaberiSlikuA'] == "" || $_GET['odaberiUtrkuA'] == "nijeOdabranaUtrkaA" ) {
        $porukaPrijavaUtrkeA = "Odaberite prijavu utrke koju želite ažurirati!";
    } else {
        global $$dohvaceniIDPrijave;
        $noviPrijavaID = $_GET['prijavaIDA'];
        $novaGodinaRodenja =  $_GET['godinaRodenjaA'];
        $novaSlika = $_GET['odaberiSlikuA'];
        $novaUtrkaID = $_GET['odaberiUtrkuA'];         

        $baza = new Baza();
        $baza->spojiDB();
        
        $sqlUpitAzuriraj = "UPDATE prijava SET godina_rodenja = $novaGodinaRodenja, slika = '$novaSlika', utrka = $novaUtrkaID WHERE prijava_id = $noviPrijavaID";
        
        $rezultatAzuriraj = $baza->updateDB($sqlUpitAzuriraj);

        if ($rezultatAzuriraj) {
            $porukaPrijavaUtrkeA = "Prijava utrke ažurirana.";
        }else{
            $porukaPrijavaUtrkeA = "Greška prilikom ažuriranja prijave za utrku.";
        }
        $baza->zatvoriDB();
    }
}
?>
<!DOCTYPE html>
<html lang="hr">
    <head>
        <title>Prijava utrke - Trčanje</title>
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
            
            <h1 class="naslovStranice">PRIJAVA UTRKE</h1>
            <?php
            global $porukaPrijavaUtrke;
            global $porukaPrijavaUtrkeA;
            echo "<p style = 'color:red; font-weight:bold; text-align:center;'>$porukaPrijavaUtrke</p>";
            echo "<p style = 'color:red; font-weight:bold; text-align:center;'>$porukaPrijavaUtrkeA</p>";
            ?>
        </header>         
        <hr>
        <section>
            
            <h3 style="text-align: center; text-decoration: underline;">Kreiranje prijave za utrku</h3>
            <form novalidate class="form" method="get" id="formPrijavaUtrke" name="formPrijavaUtrke" action="prijava_utrke.php">

                <label style="margin-bottom: 5px;" for="godinaRodenja">Godina rođenja: </label>
                <input class="tbKorimePrijava" style="margin-left: 168px; margin-bottom: 15px;" type="text" id="godinaRodenja" name="godinaRodenja" size="30" maxlength="30" placeholder="gggg" autofocus="autofocus" required="required"><br>
                
                <label style="margin-bottom: 5px;" for="odaberiSliku">Slika: </label>    
                <input  style="margin-left: 242px; margin-bottom: 15px; width: 303px; height:auto;" type="file" id="odaberiSliku" name="odaberiSliku"><br>
                
                <label style="margin-bottom: 5px;" for="odaberiUtrku">Utrka: </label>
                <select class="tbKorimePrijava" style="width: 305px; height:30px; margin-left: 238px;" name="odaberiUtrku" id="odaberiUtrku">
                    <option style='text-align:center;' value='nijeOdabranaUtrka'>--Odaberite utrku--</option>
                     <?php
                     $baza = new Baza();
                     $baza->spojiDB();
                     $danasnjiDatum = date("Y-m-d H:i:s");
                     $sqlUpitPrijavaUtrke = "SELECT utrka_id, utrka.naziv, drzava.naziv, utrka.vrijeme_zavrsetka_prijava "
                             . "FROM utrka, drzava "
                             . "WHERE utrka.drzava = drzava.drzava_id "
                             . "AND utrka.vrijeme_zavrsetka_prijava > '$danasnjiDatum'";
                     $rezultatPrijavaUtrke = $baza->selectDB($sqlUpitPrijavaUtrke);
                     $baza->zatvoriDB();
                     while ($zapis = mysqli_fetch_array($rezultatPrijavaUtrke)) {
                         echo "<option style='text-align:center;' value='$zapis[0]'>$zapis[1] | $zapis[2] | moguće se prijaviti do: $zapis[3]</option>";
                     }
                     ?>                                                        
                    </select><br>
            </form>

            <div style="text-align:center;">
                <input form="formPrijavaUtrke" name="btnKreirajPrijavuUtrke" type="submit" class="submit" value=" Kreiraj ">
            </div>
            
            <hr>
            
             <h3 style="text-align: center; text-decoration: underline;">Ažuriranje prijave za utrku</h3>
            <form novalidate class="form" method="get" id="formPrijavaUtrkeA" name="formPrijavaUtrkeA" action="prijava_utrke.php">
                
                <label style="margin-bottom: 5px;" for="prijavaIDA">ID prijave: </label>    
                <input  style="margin-left: 208px; margin-bottom: 15px;" 
                        <?php
                        if (isset($_GET['prijavaid'])) {
                            global $dohvaceniIDPrijave;
                            echo "value=$dohvaceniIDPrijave";
                        }
                        if (!isset($_GET['prijavaid'])) {
                            echo "disabled";
                        }
                        ?>
                        <?php
                        if ($nijeMoguceAzurirati) {
                            echo "type='hidden'";
                        } else {
                            echo "type='text'";
                        }
                        ?>
                        id="prijavaIDA" name="prijavaIDA"><br>
                           
                <label style="margin-bottom: 5px;" for="godinaRodenjaA">Godina rođenja: </label>
                <input class="tbKorimePrijava" style="margin-left: 168px; margin-bottom: 15px;" 
                        <?php
                       if (isset($_GET['prijavaid'])) {
                           global $dohvacenaGodinaRodenja;
                           echo "value=$dohvacenaGodinaRodenja";
                       }
                       if (!isset($_GET['prijavaid'])) {
                           echo "disabled";
                       }
                       ?>
                       <?php
                       if ($nijeMoguceAzurirati) {
                           echo "type='hidden'";
                       } else {
                           echo "type='text'";
                       }
                       ?>
                       id="godinaRodenjaA" name="godinaRodenjaA" size="30" maxlength="30" autofocus="autofocus" required="required"><br>
                
                <label style="margin-bottom: 5px;" for="odaberiSlikuA">Slika: </label>    
                <input  style="margin-left: 242px; margin-bottom: 15px; width: 303px; height:auto;" 
                        <?php
                        if (isset($_GET['prijavaid'])) {
                            global $dohvacenaSlika;
                            echo "value=$dohvacenaSlika";
                        }
                        if (!isset($_GET['prijavaid'])) {
                            echo "disabled";
                        }
                        ?>
                        <?php
                        if ($nijeMoguceAzurirati) {
                            echo "type='hidden'";
                        } else {
                            echo "type='file'";
                        }
                        ?>
                        id="odaberiSlikuA" name="odaberiSlikuA"><br>
                
                <label style="margin-bottom: 5px;" for="odaberiUtrkuA">Utrka: </label>
                <select class="tbKorimePrijava" style="width: 305px; height:30px; margin-left: 238px;" name="odaberiUtrkuA" id="odaberiUtrkuA" 
                        <?php if (!isset($_GET['prijavaid'])) {
                            echo "disabled";
                        } ?> 
                        <?php
                        if ($nijeMoguceAzurirati) {
                            echo "hidden";
                        } 
                        ?>>
                    <option style='text-align:center;' value='nijeOdabranaUtrkaA'>--Odaberite utrku--</option>
                     <?php
                     $baza = new Baza();
                     $baza->spojiDB();
                     $danasnjiDatum = date("Y-m-d H:i:s");
                     $sqlUpitPrijavaUtrke = "SELECT utrka_id, utrka.naziv, drzava.naziv, utrka.vrijeme_zavrsetka_prijava "
                             . "FROM utrka, drzava "
                             . "WHERE utrka.drzava = drzava.drzava_id "
                             . "AND utrka.vrijeme_zavrsetka_prijava > '$danasnjiDatum'";
                     $rezultatPrijavaUtrke = $baza->selectDB($sqlUpitPrijavaUtrke);
                     $baza->zatvoriDB();
                     while ($zapis = mysqli_fetch_array($rezultatPrijavaUtrke)) {
                         echo "<option style='text-align:center;' value='$zapis[0]'>$zapis[1] | $zapis[2] | moguće se prijaviti do: $zapis[3]</option>";
                     }
                     ?>                                                        
                    </select><br>
            </form>

            <div style="text-align:center;">
                <input form="formPrijavaUtrkeA" name="btnAzurirajPrijavuUtrke" type="submit" class="submit" value=" Ažuriraj ">
            </div>
            
            
            <?php         
            echo "<table>";
            echo "<caption>Pregled prijavljenih utrka</caption>";
            echo "<thead>
                <tr>
                    <th>ID prijave</th>
                    <th>Korisnik</th>
                    <th>Godina rođenja</th>
                    <th>Slika</th>
                    <th>Naziv utrke</th>
                    <th>Država u kojoj se odvija utrka</th>
                    <th>Ažuriranje</th>
                </tr>
            </thead>
            <tbody>"; 
            ?>
            <?php
            $baza = new Baza();
            $baza->spojiDB();
            
            $korisnickoImeKorisnika= $_SESSION['korisnik'];
            $sqlUpitDohvatiID = "SELECT korisnik_id FROM korisnik WHERE korisnicko_ime = '$korisnickoImeKorisnika'";
            $rezultatDohvatiID = $baza->selectDB($sqlUpitDohvatiID);
            $zapisID = mysqli_fetch_array($rezultatDohvatiID);
            $prijavljeniKorisnikID = $zapisID['korisnik_id'];
            
            $sqlUpit = "SELECT prijava.prijava_id, concat(korisnik.ime,' ',korisnik.prezime), prijava.godina_rodenja, prijava.slika, utrka.naziv, drzava.naziv "
                    . "FROM korisnik, prijava, utrka, drzava "
                    . "WHERE korisnik.korisnik_id = prijava.korisnik "
                    . "AND prijava.utrka = utrka.utrka_id "
                    . "AND utrka.drzava = drzava.drzava_id "
                    . "AND prijava.korisnik = $prijavljeniKorisnikID";
            $rezultat = $baza->selectDB($sqlUpit);

            $baza->zatvoriDB();
            while ($zapis = mysqli_fetch_array($rezultat)) {
                echo "<tr>"
                . "<td>{$zapis[0]}</td>"
                . "<td>{$zapis[1]}</td>"
                . "<td>{$zapis[2]}</td>"
                . "<td><img src='materijali/{$zapis[3]}' alt ='slikaKorisnika' width='170' height='170' style='border-radius:13px;'></img></td>"
                . "<td>{$zapis[4]}</td>"
                . "<td>{$zapis[5]}</td>"
                . "<td><a class='prijavaUtrkeAzuriraj' href='prijava_utrke.php?prijavaid={$zapis[0]}'>Ažuriraj prijavu</a></td>"
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

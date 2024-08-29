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

if (isset($_SESSION["uloga"]) && ($_SESSION["uloga"] != 4)) {
    header("Location: obrasci/prijava.php");    
    unset($_COOKIE['autenticiran']); 
    setcookie('autenticiran', null, -1, '/');
    Sesija::obrisiSesiju();
    exit();
}

if (isset($_GET['btnKreirajUtrku'])) {
    if ($_GET['vrijemeZavrsetkaPrijava'] == NULL || $_GET['naziv'] == "" || $_GET['odaberiDrzavu'] == "") {
        $porukaUtrke = "Potrebno je popuniti sva polja!";
    } else {
        $naziv = $_GET['naziv'];
        $vrijemeZavrsetkaPrijava = $_GET['vrijemeZavrsetkaPrijava'];
        $formatiranoVrijemeZavrsetkaPrijava = date("Y-m-d H:i:s", strtotime($vrijemeZavrsetkaPrijava));
        $IdDrzaveUtrka = $_GET['odaberiDrzavu'];
        
        $baza = new Baza();
        $baza->spojiDB();
        $sqlUpit = "INSERT INTO utrka (naziv, vrijeme_zavrsetka_prijava, zakljucana, drzava)"
                . "VALUES ('$naziv', '$formatiranoVrijemeZavrsetkaPrijava', 0, $IdDrzaveUtrka)";

        $rezultat = $baza->insertDB($sqlUpit);
        if ($rezultat) {
            $porukaUtrke = "Utrka kreirana.";
        } else {
            $porukaUtrke = "Greška prilikom kreiranja utrke.";
        }
        $baza->zatvoriDB();
    }
}

function dohvatiPodatke(){
    global $dohvaceniId;
    global $dohvaceniNaziv;
    global $dohvacenoVrijemeZavrsetkaPrijava;
    global $dohvacenoZakljucano;
    $baza = new Baza();
    $baza->spojiDB();
    
    $dohvaceniId = $_GET['utrkaid'];
    
    $sqlUpit = "SELECT utrka_id, naziv, vrijeme_zavrsetka_prijava, zakljucana, drzava FROM utrka WHERE utrka_id = $dohvaceniId";
    
    $rezultat = $baza->selectDB($sqlUpit);  
    $red = mysqli_fetch_array($rezultat);
    
    $baza->zatvoriDB();
    
    $dohvaceniId = $red['utrka_id'];
    $dohvaceniNaziv = $red['naziv'];
    $dohvacenoVrijemeZavrsetkaPrijava = $red['vrijeme_zavrsetka_prijava'];
    $dohvacenoZakljucano = $red['zakljucana'];
    
}
dohvatiPodatke();

if (isset($_GET['btnAzurirajUtrku'])) {
    if ($_GET['idA'] == "" || $_GET['nazivA'] == "" || $_GET['vrijemeZavrsetkaPrijavaA'] == "" || $_GET['odaberiDrzavuA'] == "nijeOdabranaDrzavaA" || $_GET['zakljucanaA'] == "") {
        $porukaUtrkeA = "Odaberite utrku koju želite ažurirati!";
    } else {

        $noviId =  $_GET['idA'];
        $noviNaziv = $_GET['nazivA'];
        $novoVrijemeZavrsetkaPrijava = $_GET['vrijemeZavrsetkaPrijavaA'];
        $novoZakljucana = $_GET['zakljucanaA'];
        $novaDrzava = $_GET['odaberiDrzavuA'];

        $baza = new Baza();
        $baza->spojiDB();

        $sqlUpitAzuriraj = "UPDATE utrka SET naziv = '$noviNaziv', vrijeme_zavrsetka_prijava = '$novoVrijemeZavrsetkaPrijava', zakljucana = $novoZakljucana, drzava = '$novaDrzava' WHERE utrka_id = $noviId";

        $rezultatAzuriraj = $baza->updateDB($sqlUpitAzuriraj);

        if ($rezultatAzuriraj) {
            $porukaUtrkeA = "Utrka ažurirana.";
        }else{
            $porukaUtrkeA = "Greška prilikom ažuriranja utrke.";
        }
        $baza->zatvoriDB();
    }
}
?>
<!DOCTYPE html>
<html lang="hr">
    <head>
        <title>Utrke - Trčanje</title>
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

            <h1 class="naslovStranice">UTRKE</h1>
            <?php
            global $porukaUtrke;
            global $porukaUtrkeA;
            echo "<p style = 'color:red; font-weight:bold; text-align:center;'>$porukaUtrke</p>";
            echo "<p style = 'color:red; font-weight:bold; text-align:center;'>$porukaUtrkeA</p>";
            ?>
        </header>         
        <hr>
        <section>
            <h3 style="text-align: center; text-decoration: underline;">Kreiranje utrke</h3>
            <form novalidate class="form" method="get" id="formUtrke" name="formUtrke" action="utrke.php">

                <label style="margin-bottom: 5px;" for="naziv">Naziv: </label>
                <input class="tbKorimePrijava" style="margin-left: 168px;" type="text" id="naziv" name="naziv" size="30" maxlength="30" placeholder="naziv" autofocus="autofocus" required="required"><br>
                
                <label style="margin-bottom: 5px;" for="vrijemeZavrsetkaPrijava">Vrijeme završetka prijava: </label>
                <input class="tbKorimePrijava" type="datetime-local" id="vrijemeZavrsetkaPrijava" name="vrijemeZavrsetkaPrijava" size="30" maxlength="30" required="required"><br>
                
                <label style="margin-bottom: 5px;" for="odaberiDrzavu">Država: </label>
                <select class="tbKorimePrijava" style="width: 305px; height:30px; margin-left: 157px;" name="odaberiDrzavu" id="odaberiDrzavu">
                    <option style='text-align:center;' value='nijeOdabranaDrzava'>--Odaberite državu--</option>
                     <?php
                     $baza = new Baza();
                     $baza->spojiDB();
                     $sqlUpitUtrka = "SELECT drzava_id, drzava.naziv FROM drzava";
                     $rezultatUtrka = $baza->selectDB($sqlUpitUtrka);
                     $baza->zatvoriDB();
                     while ($zapis = mysqli_fetch_array($rezultatUtrka)) {
                         echo "<option style='text-align:center;' value='$zapis[0]'>$zapis[1]</option>";
                     }
                     ?>                                                        
                    </select><br>
            </form>

            <div style="text-align:center;">
                <input form="formUtrke" name="btnKreirajUtrku" type="submit" class="submit" value=" Kreiraj ">
            </div>
            
            <hr>
            
            <h3 style="text-align: center; text-decoration: underline;">Ažuriranje utrke</h3>
            <form novalidate class="form" method="get" id="formUtrkeA" name="formUtrkeA" action="utrke.php">
                
                <label style="margin-bottom: 5px;" for="idA">ID utrke: </label>
                <input class="tbKorimePrijava" style="margin-left: 152px;" 
                        <?php if (isset($_GET['utrkaid'])) {
                           global $dohvaceniId;
                           echo "value=$dohvaceniId";
                       } ?>
                       type="text" id="idA" name="idA" size="30" maxlength="30"  autofocus="autofocus" required="required"><br>
                
                <label style="margin-bottom: 5px;" for="nazivA">Naziv: </label>
                <input class="tbKorimePrijava" style="margin-left: 168px;"
                       <?php if (isset($_GET['utrkaid'])) {
                           global $dohvaceniNaziv;
                           echo "value='$dohvaceniNaziv'";
                       } ?>
                       type="text" id="nazivA" name="nazivA" size="30" maxlength="30" autofocus="autofocus" required="required"><br>
                
                <label style="margin-bottom: 5px;" for="vrijemeZavrsetkaPrijavaA">Vrijeme završetka prijava: </label>
                <input class="tbKorimePrijava" type="text"
                       <?php if (isset($_GET['utrkaid'])) {
                           global $dohvacenoVrijemeZavrsetkaPrijava;
                           echo "value='$dohvacenoVrijemeZavrsetkaPrijava'";
                       } ?>
                       id="vrijemeZavrsetkaPrijavaA" name="vrijemeZavrsetkaPrijavaA" size="30" maxlength="30" required="required"><br>
                
                <label style="margin-bottom: 5px;" for="zakljucanaA">Zaključana (1-DA, 0-NE): </label>
                <input class="tbKorimePrijava" style="margin-left: 34px;"
                       <?php if (isset($_GET['utrkaid'])) {
                           global $dohvacenoZakljucano;
                           echo "value=$dohvacenoZakljucano";
                       } ?>
                       type="text" id="zakljucanaA" name="zakljucanaA" size="30" maxlength="30" autofocus="autofocus" required="required"><br>
                
                <label style="margin-bottom: 5px;" for="odaberiDrzavuA">Država: </label>
                <select class="tbKorimePrijava" style="width: 305px; height:30px; margin-left: 157px;" name="odaberiDrzavuA" id="odaberiDrzavuA">
                    <option style='text-align:center;' value='nijeOdabranaDrzavaA'>--Odaberite državu--</option>
                     <?php
                     $baza = new Baza();
                     $baza->spojiDB();
                     $sqlUpitUtrka = "SELECT drzava_id, drzava.naziv FROM drzava";
                     $rezultatUtrka = $baza->selectDB($sqlUpitUtrka);
                     $baza->zatvoriDB();
                     while ($zapis = mysqli_fetch_array($rezultatUtrka)) {
                         echo "<option style='text-align:center;' value='$zapis[0]'>$zapis[1]</option>";
                     }
                     ?>                                                        
                    </select><br>
            </form>

            <div style="text-align:center;">
                <input form="formUtrkeA" name="btnAzurirajUtrku" type="submit" class="submit" value=" Ažuriraj ">
            </div>
            
            <hr>
            
            <?php
                echo "<table>
            <caption>Utrke</caption>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Naziv</th>
                    <th>Vrijeme završetka prijava</th>
                    <th>Država</th>
                    <th>Zaključana</th>
                    <th>Ažuriranje</th>
                </tr>
            </thead>
            <tbody>";
            ?>
            
            <?php
            $baza = new Baza();
            $baza->spojiDB();
            $sqlUpit = "SELECT utrka.utrka_id, utrka.naziv, utrka.vrijeme_zavrsetka_prijava, drzava.naziv, utrka.zakljucana "
                    . "FROM utrka, drzava WHERE utrka.drzava = drzava.drzava_id";
            $rezultat = $baza->selectDB($sqlUpit);

            $baza->zatvoriDB();

            while ($zapis = mysqli_fetch_array($rezultat)) {
                echo "<tr>";
                echo "<td>{$zapis[0]}</td>";
                echo "<td>{$zapis[1]}</td>";
                echo "<td>{$zapis[2]}</td>";
                echo "<td>{$zapis[3]}</td>";
                if ($zapis[4] == 1) {
                    echo "<td style='background-color:#D24D57;'>Zaključana</td>";
                } else {
                    echo "<td style='background-color:#26A65B;'>Nije zaključana</td>";
                }
                echo "<td><a class='utrkaAzuriraj' href='utrke.php?utrkaid={$zapis['utrka_id']}'>Ažuriraj utrku</a></td>";
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

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

if (isset($_SESSION["uloga"]) && ($_SESSION["uloga"] < 3)) {
    header("Location: obrasci/prijava.php");
    unset($_COOKIE['autenticiran']);
    setcookie('autenticiran', null, -1, '/');
    Sesija::obrisiSesiju();
    exit();
}

if (isset($_GET['btnKreirajEtapu'])) {
    if ($_GET['datumIVrijemePocetkaEtape'] == NULL || $_GET['naziv'] == "" || $_GET['odaberiUtrku'] == "nijeOdabranaUtrka") {
        $porukaEtape = "Potrebno je popuniti sva polja!";
    } else {
        $naziv = $_GET['naziv'];
        $datumIVrijemePocetkaEtape = $_GET['datumIVrijemePocetkaEtape'];
        $formatiranoDatumIVrijemePocetkaEtape = date("Y-m-d H:i:s", strtotime($datumIVrijemePocetkaEtape));
        $IDUtrke = $_GET['odaberiUtrku'];
        
        $baza = new Baza();
        $baza->spojiDB();
        $sqlUpit = "INSERT INTO etapa (naziv, datum_i_vrijeme, zakljucana, utrka)"
                . "VALUES ('$naziv', '$formatiranoDatumIVrijemePocetkaEtape', 0, $IDUtrke)";

        $rezultat = $baza->insertDB($sqlUpit);
        if ($rezultat) {
            $porukaEtape = "Etapa kreirana.";
        } else {
            $porukaEtape = "Greška prilikom kreiranja etape.";
        }
        $baza->zatvoriDB();
    }
}

function dohvatiPodatke(){
    global $dohvaceniId;
    global $dohvaceniNaziv;
    global $dohvaceniDatumIVrijeme;
    global $dohvacenoZakljucano;
    
    $baza = new Baza();
    $baza->spojiDB();
    
    $dohvaceniId = $_GET['etapaid'];
    
    $sqlUpit = "SELECT etapa_id, naziv, datum_i_vrijeme, zakljucana FROM etapa WHERE etapa_id = $dohvaceniId";
    
    $rezultat = $baza->selectDB($sqlUpit);  
    $red = mysqli_fetch_array($rezultat);
    
    $baza->zatvoriDB();
    
    $dohvaceniId = $red['etapa_id'];
    $dohvaceniNaziv = $red['naziv'];
    $dohvaceniDatumIVrijeme = $red['datum_i_vrijeme'];
    $dohvacenoZakljucano = $red['zakljucana'];
}
dohvatiPodatke();

if (isset($_GET['btnAzurirajEtapu'])) {
    if ($_GET['idA'] == "" || $_GET['nazivA'] == "" || $_GET['datumIVrijemePocetkaEtapeA'] == "" || $_GET['odaberiUtrkuA'] == "nijeOdabranaUtrkaA" || $_GET['zakljucanaA'] == "") {
        $porukaEtapeA = "Odaberite utrku koju želite ažurirati!";
    } else {

        $noviId =  $_GET['idA'];
        $noviNaziv = $_GET['nazivA'];
        $noviDatumIVrijeme = $_GET['datumIVrijemePocetkaEtapeA'];
        $novoZakljucana = $_GET['zakljucanaA'];
        $novaUtrkaID = $_GET['odaberiUtrkuA'];

        $baza = new Baza();
        $baza->spojiDB();

        $sqlUpitAzuriraj = "UPDATE etapa SET etapa_id = $noviId, naziv = '$noviNaziv', datum_i_vrijeme = '$noviDatumIVrijeme', zakljucana = $novoZakljucana, utrka = '$novaUtrkaID' WHERE etapa_id = $noviId";

        $rezultatAzuriraj = $baza->updateDB($sqlUpitAzuriraj);

        if ($rezultatAzuriraj) {
            $porukaEtapeA = "Etapa ažurirana.";
        }else{
            $porukaEtapeA = "Greška prilikom ažuriranja etape.";
        }
        $baza->zatvoriDB();
    }
}
?>
<!DOCTYPE html>
<html lang="hr">
    <head>
        <title>Etape - Trčanje</title>
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

            <h1 class="naslovStranice">ETAPE</h1>
            <?php
            global $porukaEtape;
            global $porukaEtapeA;
            echo "<p style = 'color:red; font-weight:bold; text-align:center;'>$porukaEtape</p>";
            echo "<p style = 'color:red; font-weight:bold; text-align:center;'>$porukaEtapeA</p>";
            ?>
        </header>         
        <hr>
        <section>
            
            <h3 style="text-align: center; text-decoration: underline;">Kreiranje etape</h3>
            <form novalidate class="form" method="get" id="formEtape" name="formEtape" action="etape.php">

                <label style="margin-bottom: 5px;" for="naziv">Naziv: </label>
                <input class="tbKorimePrijava" style="margin-left: 202px;" type="text" id="naziv" name="naziv" size="30" maxlength="30" placeholder="naziv" autofocus="autofocus" required="required"><br>
                
                <label style="margin-bottom: 5px;" for="datumIVrijemePocetkaEtape">Datum i vrijeme početka etape: </label>
                <input class="tbKorimePrijava" style="margin-left: 27px;" type="datetime-local" id="datumIVrijemePocetkaEtape" name="datumIVrijemePocetkaEtape" size="30" maxlength="30" required="required"><br>
                
                <label style="margin-bottom: 5px;" for="odaberiUtrku">Utrka: </label>
                <select class="tbKorimePrijava" style="width: 305px; height:30px; margin-left: 204px;" name="odaberiUtrku" id="odaberiUtrku">
                    <option style='text-align:center;' value='nijeOdabranaUtrka'>--Odaberite utrku--</option>
                     <?php
                     $baza = new Baza();
                     $baza->spojiDB();
                     
                     $korisnickoImeKorisnika = $_SESSION['korisnik'];
                     $sqlUpitDohvatiID = "SELECT korisnik_id FROM korisnik WHERE korisnicko_ime = '$korisnickoImeKorisnika'";
                     $rezultatDohvatiID = $baza->selectDB($sqlUpitDohvatiID);
                     $zapisID = mysqli_fetch_array($rezultatDohvatiID);
                     $prijavljeniKorisnikID = $zapisID['korisnik_id'];
                     
                     $sqlUpitEtapa = "SELECT utrka.utrka_id, utrka.naziv, drzava.naziv, utrka.zakljucana "
                             . "FROM utrka INNER JOIN drzava ON utrka.drzava = drzava.drzava_id "
                             . "INNER JOIN moderator_drzava ON drzava.drzava_id = moderator_drzava.drzava "
                             . "AND moderator_drzava.korisnik = $prijavljeniKorisnikID "
                             . "AND utrka.zakljucana = 0";
                     $rezultatEtapa = $baza->selectDB($sqlUpitEtapa);
                     $baza->zatvoriDB();
                     while ($zapis = mysqli_fetch_array($rezultatEtapa)) {
                         echo "<option style='text-align:center;' value='$zapis[0]'>$zapis[1] | $zapis[2]</option>";
                     }
                     ?>                                                        
                    </select><br>
            </form>

            <div style="text-align:center;">
                <input form="formEtape" name="btnKreirajEtapu" type="submit" class="submit" value=" Kreiraj ">
            </div>
            
            <hr>
            
            <h3 style="text-align: center; text-decoration: underline;">Ažuriranje etape</h3>
            <form novalidate class="form" method="get" id="formEtapeA" name="formEtapeA" action="etape.php">
                
                <label style="margin-bottom: 5px;" for="idA">ID etape: </label>
                <input class="tbKorimePrijava" style="margin-left: 181px;" 
                        <?php if (isset($_GET['etapaid'])) {
                           global $dohvaceniId;
                           echo "value=$dohvaceniId";
                       } ?>
                       type="text" id="idA" name="idA" size="30" maxlength="30"  autofocus="autofocus" required="required"><br>
                
                <label style="margin-bottom: 5px;" for="nazivA">Naziv: </label>
                <input class="tbKorimePrijava" style="margin-left: 202px;" 
                       <?php if (isset($_GET['etapaid'])) {
                           global $dohvaceniNaziv;
                           echo "value='$dohvaceniNaziv'";
                       } ?>
                       type="text" id="nazivA" name="nazivA" size="30" maxlength="30" autofocus="autofocus" required="required"><br>
                
                <label style="margin-bottom: 5px;" for="datumIVrijemePocetkaEtapeA">Datum i vrijeme početka etape: </label>
                <input class="tbKorimePrijava" style="margin-left: 26px;" 
                       <?php if (isset($_GET['etapaid'])) {
                           global $dohvaceniDatumIVrijeme;
                           echo "value='$dohvaceniDatumIVrijeme'";
                       } ?>
                       type="text" id="datumIVrijemePocetkaEtapeA" name="datumIVrijemePocetkaEtapeA" size="30" maxlength="30" required="required"><br>
                
                <label style="margin-bottom: 5px;" for="zakljucanaA">Zaključana (1-DA, 0-NE): </label>
                <input class="tbKorimePrijava" style="margin-left: 68px;"
                       <?php if (isset($_GET['etapaid'])) {
                           global $dohvacenoZakljucano;
                           echo "value=$dohvacenoZakljucano";
                       } ?>
                       type="text" id="zakljucanaA" name="zakljucanaA" size="30" maxlength="30" autofocus="autofocus" required="required"><br>
                
                <label style="margin-bottom: 5px;" for="odaberiUtrkuA">Utrka: </label>
                <select class="tbKorimePrijava" style="width: 305px; height:30px; margin-left: 204px;" name="odaberiUtrkuA" id="odaberiUtrkuA">
                    <option style='text-align:center;' value='nijeOdabranaUtrkaA'>--Odaberite utrku--</option>
                     <?php
                     $baza = new Baza();
                     $baza->spojiDB();
                                        
                     $sqlUpitEtapaA = "SELECT utrka.utrka_id, utrka.naziv, drzava.naziv, utrka.zakljucana "
                             . "FROM utrka INNER JOIN drzava ON utrka.drzava = drzava.drzava_id "
                             . "INNER JOIN moderator_drzava ON drzava.drzava_id = moderator_drzava.drzava "
                             . "AND moderator_drzava.korisnik = $prijavljeniKorisnikID "
                             . "AND utrka.zakljucana = 0";
                     $rezultatEtapaA = $baza->selectDB($sqlUpitEtapaA);
                     $baza->zatvoriDB();
                     while ($zapis = mysqli_fetch_array($rezultatEtapaA)) {
                         echo "<option style='text-align:center;' value='$zapis[0]'>$zapis[1] | $zapis[2]</option>";
                     }
                     ?>                                                        
                    </select><br>
            </form>

            <div style="text-align:center;">
                <input form="formEtapeA" name="btnAzurirajEtapu" type="submit" class="submit" value=" Ažuriraj ">
            </div>
            
            <hr>
            
            <?php
            $baza = new Baza();
            $baza->spojiDB();

            echo "<table>";
            echo "<caption>Popis etapa</caption>";
            echo "<thead>
                <tr>
                    <th>ID etape</th>
                    <th>Naziv etape</th>
                    <th>Datum i vrijeme početka etape</th>
                    <th>Zaključana</th>
                    <th>Naziv utrke</th>
                    <th>Država u kojoj se odvija utrka</th>
                    <th>Ažuriranje</th>
                </tr>
            </thead>
            <tbody>";
            $sqlUpit = "SELECT etapa.etapa_id, etapa.naziv, etapa.datum_i_vrijeme, etapa.zakljucana, utrka.naziv, drzava.naziv "
                    . "FROM utrka, etapa, drzava, moderator_drzava "
                    . "WHERE drzava.drzava_id = utrka.drzava "
                    . "AND utrka.utrka_id = etapa.utrka "
                    . "AND drzava.drzava_id = moderator_drzava.drzava "
                    . "AND moderator_drzava.korisnik = $prijavljeniKorisnikID ORDER BY etapa.utrka ASC, etapa.datum_i_vrijeme ASC";
            $rezultat = $baza->selectDB($sqlUpit);
            while ($zapis = mysqli_fetch_array($rezultat)) {
                echo "<tr>";
                echo "<td>{$zapis[0]}</td>";
                echo "<td>{$zapis[1]}</td>";
                echo "<td>{$zapis[2]}</td>";
                if ($zapis[3] == 1) {
                    echo "<td style='background-color:#D24D57;'>Zaključana</td>";
                } else {
                    echo "<td style='background-color:#26A65B;;'>Nije zaključana</td>";
                }
                echo "<td>{$zapis[4]}</td>";
                echo "<td>{$zapis[5]}</td>";
                echo "<td><a class='etapaAzuriraj' href='etape.php?etapaid={$zapis[0]}'>Ažuriraj etapu</a></td>";
                echo "</tr>";
            }
            echo "</tbody>";
            echo "</table>";
            $baza->zatvoriDB();
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


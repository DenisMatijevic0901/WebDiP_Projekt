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

if (isset($_GET['btnEvidentiraj'])) {
    if ($_GET['odaberiKorisnika'] == "nijeOdabranKorisnik" || $_GET['odaberiEtapu'] == "nijeOdabranaEtapa" || $_GET['evidentiranoVrijeme'] == "") {
        $porukaEvidentiranje = "Potrebno je popuniti sva polja!";
    } else {
        $korisnikID = $_GET['odaberiKorisnika'];
        $etapaID = $_GET['odaberiEtapu'];
        $evidentiranoVrijeme = $_GET['evidentiranoVrijeme'];

        $baza = new Baza();
        $baza->spojiDB();

        $sqlUpitProvjeraRezultata = "SELECT * FROM rezultat_etape WHERE korisnik = $korisnikID AND etapa = $etapaID";
        $rezultatProvjeraRezultata = $baza->selectDB($sqlUpitProvjeraRezultata);
        $zapisProvjeraRezultata = mysqli_fetch_array($rezultatProvjeraRezultata);

        if ($zapisProvjeraRezultata != NULL) {
            $porukaEvidentiranje = "Rezultat za izabranog korisnika i etapu već postoji!";
        } else {
            $sqlUpit = "INSERT INTO rezultat_etape (korisnik, etapa, evidentirano_vrijeme, odustao) "
                    . "VALUES ($korisnikID, $etapaID, '$evidentiranoVrijeme', 0)";

            $rezultat = $baza->insertDB($sqlUpit);
            if ($rezultat) {
                $porukaEvidentiranje = "Vrijeme uspješno evidentirano.";
            } else {
                $porukaEvidentiranje = "Greška prilikom evidentiranja vremena.";
            }
        }
        $baza->zatvoriDB();
    }
}

if (isset($_GET['etapaid'])) {
    $baza = new Baza();
    $baza->spojiDB();

    $etapaID = $_GET['etapaid'];
    $utrkaID = $_GET['utrkaid'];

    $sqlUpitProvjeraBrojaPrijava = "SELECT COUNT(*) FROM prijava WHERE prijava.utrka = $utrkaID";
    $rezultatProvjeraBrojaPrijava = $baza->selectDB($sqlUpitProvjeraBrojaPrijava);
    $zapisProvjeraBrojaPrijava = mysqli_fetch_array($rezultatProvjeraBrojaPrijava);

    $sqlUpitProvjeraBrojaRezultataEtape = "SELECT COUNT(*) FROM rezultat_etape WHERE etapa = $etapaID";
    $rezultatProvjeraBrojaRezultataEtape = $baza->selectDB($sqlUpitProvjeraBrojaRezultataEtape);
    $zapisProvjeraBrojaRezultataEtape = mysqli_fetch_array($rezultatProvjeraBrojaRezultataEtape);

    if ($zapisProvjeraBrojaPrijava[0] == $zapisProvjeraBrojaRezultataEtape[0]) {
        $sqlUpitZakljucajEtapu = "UPDATE etapa SET zakljucana = 1 WHERE etapa_id = $etapaID";
        $rezultatZakljucajEtapu = $baza->updateDB($sqlUpitZakljucajEtapu);

        if ($rezultatZakljucajEtapu) {
            $porukaEvidentiranje = "Etapa uspješno zaključana";
        } else {
            $porukaEvidentiranje = "Greška prilikom zaključavanja etape.";
        }
    } else {
        $porukaEvidentiranje = "Nije moguće zaključati etapu jer nisu evidentirani rezultati za sve natjecatelje etape s rednim brojem $etapaID!";
    }
    $baza->zatvoriDB();
}
?>
<!DOCTYPE html>
<html lang="hr">
    <head>
        <title>Evidentiranje vremena - Trčanje</title>
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

            <h1 class="naslovStranice" style="font-size: 80px;">EVIDENTIRANJE VREMENA I ZAKLJUČAVANJE ETAPA</h1>
            <?php
            global $porukaEvidentiranje;
            echo "<p style = 'color:red; font-weight:bold; text-align:center;'>$porukaEvidentiranje</p>";
            ?>
        </header>         
        <hr>
        <section>

            <h3 style="text-align: center; text-decoration: underline;">Evidentiranje vremena</h3>
            <form novalidate class="form" method="get" id="formEvidencija" name="formEvidencija" action="evidentiranje_vremena.php">

                <label style="margin-bottom: 5px;" for="odaberiKorisnika">Korisnik i utrka: </label>
                <select class="tbKorimePrijava" style="width: 305px; height:30px; margin-left: 91px;" name="odaberiKorisnika" id="odaberiKorisnika">
                    <option style='text-align:center;' value='nijeOdabranKorisnik'>--Odaberite korisnika--</option>
                    <?php
                    $baza = new Baza();
                    $baza->spojiDB();

                    $korisnickoImeKorisnika = $_SESSION['korisnik'];
                    $sqlUpitDohvatiID = "SELECT korisnik_id FROM korisnik WHERE korisnicko_ime = '$korisnickoImeKorisnika'";
                    $rezultatDohvatiID = $baza->selectDB($sqlUpitDohvatiID);
                    $zapisID = mysqli_fetch_array($rezultatDohvatiID);
                    $prijavljeniKorisnikID = $zapisID['korisnik_id'];

                    $sqlUpitKorisnik = "SELECT korisnik.korisnik_id, korisnik.ime, korisnik.prezime, utrka.naziv, drzava.naziv "
                            . "FROM korisnik, prijava, utrka, drzava, moderator_drzava "
                            . "WHERE korisnik.korisnik_id = prijava.korisnik "
                            . "AND prijava.utrka = utrka.utrka_id "
                            . "AND utrka.drzava = drzava.drzava_id "
                            . "AND drzava.drzava_id = moderator_drzava.drzava "
                            . "AND moderator_drzava.korisnik = $prijavljeniKorisnikID ORDER BY korisnik.ime ASC, korisnik.prezime ASC";
                    $rezultatKorisnik = $baza->selectDB($sqlUpitKorisnik);
                    $baza->zatvoriDB();
                    while ($zapisKorisnik = mysqli_fetch_array($rezultatKorisnik)) {
                        echo "<option style='text-align:center;' value='$zapisKorisnik[0]'>$zapisKorisnik[1] $zapisKorisnik[2] | $zapisKorisnik[3] | $zapisKorisnik[4]</option>";
                    }
                    ?>                                                        
                </select><br>

                <label style="margin-bottom: 5px;" for="odaberiEtapu">Etapa: </label>
                <select class="tbKorimePrijava" style="width: 305px; height:30px; margin-left: 154px;" name="odaberiEtapu" id="odaberiEtapu">
                    <option style='text-align:center;' value='nijeOdabranaEtapa'>--Odaberite etapu--</option>
                    <?php
                    $baza = new Baza();
                    $baza->spojiDB();

                    $sqlUpitOdaberiEtapu = "SELECT etapa.etapa_id, etapa.naziv, utrka.naziv,drzava.naziv "
                            . "FROM etapa, utrka, drzava, moderator_drzava "
                            . "WHERE etapa.utrka = utrka.utrka_id "
                            . "AND utrka.drzava = drzava.drzava_id "
                            . "AND drzava.drzava_id = moderator_drzava.drzava "
                            . "AND moderator_drzava.korisnik = $prijavljeniKorisnikID ORDER BY utrka.utrka_id, etapa.naziv ASC";
                    $rezultatOdaberiEtapu = $baza->selectDB($sqlUpitOdaberiEtapu);
                    $baza->zatvoriDB();
                    while ($zapisOdaberiEtapu = mysqli_fetch_array($rezultatOdaberiEtapu)) {
                        echo "<option style='text-align:center;' value='$zapisOdaberiEtapu[0]'>$zapisOdaberiEtapu[1] | $zapisOdaberiEtapu[2] | $zapisOdaberiEtapu[3]</option>";
                    }
                    ?>                                                        
                </select><br>

                <label style="margin-bottom: 5px;" for="evidentiranoVrijeme">Evidentirano vrijeme: </label>
                <input class="tbKorimePrijava" style="margin-left: 52px;" type="text" id="evidentiranoVrijeme" name="evidentiranoVrijeme" placeholder="hh:mm:ss" size="30" maxlength="30" required="required"><br>

            </form>

            <div style="text-align:center;">
                <input form="formEvidencija" name="btnEvidentiraj" type="submit" class="submit" value=" Evidentiraj ">
            </div>

            <hr>

            <?php
            $baza = new Baza();
            $baza->spojiDB();

            echo "<table>";
            echo "<caption>Popis evidentiranih vremena</caption>";
            echo "<thead>
                <tr>
                    <th>Ime</th>
                    <th>Prezime</th>
                    <th>Država u kojoj se odvija utrka</th>
                    <th>Naziv utrke</th>
                    <th>Naziv etape</th>
                    <th>Evidentirano vrijeme</th>
                </tr>
            </thead>
            <tbody>";
            $sqlUpit = "SELECT korisnik.ime, korisnik.prezime, utrka.naziv, etapa.naziv, rezultat_etape.evidentirano_vrijeme, drzava.naziv "
                    . "FROM korisnik, rezultat_etape, etapa, utrka, drzava "
                    . "WHERE korisnik.korisnik_id = rezultat_etape.korisnik "
                    . "AND drzava.drzava_id = utrka.drzava "
                    . "AND rezultat_etape.etapa = etapa.etapa_id "
                    . "AND etapa.utrka = utrka.utrka_id "
                    . "ORDER BY korisnik.ime ASC, utrka.naziv ASC, etapa.naziv ASC";
            $rezultat = $baza->selectDB($sqlUpit);
            while ($zapis = mysqli_fetch_array($rezultat)) {
                echo "<tr>";
                echo "<td>{$zapis[0]}</td>";
                echo "<td>{$zapis[1]}</td>";
                echo "<td>{$zapis[5]}</td>";
                echo "<td>{$zapis[2]}</td>";
                echo "<td>{$zapis[3]}</td>";
                if ($zapis[4] == "00:00:00") {
                    echo "<td style='background-color:#D24D57; font-weight:bold;'>Odustao</td>";
                } else {
                    echo "<td style='font-weight:bold; background-color:#26A65B'>{$zapis[4]}</td>";
                }
                echo "</tr>";
            }
            echo "</tbody>";
            echo "</table>";
            $baza->zatvoriDB();
            ?>

            <hr>

            <?php
            $baza = new Baza();
            $baza->spojiDB();

            echo "<table>";
            echo "<caption>Zaključavanje etapa</caption>";
            echo "<thead>
                <tr>
                    <th>ID etape</th>
                    <th>Naziv etape</th>
                    <th>Datum i vrijeme početka etape</th>
                    <th>Zaključana</th>
                    <th>Naziv utrke</th>
                    <th>Država u kojoj se odvija utrka</th>
                    <th>Zaključavanje</th>
                </tr>
            </thead>
            <tbody>";
            $sqlUpit = "SELECT etapa.etapa_id, etapa.naziv, etapa.datum_i_vrijeme, etapa.zakljucana, utrka.naziv, drzava.naziv, utrka.utrka_id "
                    . "FROM utrka, etapa, drzava, moderator_drzava "
                    . "WHERE drzava.drzava_id = utrka.drzava "
                    . "AND utrka.utrka_id = etapa.utrka "
                    . "AND drzava.drzava_id = moderator_drzava.drzava "
                    . "AND moderator_drzava.korisnik = $prijavljeniKorisnikID ORDER BY etapa.utrka ASC, etapa.naziv ASC, etapa.datum_i_vrijeme ASC";
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
                if ($zapis[3] == 1) {
                    echo "<td style='background-color:#D24D57;'>Zaključana</td>";
                } else {
                    echo "<td><a class='etapaZakljucaj' href='evidentiranje_vremena.php?etapaid={$zapis[0]}&utrkaid={$zapis[6]}'>Zaključaj etapu</a></td>";
                }
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


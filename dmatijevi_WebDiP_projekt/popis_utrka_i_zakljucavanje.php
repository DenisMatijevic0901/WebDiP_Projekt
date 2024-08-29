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

if (isset($_GET['utrkaid'])) {
    $baza = new Baza();
    $baza->spojiDB();

    $utrkaID = $_GET['utrkaid'];

    $sqlUpitBrojEtapaUtrke = "SELECT COUNT(*) FROM etapa WHERE etapa.utrka = $utrkaID";
    $rezultatBrojEtapaUtrke = $baza->selectDB($sqlUpitBrojEtapaUtrke);
    $zapisBrojEtapaUtrke = mysqli_fetch_array($rezultatBrojEtapaUtrke);

    $sqlUpitBrojZakljucanihEtapa = "SELECT COUNT(*) FROM etapa WHERE etapa.utrka = $utrkaID AND etapa.zakljucana = 1";
    $rezultatBrojZakljucanihEtapa = $baza->selectDB($sqlUpitBrojZakljucanihEtapa);
    $zapisBrojZakljucanihEtapa = mysqli_fetch_array($rezultatBrojZakljucanihEtapa);

    if ($zapisBrojEtapaUtrke[0] == $zapisBrojZakljucanihEtapa[0]) {
        $sqlUpitZakljucajUtrku = "UPDATE utrka SET zakljucana = 1 WHERE utrka_id = $utrkaID";
        $rezultatZakljucajUtrku = $baza->updateDB($sqlUpitZakljucajUtrku);

        if ($rezultatZakljucajUtrku) {
            $porukaZakljucajUtrku = "Utrka uspješno zaključana";
            $sqlUpitPobjednik = "SELECT korisnik.korisnik_id, korisnik.ime, korisnik.prezime, SUM(rezultat_etape.ostvareni_bodovi) "
                    . "FROM korisnik, rezultat_etape, etapa "
                    . "WHERE korisnik.korisnik_id = rezultat_etape.korisnik "
                    . "AND rezultat_etape.etapa = etapa.etapa_id "
                    . "AND etapa.utrka = $utrkaID "
                    . "GROUP BY korisnik.korisnik_id "
                    . "ORDER BY 4 DESC LIMIT 1";
            $rezultatPobjednik = $baza->selectDB($sqlUpitPobjednik);
            $zapisPobjednik = mysqli_fetch_array($rezultatPobjednik);
            
            $sqlUnesiPobjednika = "INSERT INTO pobjednik (utrka, korisnik) VALUES ($utrkaID, $zapisPobjednik[0])";
            $rezultatUnesiPobjednika = $baza->selectDB($sqlUnesiPobjednika);
        } else {
            $porukaZakljucajUtrku = "Greška prilikom zaključavanja utrke.";
        }
    } else {
        $porukaZakljucajUtrku = "Nije moguće zaključati utrku jer nisu sve etape utrke zaključane!";
    }
    $baza->zatvoriDB();
}

if (isset($_GET['etapaid'])) {
    $baza = new Baza();
    $baza->spojiDB();

    $dohvaceniIDEtape = $_GET['etapaid'];

    $sqlUpitDohvatiPrvog = "SELECT korisnik_id, korisnik.ime, korisnik.prezime, rezultat_etape.evidentirano_vrijeme "
            . "FROM korisnik, rezultat_etape "
            . "WHERE korisnik.korisnik_id = rezultat_etape.korisnik "
            . "AND rezultat_etape.etapa = $dohvaceniIDEtape "
            . "AND rezultat_etape.odustao = 0 "
            . "ORDER BY rezultat_etape.evidentirano_vrijeme ASC LIMIT 1";
    $rezultatDohvatiPrvog = $baza->selectDB($sqlUpitDohvatiPrvog);
    $zapisDohvatiPrvog = mysqli_fetch_array($rezultatDohvatiPrvog);

    $sqlUpitDohvatiDrugog = "SELECT korisnik_id, korisnik.ime, korisnik.prezime, rezultat_etape.evidentirano_vrijeme "
            . "FROM korisnik, rezultat_etape "
            . "WHERE korisnik.korisnik_id = rezultat_etape.korisnik "
            . "AND rezultat_etape.etapa = $dohvaceniIDEtape "
            . "AND rezultat_etape.odustao = 0 "
            . "ORDER BY rezultat_etape.evidentirano_vrijeme ASC LIMIT 1,1";
    $rezultatDohvatiDrugog = $baza->selectDB($sqlUpitDohvatiDrugog);
    $zapisDohvatiDrugog = mysqli_fetch_array($rezultatDohvatiDrugog);

    $sqlUpitDohvatiTreceg = "SELECT korisnik_id, korisnik.ime, korisnik.prezime, rezultat_etape.evidentirano_vrijeme "
            . "FROM korisnik, rezultat_etape "
            . "WHERE korisnik.korisnik_id = rezultat_etape.korisnik "
            . "AND rezultat_etape.etapa = $dohvaceniIDEtape "
            . "AND rezultat_etape.odustao = 0 "
            . "ORDER BY rezultat_etape.evidentirano_vrijeme ASC LIMIT 2,1";
    $rezultatDohvatiTreceg = $baza->selectDB($sqlUpitDohvatiTreceg);
    $zapisDohvatiTreceg = mysqli_fetch_array($rezultatDohvatiTreceg);

    $sql1 = "UPDATE rezultat_etape SET ostvareni_bodovi = 100 WHERE korisnik = $zapisDohvatiPrvog[0] AND etapa = $dohvaceniIDEtape";
    $rezultat1 = $baza->selectDB($sql1);
    $sql2 = "UPDATE rezultat_etape SET ostvareni_bodovi = 50 WHERE korisnik = $zapisDohvatiDrugog[0] AND etapa = $dohvaceniIDEtape";
    $rezultat2 = $baza->selectDB($sql2);
    $sql3 = "UPDATE rezultat_etape SET ostvareni_bodovi = 10 WHERE korisnik = $zapisDohvatiTreceg[0] AND etapa = $dohvaceniIDEtape";
    $rezultat3 = $baza->selectDB($sql3);
    $baza->zatvoriDB();
}
?>
<!DOCTYPE html>
<html lang="hr">
    <head>
        <title>Popis utrka i zaključavanje - Trčanje</title>
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

            <h1 class="naslovStranice" style="font-size: 60px;">POPIS UTRKA I ZAKLJUČAVANJE UTRKA</h1>
            <?php
            global $porukaZakljucajUtrku;
            echo "<p style = 'color:red; font-weight:bold; text-align:center;'>$porukaZakljucajUtrku</p>";
            ?>
        </header>         
        <hr>
        <section>      

            <?php
            $baza = new Baza();
            $baza->spojiDB();

            echo "<table>";
            echo "<caption>Popis utrka</caption>";
            echo "<thead>
                <tr>
                    <th>ID utrke</th>
                    <th>Naziv utrke</th>
                    <th>Vrijeme završetka prijava</th>
                    <th>Naziv države u kojoj se odvija utrka</th>
                    <th>Zaključana</th>
                    <th>Zaključavanje utrke / Proglašavanje pobjednika</th>
                </tr>
            </thead>
            <tbody>";
            $korisnickoImeKorisnika = $_SESSION['korisnik'];
            $sqlUpitDohvatiID = "SELECT korisnik_id FROM korisnik WHERE korisnicko_ime = '$korisnickoImeKorisnika'";
            $rezultatDohvatiID = $baza->selectDB($sqlUpitDohvatiID);
            $zapisID = mysqli_fetch_array($rezultatDohvatiID);
            $prijavljeniKorisnikID = $zapisID['korisnik_id'];

            $sqlUpit = "SELECT utrka.utrka_id, utrka.naziv, utrka.vrijeme_zavrsetka_prijava, utrka.zakljucana, drzava.naziv "
                    . "FROM utrka, drzava, moderator_drzava "
                    . "WHERE utrka.drzava = drzava.drzava_id "
                    . "AND drzava.drzava_id = moderator_drzava.drzava "
                    . "AND moderator_drzava.korisnik = $prijavljeniKorisnikID "
                    . "ORDER BY utrka.naziv";
            $rezultat = $baza->selectDB($sqlUpit);

            while ($zapis = mysqli_fetch_array($rezultat)) {
                echo "<tr>";
                echo "<td>{$zapis[0]}</td>";
                echo "<td>{$zapis[1]}</td>";
                echo "<td>{$zapis[2]}</td>";
                echo "<td>{$zapis[4]}</td>";
                if ($zapis[3] == 1) {
                    echo "<td style='background-color:#D24D57;'>Zaključana</td>";
                } else {
                    echo "<td style='background-color:#26A65B;;'>Nije zaključana</td>";
                }
                if ($zapis[3] == 1) {
                    echo "<td style='background-color:#D24D57;'>Zaključana</td>";
                } else {
                    echo "<td><a class='utrkaZakljucaj' href='popis_utrka_i_zakljucavanje.php?utrkaid={$zapis[0]}'>Zaključaj utrku</a></td>";
                }
                echo "</tr>";
            }
            echo "</tbody>";
            echo "</table>";
            $baza->zatvoriDB();
            ?>

            <?php
            $baza = new Baza();
            $baza->spojiDB();

            echo "<table>";
            echo "<caption>Bodovno stanje</caption>";
            echo "<thead>
                <tr>
                    <th>Ime</th>
                    <th>Prezime</th>
                    <th>Naziv države</th>
                    <th>Naziv utrke</th>
                    <th>Naziv etape</th>
                    <th>Evidentirano vrijeme</th>
                    <th>Odustao / Završio</th>
                    <th>Bodovi</th>
                </tr>
            </thead>
            <tbody>";

            $sqlUpit = "SELECT korisnik.ime, korisnik.prezime, drzava.naziv, utrka.naziv, etapa.naziv, "
                    . "rezultat_etape.ostvareni_bodovi,rezultat_etape.evidentirano_vrijeme, rezultat_etape.odustao "
                    . "FROM drzava, utrka, korisnik, rezultat_etape, etapa "
                    . "WHERE korisnik.korisnik_id = rezultat_etape.korisnik "
                    . "AND rezultat_etape.etapa = etapa.etapa_id "
                    . "AND utrka.utrka_id = etapa.utrka "
                    . "AND drzava.drzava_id = utrka.drzava "
                    . "ORDER BY korisnik.ime ASC, utrka.utrka_id ASC";
            $rezultat = $baza->selectDB($sqlUpit);

            while ($zapis = mysqli_fetch_array($rezultat)) {
                echo "<tr>";
                echo "<td>{$zapis[0]}</td>";
                echo "<td>{$zapis[1]}</td>";
                echo "<td>{$zapis[2]}</td>";
                echo "<td>{$zapis[3]}</td>";
                echo "<td>{$zapis[4]}</td>";
                echo "<td>{$zapis[6]}</td>";
                if ($zapis[7] == 1) {
                    echo "<td style='background-color:#D24D57;'>Odustao</td>";
                } else {
                echo "<td style='background-color:#26A65B;;'>Završio</td>";              
                }           
                if ($zapis[5] == 100) {
                    echo "<td style='background-color: gold; color:black; font-weight: bold;'>{$zapis[5]}</td>";
                } else if ($zapis[5] == 50) {
                    echo "<td style='background-color: silver; color:black; font-weight: bold;'>{$zapis[5]}</td>";
                } else if ($zapis[5] == 10) {
                    echo "<td style='background-color: #CD7F32; color:black; font-weight: bold;'>{$zapis[5]}</td>";
                } else {
                    echo "<td style='font-weight: bold;'>{$zapis[5]}</td>";
                }
                echo "</tr>";
            }
            echo "</tbody>";
            echo "</table>";
            $baza->zatvoriDB();
            ?>

            <?php
            $baza = new Baza();
            $baza->spojiDB();

            echo "<table>";
            echo "<caption>Evidentiranje bodova pojedine etape</caption>";
            echo "<thead>
                <tr>
                    <th>ID etape</th> 
                    <th>Naziv utrke</th>
                    <th>Država u kojoj se odvija utrka</th>
                    <th>Naziv etape</th>
                    <th>Vrijeme početka etape</th>
                    <th>Zaključana</th>
                    <th>Evidentiranje bodova</th>
                </tr>
            </thead>
            <tbody>";

            $sqlUpit = "SELECT etapa.etapa_id, etapa.naziv, utrka.naziv, drzava.naziv, etapa.datum_i_vrijeme, etapa.zakljucana "
                    . "FROM etapa, utrka, drzava,moderator_drzava "
                    . "WHERE etapa.utrka = utrka.utrka_id "
                    . "AND utrka.drzava = drzava.drzava_id "
                    . "AND moderator_drzava.drzava = drzava.drzava_id "
                    . "AND moderator_drzava.korisnik = $prijavljeniKorisnikID";
            $rezultat = $baza->selectDB($sqlUpit);

            while ($zapis = mysqli_fetch_array($rezultat)) {
                echo "<tr>";
                echo "<td>{$zapis[0]}</td>";
                echo "<td>{$zapis[2]}</td>";
                echo "<td>{$zapis[3]}</td>";
                echo "<td>{$zapis[1]}</td>";
                echo "<td>{$zapis[4]}</td>";
                if ($zapis[5] == 1) {
                    echo "<td style='background-color:#D24D57;'>Zaključana</td>";
                } else {
                    echo "<td style='background-color:#26A65B;;'>Nije zaključana</td>";
                }
                if ($zapis[5] == 1) {
                    echo "<td><a class='proglasiPobjednika' href='popis_utrka_i_zakljucavanje.php?etapaid={$zapis[0]}'>Evidentiraj bodove</a></td>";
                }
                echo "</tr>";
            }
            echo "</tbody>";
            echo "</table>";
            $baza->zatvoriDB();
            ?>

            <?php
            $baza = new Baza();
            $baza->spojiDB();

            echo "<table>";
            echo "<caption>Pobjednici</caption>";
            echo "<thead>
                <tr>
                    <th>Ime</th> 
                    <th>Prezime</th>
                    <th>Naziv utrke</th>
                    <th>Država u kojoj se odvijala utrka</th>
                </tr>
            </thead>
            <tbody>";

            $sqlUpit = "SELECT korisnik.ime, korisnik.prezime, utrka.naziv, drzava.naziv "
                    . "FROM drzava, utrka, pobjednik, korisnik "
                    . "WHERE korisnik.korisnik_id = pobjednik.korisnik "
                    . "AND pobjednik.utrka = utrka.utrka_id "
                    . "AND utrka.drzava = drzava.drzava_id "
                    . "ORDER BY korisnik.ime ASC";
            $rezultat = $baza->selectDB($sqlUpit);

            while ($zapis = mysqli_fetch_array($rezultat)) {
                echo "<tr>";
                echo "<td>{$zapis[0]}</td>";
                echo "<td>{$zapis[1]}</td>";
                echo "<td>{$zapis[2]}</td>";
                echo "<td>{$zapis[3]}</td>";
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


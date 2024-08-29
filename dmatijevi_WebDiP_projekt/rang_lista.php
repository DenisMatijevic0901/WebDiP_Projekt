<?php
error_reporting(E_ALL ^ E_NOTICE);  
$direktorij = getcwd();
$putanja = dirname($_SERVER['REQUEST_URI']);

include './zaglavlje.php';

if (isset($_GET['btnRangLista'])) {
    $datumOd = $_GET['datumOd'];
    $datumDo = $_GET['datumDo'];
    
    if ($datumOd == "" || $datumDo == "") {
        $porukaRangLista = "Odaberite datume!";
    }
    $formatiraniDatumOd = date("Y-m-d H:i:s", strtotime($datumOd));
    $formatiraniDatumDo = date("Y-m-d H:i:s", strtotime($datumDo));
}
?>
<!DOCTYPE html>
<html lang="hr">
    <head>
        <title>Rang lista - Trčanje</title>
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
            
            <h1 class="naslovStranice">RANG LISTA</h1>
            <?php 
            global $porukaRangLista;
            echo "<p style = 'color:red; font-weight:bold; text-align:center;'>$porukaRangLista</p>";
            ?>
        </header>         
        <hr>
        <section>
        
        <form novalidate class="form" method="get" id="formRangLista" name="formRangLista" action="rang_lista.php">
                <label style="margin-bottom: 5px;" for="datumOd">Datum od: </label>
                <input class="tbKorimePrijava" type="datetime-local" id="datumOd" name="datumOd" size="30" maxlength="30" placeholder="dd.mm.gggg." autofocus="autofocus" required="required"><br>

                <label style="margin-bottom: 5px;" for="datumDo">Datum do: </label>
                <input class="tbKorimePrijava" type="datetime-local" id="datumDo" name="datumDo" size="30" maxlength="30" placeholder="dd.mm.gggg." autofocus="autofocus" required="required"><br>

            </form>
            
            <div style="text-align:center;">
                <input form="formRangLista" name="btnRangLista" type="submit" class="submit" value=" Pretraži ">
            </div>
            
            <hr>
            <?php
            echo "<table>";
            if ($datumOd == "" || $datumDo == "") {
                echo "<caption>Rang lista broja završenih etapa korisnika</caption>";
            }else{
                echo "<caption>Rang lista broja završenih etapa korisnika od $formatiraniDatumOd do $formatiraniDatumDo</caption>";
            }  
            echo "<thead>
                <tr>
                    <th>Korisnik</th>
                    <th>Broj završenih etapa</th>
                </tr>
            </thead>
            <tbody>"; 
            ?>
            <?php
            $baza = new Baza();
            $baza->spojiDB();
            $sqlUpit = "SELECT concat(korisnik.ime,' ',korisnik.prezime), COUNT(*) "
                    . "FROM korisnik, rezultat_etape, etapa "
                    . "WHERE korisnik.korisnik_id = rezultat_etape.korisnik "
                    . "AND rezultat_etape.odustao = 0 "
                    . "AND etapa.etapa_id = rezultat_etape.etapa "
                    . "AND etapa.datum_i_vrijeme BETWEEN '$formatiraniDatumOd' AND '$formatiraniDatumDo' "
                    . "GROUP BY 1";
            $rezultat = $baza->selectDB($sqlUpit);

            $baza->zatvoriDB();
            global $rezultat;
            while ($zapis = mysqli_fetch_array($rezultat)) {
                echo "<tr>"
                . "<td>{$zapis[0]}</td>"
                . "<td>{$zapis[1]}</td>"
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

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

?>
<!DOCTYPE html>
<html lang="hr">
    <head>
        <title>Popis budućih utrka - Trčanje</title>
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
            
            <h1 class="naslovStranice">POPIS BUDUĆIH UTRKA</h1>
        </header>         
        <hr>
        <section>
            <?php
            $danasnjiDatum = date("Y-m-d H:i:s");
            echo "<p style='margin-left: 50px; margin-bottom: 50px; font-weight:bolder;'>Trenutno vrijeme je: $danasnjiDatum</p>";
            echo "<a class='popisBuducihUtrkaAzurirajVrijeme' href='popis_buducih_utrka.php?'>Ažuriraj trenutno vrijeme</a>";
            
            echo "<table>";
            echo "<caption>Popis budućih utrka za koje su otvorene prijave</caption>";
            echo "<thead>
                <tr>
                    <th>ID</th>
                    <th>Naziv utrke</th>
                    <th>Vrijeme završetka prijava</th>
                    <th>Država u kojoj se odvija utrka</th>
                </tr>
            </thead>
            <tbody>"; 
            ?>
            <?php
            $baza = new Baza();
            $baza->spojiDB();
            
            
            
            $sqlUpit = "SELECT utrka.utrka_id, utrka.naziv, utrka.vrijeme_zavrsetka_prijava, drzava.naziv "
                    . "FROM utrka JOIN drzava ON utrka.drzava = drzava.drzava_id "
                    . "AND utrka.vrijeme_zavrsetka_prijava > '$danasnjiDatum'";
            $rezultat = $baza->selectDB($sqlUpit);

            $baza->zatvoriDB();
            while ($zapis = mysqli_fetch_array($rezultat)) {
                echo "<tr>"
                . "<td>{$zapis[0]}</td>"
                . "<td>{$zapis[1]}</td>"
                . "<td>{$zapis[2]}</td>"
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

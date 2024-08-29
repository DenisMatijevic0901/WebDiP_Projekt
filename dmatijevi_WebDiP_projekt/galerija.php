<?php
error_reporting(E_ALL ^ E_NOTICE);  
$direktorij = getcwd();
$putanja = dirname($_SERVER['REQUEST_URI']);

include './zaglavlje.php';

?>
<!DOCTYPE html>
<html lang="hr">
    <head>
        <title>Galerija - Trčanje</title>
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
            
            <h1 class="naslovStranice">GALERIJA</h1>
            <?php 
            global $porukaRangLista;
            echo "<p style = 'color:red; font-weight:bold; text-align:center;'>$porukaRangLista</p>";
            ?>
        </header>         
        <hr>
        <section>

            <form novalidate class="form" method="get" id="formGalerija" name="formGalerija" action="galerija.php">
                
                <label style="margin-bottom: 5px;" for="odaberiDrzavu">Odaberite državu: </label>
                <select class="tbKorimePrijava" style="width: 305px; height:30px;" name="odaberiDrzavu" id="odaberiDrzavu">
                    <option style='text-align:center;' value='nijeOdabranaDrzava'>--Odaberite državu--</option>
                    <?php
                    $baza = new Baza();
                    $baza->spojiDB();
                    $sqlUpitGalerijaFiltriranjeDrzava = "SELECT drzava_id, naziv FROM `drzava`";
                    $rezultatGalerijaFiltriranjeDrzava = $baza->selectDB($sqlUpitGalerijaFiltriranjeDrzava);
                    $baza->zatvoriDB();
                    while ($zapis = mysqli_fetch_array($rezultatGalerijaFiltriranjeDrzava)) {
                        echo "<option style='text-align:center;' value='$zapis[0]'>$zapis[1]</option>";
                    }
                    ?>                                                        
                </select><br>

                <hr style="margin-top: 15px; margin-bottom: 15px;">   
                
                <input style="margin-top: 10px;" form="formGalerija" name="btnGalerijaSortirajPoImenuU" type="submit" class="submit" value=" Sortiraj po imenu uzlazno ">
                <input style="margin-top: 10px;" form="formGalerija" name="btnGalerijaSortirajPoImenuS" type="submit" class="submit" value=" Sortiraj po imenu silazno">

                <input style="margin-top: 10px;" form="formGalerija" name="btnGalerijaSortirajPoPrezimenuU" type="submit" class="submit" value=" Sortiraj po prezimenu uzlazno">
                <input style="margin-top: 10px;" form="formGalerija" name="btnGalerijaSortirajPoPrezimenuS" type="submit" class="submit" value=" Sortiraj po prezimenu silazno">
            </form>

            <div style="text-align:center;">
                <input form="formGalerija" name="btnGalerija" type="submit" class="submit" value=" Pretraži ">
            </div>

            <hr>
            <h3 style="text-align: center; text-decoration: underline;">Pobjednici utrka</h3>
   
            <?php
            $baza = new Baza();
            $baza->spojiDB();
            
            $odabranaDrzavaID = $_GET['odaberiDrzavu'];
            $sqlUpit = "SELECT korisnik.ime, korisnik.prezime, prijava.slika, utrka.naziv, drzava.naziv "
                    . "FROM drzava, utrka, prijava, korisnik, pobjednik "
                    . "WHERE drzava.drzava_id = utrka.drzava "
                    . "AND utrka.utrka_id = prijava.utrka "
                    . "AND prijava.korisnik = korisnik.korisnik_id "
                    . "AND korisnik.korisnik_id = pobjednik.korisnik "
                    . "AND pobjednik.utrka = utrka.utrka_id";
            $rezultat = $baza->selectDB($sqlUpit);
            
            if(isset($_GET['btnGalerija']) && $_GET['odaberiDrzavu'] != "nijeOdabranaDrzava"){
                $sqlUpitFiltriranjeDrzava = "SELECT korisnik.ime, korisnik.prezime, prijava.slika, utrka.naziv, drzava.naziv "
                    . "FROM drzava, utrka, prijava, korisnik, pobjednik "
                    . "WHERE drzava.drzava_id = utrka.drzava "
                    . "AND utrka.utrka_id = prijava.utrka "
                    . "AND prijava.korisnik = korisnik.korisnik_id "
                    . "AND korisnik.korisnik_id = pobjednik.korisnik "
                    . "AND pobjednik.utrka = utrka.utrka_id "
                    . "AND drzava.drzava_id = $odabranaDrzavaID "
                    . "ORDER BY 1";
                $rezultat = $baza->selectDB($sqlUpitFiltriranjeDrzava);
            }
            
            if(isset($_GET['btnGalerijaSortirajPoImenuU'])){
                $sqlUpitSortiranjePoImenuU = "SELECT korisnik.ime, korisnik.prezime, prijava.slika, utrka.naziv, drzava.naziv "
                        . "FROM drzava, utrka, prijava, korisnik, pobjednik "
                        . "WHERE drzava.drzava_id = utrka.drzava "
                        . "AND utrka.utrka_id = prijava.utrka "
                        . "AND prijava.korisnik = korisnik.korisnik_id "
                        . "AND korisnik.korisnik_id = pobjednik.korisnik "
                        . "AND pobjednik.utrka = utrka.utrka_id "
                        . "ORDER BY 1 ASC";
                $rezultat = $baza->selectDB($sqlUpitSortiranjePoImenuU);
            }
            
            if(isset($_GET['btnGalerijaSortirajPoImenuS'])){
                $sqlUpitSortiranjePoImenuS = "SELECT korisnik.ime, korisnik.prezime, prijava.slika, utrka.naziv, drzava.naziv "
                        . "FROM drzava, utrka, prijava, korisnik, pobjednik "
                        . "WHERE drzava.drzava_id = utrka.drzava "
                        . "AND utrka.utrka_id = prijava.utrka "
                        . "AND prijava.korisnik = korisnik.korisnik_id "
                        . "AND korisnik.korisnik_id = pobjednik.korisnik "
                        . "AND pobjednik.utrka = utrka.utrka_id "
                        . "ORDER BY 1 DESC";
                $rezultat = $baza->selectDB($sqlUpitSortiranjePoImenuS);
            }
            
            if(isset($_GET['btnGalerijaSortirajPoPrezimenuU'])){
                $sqlUpitSortiranjePoPrezimenuU = "SELECT korisnik.ime, korisnik.prezime, prijava.slika, utrka.naziv, drzava.naziv "
                        . "FROM drzava, utrka, prijava, korisnik, pobjednik "
                        . "WHERE drzava.drzava_id = utrka.drzava "
                        . "AND utrka.utrka_id = prijava.utrka "
                        . "AND prijava.korisnik = korisnik.korisnik_id "
                        . "AND korisnik.korisnik_id = pobjednik.korisnik "
                        . "AND pobjednik.utrka = utrka.utrka_id "
                        . "ORDER BY 2 ASC";
                $rezultat = $baza->selectDB($sqlUpitSortiranjePoPrezimenuU);
            }
            
            if(isset($_GET['btnGalerijaSortirajPoPrezimenuS'])){
                $sqlUpitSortiranjePoPrezimenuS = "SELECT korisnik.ime, korisnik.prezime, prijava.slika, utrka.naziv, drzava.naziv "
                        . "FROM drzava, utrka, prijava, korisnik, pobjednik "
                        . "WHERE drzava.drzava_id = utrka.drzava "
                        . "AND utrka.utrka_id = prijava.utrka "
                        . "AND prijava.korisnik = korisnik.korisnik_id "
                        . "AND korisnik.korisnik_id = pobjednik.korisnik "
                        . "AND pobjednik.utrka = utrka.utrka_id "
                        . "ORDER BY 2 DESC";
                $rezultat = $baza->selectDB($sqlUpitSortiranjePoPrezimenuS);
            }

            $baza->zatvoriDB();
            while ($zapis = mysqli_fetch_array($rezultat)) {
               echo "<div margin-bottom: -300px; style='background-color:rgb(248, 248, 255, 0.7); border-radius:800px; '>";
               echo "<p><img src='materijali/{$zapis[2]}' alt ='slikaKorisnika' class='galerijaHover' width='400' height='400' style='margin-left: 150px; border-radius:13px; display: block; margin-left: auto; margin-right: auto;'></img></p>";
               echo "<p style='margin-left: 150px; display:inline-block;  color:black; font-size: 24px; font-weight:bold; padding:0; margin-bottom:0px;'>Ime: $zapis[0]</p>";
               echo "<p style='margin-left: 150px; display:inline-block;  color:black; font-size: 24px; font-weight:bold; padding:0; margin-bottom:0px;'>Prezime: $zapis[1]</p>";
               echo "<p style='margin-left: 150px; display:inline-block;  color:black; font-size: 24px; font-weight:bold; padding:0; margin-bottom:0px;'>Utrka: $zapis[3]</p>";
               echo "<p style='margin-left: 150px; display:inline-block;  color:black; font-size: 24px; font-weight:bold; padding:0; '>Država: $zapis[4]</p>";
               echo "</div>";
            }

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

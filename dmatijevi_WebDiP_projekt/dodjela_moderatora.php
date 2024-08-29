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


function dohvatiPodatke(){
    global $dohvaceniNaziv;
    global $dohvaceniGrad;
    global $dohvaceniId;
    $baza = new Baza();
    $baza->spojiDB();
    
    $dohvaceniId = $_GET['drzavaid'];
    
    $sqlUpit = "SELECT * FROM `drzava` WHERE drzava_id = '$dohvaceniId'";
    
    $rezultat = $baza->selectDB($sqlUpit);  
    $red = mysqli_fetch_array($rezultat);
    
    $baza->zatvoriDB();
    
    $dohvaceniId = $red['drzava_id'];
    $dohvaceniNaziv = $red['naziv'];
    $dohvaceniGrad = $red['glavni_grad'];
}
dohvatiPodatke();


if (isset($_GET['btnDodijeliModeratora'])) {
    if ($_GET['naziv'] == "" || $_GET['odaberiModeratora'] == "nijeOdabranModerator") {
        $porukaDodjelaModeratora = "Potrebno je popuniti sva polja!";
    } else {
        $baza = new Baza();
        $baza->spojiDB();
        $IdModeratora = $_GET['odaberiModeratora'];
        $nazivDrzave = $_GET['naziv'];
        
        $sqlUpitIdDrzave = "SELECT drzava_id FROM drzava WHERE naziv = '$nazivDrzave'";
        $rezultatIdDrzave = $baza->selectDB($sqlUpitIdDrzave);
        $zapisIdDrzave = mysqli_fetch_array($rezultatIdDrzave);
        $IdDrzave = $zapisIdDrzave['drzava_id'];
        
        $sqlUpit = "INSERT INTO moderator_drzava (korisnik, drzava)"
                . "VALUES ($IdModeratora, $IdDrzave)";
        $rezultat = $baza->insertDB($sqlUpit);
        
        if ($rezultat) {
            $porukaDodjelaModeratora = "Moderator dodijeljen.";
        }else{
            $porukaDodjelaModeratora = "Greška prilikom dodjele moderatora.";
        }
        $baza->zatvoriDB();
        header("Location: $putanja/drzave.php");
    }
}
?>
<!DOCTYPE html>
<html lang="hr">
    <head>
        <title>Dodjela moderatora državi - Trčanje</title>
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

            <h1 class="naslovStranice">DRŽAVE</h1>
            <?php
            global $porukaDodjelaModeratora;
            echo "<p style = 'color:red; font-weight:bold; text-align:center;'>$porukaDodjelaModeratora</p>";
            ?>
        </header>         
        <hr>
        <section>
            <h3 style="text-align: center; text-decoration: underline;">Dodjela moderatora državi</h3>
            <form novalidate class="form" method="get" id="formDodjelaModeratora" name="formDodjelaModeratora" action="dodjela_moderatora.php">

                <label style="margin-bottom: 5px;" for="naziv">Naziv države: </label>
                <input class="tbKorimePrijava" style="margin-left: 72px;" 
                       <?php if (isset($_GET['drzavaid'])) {
                           global $dohvaceniNaziv;
                           
                           echo "value='$dohvaceniNaziv'";
                       } ?>
                       type="text" id="naziv" name="naziv" size="30" maxlength="30" placeholder="naziv" autofocus="autofocus" required="required"><br>
                <label style="margin-bottom: 5px;" for="odaberiModeratora">Moderator: </label>
                <select class="tbKorimePrijava" style="width: 305px; height:30px; margin-left: 92px;" name="odaberiModeratora" id="odaberiModeratora">
                    <option style='text-align:center;' value='nijeOdabranModerator'>--Odaberite moderatora--</option>
                     <?php
                     $baza = new Baza();
                     $baza->spojiDB();
                     $sqlUpitModerator = "SELECT korisnik.korisnik_id, concat(korisnik.ime, ' ' , korisnik.prezime) FROM korisnik WHERE korisnik.tip_korisnika = 3";
                     $rezultatModerator = $baza->selectDB($sqlUpitModerator);
                     $baza->zatvoriDB();
                     while ($zapis = mysqli_fetch_array($rezultatModerator)) {
                         echo "<option style='text-align:center;' value='$zapis[0]'>$zapis[1]</option>";
                     }
                     ?>                                                        
                    </select><br>
            </form>

            <div style="text-align:center;">
                <input form="formDodjelaModeratora" name="btnDodijeliModeratora" type="submit" class="submit" value=" Dodijeli ">
            </div>
 
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

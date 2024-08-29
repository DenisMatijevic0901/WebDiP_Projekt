<?php
error_reporting(E_ALL ^ E_NOTICE);
error_reporting(E_ERROR | E_PARSE);
$direktorij = dirname(getcwd());
$putanja = dirname($_SERVER['REQUEST_URI'],2);

include '../zaglavlje.php';
?>

<!DOCTYPE html>
<html lang="hr">
    <head>
        <title>Lista korisnika - Trčanje</title>
        <meta charset="utf-8">
        <meta name="author" content="Denis Matijević">
        <meta name="description" content="9.6.2022.">
        <meta name="viewport" content="width=device-width, minimum-scale=1, maximum-scale=1">
    </head>
    <body>
        <header>
            <h1>Lista korisnika</h1>
        </header>         
        <section>
            <?php
            echo "<table style='border:1px solid black;'>
            <caption>Lista korisnika</caption>
            <thead>
                <tr>
                    <th>Ime</th>
                    <th>Prezime</th>
                    <th>Email</th>
                    <th>Korisničko ime</th>
                    <th>Lozinka</th>
                    <th>Tip korisnika</th>                 
                </tr>
            </thead>
            <tbody>";     
                  $baza = new Baza();
                  $baza->spojiDB();
                  $sqlUpit = "SELECT * FROM korisnik";
                  
                  $rezultat = $baza->selectDB($sqlUpit);

                  $baza->zatvoriDB();
                  while ($zapis = mysqli_fetch_array($rezultat)) {
                      echo "<tr>"
                      . "<td>{$zapis['ime']}</td>"
                      . "<td>{$zapis['prezime']}</td>"
                      . "<td>{$zapis['email']}</td>"
                      . "<td>{$zapis['korisnicko_ime']}</td>"
                      . "<td>{$zapis['lozinka']}</td>"
                      . "<td>{$zapis['tip_korisnika']}</td>"
                      . "</tr>";
                  }
                  $baza->zatvoriDB();
              ?>
        </section>
    </body>
</html>
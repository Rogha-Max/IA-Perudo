<!DOCTYPE html>
<html>
    
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>PERUDO</title>
        <meta name="description" content="IA contre IA">
    </head>
    <body>
        
        <h1>PARTIE DE PERUDO</h1>
        <?php
        require_once("Partie.php");
        //$p=new Partie("Humain","Humain","Humain","Humain");
        //$p=new Partie("Pique","Coeur","Carreau","Trefle");
        //$p=new Partie("Coeur","Trefle","Pique","Carreau");
        //$p=new Partie("Carreau","Pique","Trefle","Coeur");
        $p=new Partie("Trefle","Carreau","Coeur","Pique");
        $p->main();
        ?>
 </body>
 </html>
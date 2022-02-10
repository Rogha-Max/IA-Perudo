<?php
require_once("Joueur.php");

class Pique extends Joueur {
   private $DesJoueurs;
   private $HistoCoups;



   public function historique($coupsJoues, $nbDesParJoueur) {
      $this->DesJoueurs = [
         'J1' => $nbDesParJoueur[0],
         'J2' => $nbDesParJoueur[1],
         'J3' => $nbDesParJoueur[2],
         'J4' => $nbDesParJoueur[3],
      ];
      
      $this->HistoCoups = $coupsJoues;



      
      //echo "lecture historique en cours...";
   }

   public function __construct ($nom){
      parent::__construct($nom);
      $this->DesJoueurs = array();
      $this->HistoCoups = array();
   }

   public function evaluer($qte, $val, $palifico, $nbDes) {
      //// PARAMETRES DE LA FONCTION evaluer() ////
      /*
         $qte = quantité de dés annoncé par le joueur précédent
         $val = val de dé annoncé par le joueur précédent
         $palifico = booléen qui indique si on joue en palifico ou non
         $nbDes = nombres de des sur le plateau (nous compris)
         return [qte,val];
      */
      /////// this->mesDes      nos dés en main

      $DJ1 = $this->DesJoueurs['J1'];
      $DJ2 = $this->DesJoueurs['J2'];
      $DJ3 = $this->DesJoueurs['J3'];
      $DJ4 = $this->DesJoueurs['J4'];

      switch ($this->mesDes) {
         case $DJ1:
            $lastJ = 'J4';
            $nextJ = 'J2';
            break;
         case $DJ2:
            $lastJ = 'J1';
            $nextJ = 'J3';
            break;
         case $DJ3:
            $lastJ = 'J2';
            $nextJ = 'J4';
            break;
         case $DJ4:
            $lastJ = 'J3';
            $nextJ = 'J1';
            break;
      }

      // for ($i=0; $i < $this->nbDes; $i++)
      
      $choice = random_int(0, 2);

      switch ($choice) {
         case 0:
            $val += 1;
            break;
         case 1:
            $qte += 1;
            break;
         case 2:
            $qte += 1;
            $val += 1;
            break;
      }
      /*if ($palifico && $qte > $nbDes / 4) {
         $decision = array(-1, $val);
      }*/
      if ($val > 6) $val = 6;
      $decision = array($qte, $val);
      return $decision;
   }
}
?>
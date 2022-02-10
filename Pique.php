<?php

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

   public function __construct (){
       $this->DesJoueurs = array();
       $this->HistoCoups = array();
   }


   public function evaluer($qte, $val, $palifico, $nbDes) {
      //return [qte,val];
      // nbDes = nombres de des sur le plateau (nous compris)
      // $DJ1 = $this->DesJoueurs['J1'];
      // for ($i=0; $i < $this->nbDes; $i++)
      // this->mesDes      nos dés en main

      $choice = random_int(0, 2);

      switch ($choice) {
         case 0:
            $decision = array($qte, $val + 1);
            break;
         case 1:
            $decision = array($qte + 1, $val);
            break;
         case 2:
            $decision = array($qte + 1, $val + 1);
            break;
      }
      if ($palifico && $qte > $nbDes / 4) {
         $decision = array(-1, $val);
      }
      return $decision;
   }
}
?>
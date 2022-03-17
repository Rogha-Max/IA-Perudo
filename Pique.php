<?php
require_once("Joueur.php");

class Pique extends Joueur {
   private $DesJoueurs;
   private $HistoCoups;
   private $WeAre;
   private $Decision;
   private $TopMain;
   //private $probaGlobal;
   //private $imax;

   
   public function __construct ($nom){
      parent::__construct($nom);
      $this->DesJoueurs = array();
      $this->HistoCoups = array();
      $this->WeAre = "A definir";
      $this->Decision = array();
      $this->TopMain = array();
      //$this->probaGlobal = array();
      //$this->imax = '';
   }
   

   /////////////////////////////////////////////////////
   //// Fonctions d'analyse de notre main et du jeu ////
   /////////////////////////////////////////////////////

   public function historique($coupsJoues, $nbDesParJoueur) { //// Fonction contenant l'historique des coups et le nombre des dés de chaque joueurs
      $this->DesJoueurs = $nbDesParJoueur;
      
      $this->HistoCoups = $coupsJoues;

      $this->setPosition($coupsJoues);

      $this->topValue();
   }

   public function setPosition($coups){ //// Fonction permettant de savoir quel joueur nous sommes
      if (empty($coups)) {
         $this->WeAre = 0;
      } else {
         switch ($coups[count($coups)-1][0]) {
            case 0:
               $this->WeAre = 1;
               break;
            case 1:
               $this->WeAre = 2;
               break;
            case 2:
               $this->WeAre = 3;
               break;
            case 3:
               $this->WeAre = 0;
               break;
         }
      }   
   }

   public function topValue() { //// Fonction pour determiner la valeur qui se répete le plus dans notre main
      $t = array(1 => 0,0,0,0,0,0); //tableau contenant le nombre de dés de chaque valeur
      $mostValue = 0; //valeur la plus présente dans notre main
      $nbFois = 0; //nombre de fois que le dés est présent

      foreach ($this->mesDes as $value) {
         $t[$value]++;
      }

      for ($i=1; $i < 7 ; $i++) { 
         if ($nbFois < $t[$i]) {
            $nbFois = $t[$i];
            $mostValue = $i;
         }
      }

      $nbPaco = $t[1];

      $this->TopMain = [
         'topValue' => $mostValue,
         'nTime' => $nbFois,
         'nbPaco' => $nbPaco,
      ];
   }


   //////////////////////////////////////////////////////////////
   //// Fonction qui évalue et reourne notre décision de jeu ////
   //////////////////////////////////////////////////////////////

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

      $first = (empty($val) || empty($qte)) ? TRUE : FALSE ; ///// Jouons nous en premier ?
      $paco = ($val = 1) ? TRUE : FALSE ; ///// Jouons nous en paco ?


      if ($val > 6) { // Filtres pour débuter à jouer
         $this->bluff();
      } 
      else {
         if ($first && $palifico) { ///// On joue en premier et il nous reste un seul dés
            $this->usPalifico();
         }
         elseif ($first && !$palifico) { ///// On joue en premier et il nous reste plus d'un dés
            $this->premier($nbDes);
         }
         elseif (!$first && $palifico) { ///// On joue pas en premier mais en palifico
            $this->palifico($qte, $val, $nbDes);
         }
         elseif (!$first && !$palifico && $paco) { ///// On joue ni en premier ni en palifico mais en paco
            $this->paco($qte, $val, $nbDes);
         }
         elseif (!$first && !$palifico && !$paco) { ///// On joue ni en premier ni en palifico ni en paco (CAS NORMAL)
            $this->randomIA($qte, $val);
         }
         else { ///// Au cas ou aucun cas n'a pu être rempli on appel l'IA random (/!\PAS NORMAL/!\)
            $this->randomIA($qte, $val);
         }
      }
      
      return $this->Decision;
   }


   ////////////////////////////////////
   //// Fonctions d'actions de jeu ////
   ////////////////////////////////////

   public function outPaco($qtePaco){ //// Fonction permettant de sortir du mode paco
      array_push($this->Decision, $qtePaco*2+1, 2);
   }   public function inPaco($qte){
 //// Fonction permettant d'entrer en mode paco
      array_push($this->Decision, ceil($qte/2), 1);   }
   public function bluff(){
 //// Fonction permettant d'accuser de bluff
      array_push($this->Decision, -1, 0);   }
     public function calza(){ //// Fonction permettant d'annoncer calza
      array_push($this->Decision, 0, -1);
   }


   ///////////////////////////////////////
   //// Fonctions de décisions de jeu ////
   ///////////////////////////////////////

   public function usPalifico(){ //// Fonction de gameplay lorsqu'il nous reste un seul dés
      # code...
   }

   public function premier($plateau){ //// Fonction de gameplay lorsqu'on joue en premier
      if ($this->TopMain['nTime'] = 1 && $this->TopMain['topValue'] = 1) { //cas où nos dés sont tous différents
         array_push($this->Decision, 3, 2);
      } else {
         if ($this->nbDes < $plateau/2) { // Si le nombre de dés dans notre main est inférieur a la moitié des dés en jeu
            $qte = $this->TopMain['nTime'] + $this->TopMain['nbPaco'];
            $qte = ceil($qte + ($qte/2));
            array_push($this->Decision, $qte, $this->TopMain['topValue']);
         } else {
            array_push($this->Decision, $this->TopMain['nTime'], $this->TopMain['topValue']);
         }
      }
   }

   public function palifico($annonce, $valeur, $plateau){ //// Fonction de gameplay lorsqu'on est en mode palifico
      if($annonce >= $plateau/2) { //si le nombre de dés annoncés est plus grand que la moitiée des dés restants
         $this->bluff(); //accuse de bluff
      } else {
         array_push($this->Decision, $annonce++, $valeur); //on augmente la quantité
      }
   }

   public function paco($annonce, $valeur, $plateau){ //// Fonction de gameplay lorsqu'on est en mode paco
      if ($annonce >= $plateau/4){ //si le nombre de dés annoncés est plus grand qu'un quart des dés restants
         if ($annonce > ($this->mesDes)/3) { //si le nombre de dés annoncés est plus grand qu'un tiers du nombre de dés qu'il nous reste
            array_push($this->Decision, $annonce++, $valeur); //on augmente la quantité
         } else { 
            $this->bluff(); //accuse de bluff
         }
     } else { 
        $this->outPaco($qte); //on sort du paco
     }
   }

   public function randomIA($qte; $val){ //// Fonction de gameplay en mode aléatoire
      $choice = random_int(0, 5);
      switch ($choice) {
         case 0:
            if ($val = 6) {
               $qte += 1;
            } else {
               $val += 1; 
            }
            break;
         case 1:
            $qte += 1;
            break;
         case 2:
            $qte += 1;
            $val += 1;
         case 4: //bluff
            $qte = -1;
            break;
         case 5: //calza
            $val = -1;
            break;
      }
      if ($val > 6) $val = 6;
      if ($qte > 10) $val = -1;
      array_push($this->Decision, $qte, $val);
   }}

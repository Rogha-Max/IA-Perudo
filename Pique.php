<?php
require_once("Joueur.php");
////////////////////////////////////////////////////////////////////////////////////
///// Si tu lis ça t'es vraiment une personne pas cool et indigne de confiance /////
////////////////////////////////////////////////////////////////////////////////////
class Pique extends Joueur {
   private $DesJoueurs;
   private $HistoCoups;
   private $WeAre;
   private $Prev;
   private $Decision;
   private $TopMain;
   private $probaGlobal;
   private $imax;

   
   public function __construct ($nom){
      parent::__construct("Thierno Boubacar");
      $this->DesJoueurs = array();
      $this->HistoCoups = array();
      $this->WeAre = "A definir";
      $this->Prev = "A definir";
      $this->Decision = array();
      $this->TopMain = [
         'topValue' => 0,
         'nTime' => 0,
         'nbPaco' => 0,
      ];
      $this->probaGlobal = [
         'AuMoins' => 0,
         'Exactement' => 0,
         'ExactementPaco' => 0,
         'AuMoinsPaco' => 0,
      ];
      $this->imax = '';
   }
   

   /////////////////////////////////////////////////////
   //// Fonctions d'analyse de notre main et du jeu ////
   /////////////////////////////////////////////////////

   public function historique($coupsJoues, $nbDesParJoueur) { //// Fonction contenant l'historique des coups et le nombre de dés de chaque joueurs
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
         $this->Prev = $coups[count($coups)-1][0];
      }   
   }

   public function topValue() { //// Fonction pour determiner la valeur qui se répète le plus dans notre main
      $t = array(0,0,0,0,0,0,0); //tableau contenant le nombre de dés de chaque valeur
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

      $this->TopMain['topValue'] = $mostValue;
      $this->TopMain['nTime'] = $nbFois;
      $this->TopMain['nbPaco'] = $nbPaco;
   }

   function fact($n){ //// Fonction factorielle qui sera réutilisée dans les calculs plus tard
      $f = 1;
      for ($i = 1; $i <= $n; $i++) {
        $f = $f * $i;
      }
      return $f;
   }

   function probaGuillaume($k, $n){ //// Fonction de calcul des probalités
    // ce sont des probabilitées grossières qui n'utilisent que le nombre de dés global et pas la main de notre joueur, il faudra faire des probas en prenant en compte notre gobelet

    //Calcul de la probabilité qu'il y ai au moins k fois la valeur
    // "$k" devra être remplacé par QTE qui sera reçue du joueur précédent pour pouvoir être en mesure de calculer les propabilitées
    // "$n" est le nombre de Des sur la table
    // définission des variables qui seront réutilisées dans d'autres parties du code
    $ProbAuMoins = 0.75;
    $ProbExactement = 0;
    $ProbExactementPaco = 0;
    $ProbAuMoinsPaco = 0;
    $P = 0;

    $f = $this->fact($n);

    // les probabilités son calculées en dessous

    for ($i = $k; $i <= $n; $i++) {
        $f = $this->fact($n) / ($this->fact($i) * $this->fact($n - $i));
        $P += $f * (pow(2, $n - $i)) / (pow(3, $n));
    }

    //echo "il y a au moins $ProbAuMoins une valeur de 2 a 6 ou le paco--";

    //Calcul de la probabilité qu'il y ai exactement k fois la valeur

    $f = $this->fact($n) / ($this->fact($k) * $this->fact($n - $k));
    $ProbExactement = $f * (pow(2, $n - $k)) / (pow(3, $n));

    //echo "il y a exactement $ProbExactement une valeur de 2 a 6 ou le paco--";

    //calcul exactement k fois le PACO

    $f = $this->fact($n) / ($this->fact($k) * $this->fact($n - $k));
    $ProbExactementPaco = $f * (pow(5, $n - $k)) / (pow(6, $n));

    //echo "il y a exactement $ProbExactementPaco le paco--";

    //calcul au moins k fois le PACO

    for ($i = $k; $i <= $n; $i++) {
        $f = $this->fact($n) / ($this->fact($i) * $this->fact($n - $i));
        $ProbAuMoinsPaco += $f * (pow(5, $n - $i)) / (pow(6, $n));
    }

    //echo "il y a au moins $ProbAuMoinsPaco le paco--";

    $data = array($ProbAuMoins, $ProbExactement, $ProbExactementPaco, $ProbAuMoinsPaco);
    //print_r($data);

    $max = 0;
    $imax = 0;
    for ($i = 0; $i < count($data); $i++)
        if ($max < $data[$i]) {
            $max = $data[$i];
            $imax = $i;
            //echo "<br>i = $i, max = $max <br>";
        }

    //echo "-- $imax";
    //echo "-- $data[$imax]";
    $this->probaGlobal['AuMoins'] = $ProbAuMoins;
    $this->probaGlobal['Exactement'] = $ProbExactement;
    $this->probaGlobal['ExactementPaco'] = $ProbExactementPaco;
    $this->probaGlobal['AuMoinsPaco'] = $ProbAuMoinsPaco;
    $this->imax = $imax;
   }


   ///////////////////////////////////////////////////////////////
   //// Fonction qui évalue et retourne notre décision de jeu ////
   ///////////////////////////////////////////////////////////////

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

      $this->probaGuillaume($qte, $nbDes);

      $first = ($val == 0 || $qte == 0) ? TRUE : FALSE ; ///// Jouons nous en premier ?
      $paco = ($val == 1) ? TRUE : FALSE ; ///// Jouons nous en paco ?


      if ($val > 6 || $qte >= $nbDes) { // Filtres pour débuter à jouer
         $this->bluff();
      }
      else {
         if ($this->nbDes == 1 ) { ///// Il nous reste un seul dés
            $this->oneLast($qte, $val, $nbDes);
         }
         elseif ($first && $this->nbDes > 1) { ///// On joue en premier et il nous reste plus d'un dés
            $this->premier($nbDes);
         }
         elseif (!$first && $palifico) { ///// On ne joue pas en premier mais en palifico
            $this->palifico($qte, $val, $nbDes);
         }
         elseif (!$first && !$palifico && $paco) { ///// On joue ni en premier ni en palifico mais en paco
            $this->paco($qte, $val, $nbDes);
         }
         elseif (!$first && !$palifico && !$paco) { ///// On joue ni en premier ni en palifico ni en paco (CAS NORMAL)
            if ($qte > $nbDes*0.8) {
               $this->inPaco($qte);
            } else {
               $this->randomIA($qte, $val);
            }
            /*if($qte > $this->DesJoueurs[$this->Prev]*1.5){
               $this->bluff();
               echo "1 <br>";
            }
            elseif($this->probaGlobal['AuMoins'] < 0.70){
               $this->bluff();
               echo "2 " . $this->probaGlobal['AuMoins'] . "<br>";
            }
            elseif($this->probaGlobal['Exactement'] > 0.60){
               $this->calza();
               echo "3" . $this->probaGlobal['Exactement'] . "<br>";
            }
            elseif($qte >= $nbDes){
               $this->bluff();
               echo "4 <br>";
            }
            else{
               $this->randomIA($qte, $val);
            }*/
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
      echo "outpaco ";
      if ($this->TopMain['nbPaco'] != 0) {
         $newQte = $qtePaco*2 + $this->TopMain['nbPaco'];
      } else {
         $newQte = $qtePaco*2 + 1;
      }
      $this->Decision = [$newQte, $this->TopMain['topValue']];
   }
   public function inPaco($qte){ //// Fonction permettant d'entrer en mode paco
      echo "inpaco ";
      $this->Decision = [ceil($qte/2), 1];
   }
   public function bluff(){ //// Fonction permettant d'accuser de bluff
      echo "bluff ";
      $this->Decision = [-1, 0];
   }
   public function calza(){ //// Fonction permettant d'annoncer calza
      echo "calza ";
      $this->Decision = [0, -1];
   }


   ///////////////////////////////////////
   //// Fonctions de décisions de jeu ////
   ///////////////////////////////////////

   public function oneLast($qteAnnonce, $valAnnonce, $plateau){ //// Fonction de gameplay lorsqu'il nous reste un seul dés
      echo "oneLast ";
      if ($qteAnnonce == 0) $this->premier($plateau);
      elseif ($qteAnnonce < $plateau*0.4) { //si la quantité annoncée est inférieure à 40% du nombre de dés restants
         if ($valAnnonce < $this->TopMain['topValue']) { //si la valeur annoncée est inférieur à notre dé
            $this->Decision = [$qteAnnonce, $this->TopMain['topValue']]; //On sur-enchérit avec notre dé
         } else {
            $this->Decision = [++$qteAnnonce, $valAnnonce]; //On augmente la quantité de 1
         }
      } elseif ($qteAnnonce == floor($plateau*0.4)) { //si la quantité annoncée vaut 40% du nombre de dés restants
         $this->calza(); //on annonce exactement
      } elseif ($qteAnnonce > $plateau*0.4) { //si la quantité annoncée est supérieure à 40% du nombre de dés restants
         $this->bluff(); //on accuse de bluff
      }
   }

   public function premier($plateau){ //// Fonction de gameplay lorsqu'on joue en premier
      echo "first ";
      if ($this->TopMain['nTime'] == 1) { //cas où nos dés sont tous différents
         $this->Decision = [round($plateau/6), 2];
      } else {
         if ($this->TopMain['topValue'] == 1) { //si notre meilleur dé est un paco
            $this->Decision = [++$this->TopMain['nbPaco'], 3]; //on joue notre nombre de paco + 1
         } else {
            if ($this->nbDes >= $plateau/4) { //si on a plus d'un quart des dés en jeu
            $this->Decision = [$this->TopMain['nTime'], $this->TopMain['topValue']]; //on joue notre dé le plus présent, le nombre de fois qu'il est présent, de notre main
            } else { //si on a moins d'un quart des dés en jeu (gameplay agressif)
               $qteJouee = $this->TopMain['nTime'] + $this->TopMain['nbPaco']; //somme meilleur dé et paco
               $qteJouee = ceil($qteJouee + ($qteJouee/2)); //on joue cette somme + elle-même divisé par 2 arrondit au supérieur
               $this->Decision = [$qteJouee, $this->TopMain['topValue']];
            }
         }
      }   
   }


   public function palifico($qteAnnonce, $valAnnonce, $plateau){ //// Fonction de gameplay lorsqu'on est en mode palifico
      echo "palifico ";
      if($qteAnnonce >= $plateau/2) { //si le nombre de dés annoncés est plus grand que la moitiée des dés restants
         echo "ici ";
         $this->bluff(); //accuse de bluff
      } else {
         echo "pasici ";
         $this->Decision = [$qteAnnonce+=1, $valAnnonce]; //on augmente la quantité
      }
   }

   public function paco($qteAnnonce, $valAnnonce, $plateau){ //// Fonction de gameplay lorsqu'on est en mode paco
      echo "paco ";
      if ($qteAnnonce > $plateau / 3) {
         $this->bluff(); //on accuse de bluff
      } elseif ($qteAnnonce == floor($plateau/4)){     // afinner le filtre
         $this->outPaco($qteAnnonce); //on sort du paco
      } else {
         $this->Decision = [$qteAnnonce+=1, 1]; //on augmente la quantité
      }
      
      
      /*if ($qteAnnonce >= $plateau/5){ //si le nombre de dés annoncés est plus grand qu'un quart des dés restants
         if ($qteAnnonce > $this->nbDes/3) { //si le nombre de dés annoncés est plus grand qu'un tiers du nombre de dés qu'il nous reste
            echo "coucou : $qteAnnonce $this->nbDes";
            $this->Decision = [$qteAnnonce+=1, 1]; //on augmente la quantité
         } else { 
            echo "coucou la";
            $this->bluff(); //accuse de bluff
         }
     } else { 
      echo "coucou pas la";
        $this->outPaco($valAnnonce); //on sort du paco
     }*/
   }

   public function randomIA($qte, $val){ //// Fonction de gameplay en mode aléatoire
      echo "random ";

      if ($qte >= 9) $this->bluff(); //accuse de bluff
      else {
         $choice = random_int(0, 2) ;
         switch ($choice) {
            case 0:
               echo "cas0 ";
               if ($val == 6) {
                  $this->Decision = [++$qte, $val]; //augmente la quantité
               } else {
                  $this->Decision = [$qte, ++$val]; //augmente la valeur
               }
               break;
            case 1:
               echo "cas1 ";
               $this->Decision = [++$qte, $val]; //augmente la quantité
               break;
            case 2:
               echo "cas2 ";
               if ($val == 6) {
                  $this->Decision = [++$qte, $val]; //augmente la quantité
               } else {
                  $this->Decision = [++$qte, ++$val]; //augmente la quantité et la valeur
               }
               break;
            /*case 3: //bluff
               echo "cas3 ";
               $this->bluff();
               break;
            case 4: //calza
               echo "cas4 ";
               $this->calza();
               break;
            case 5: //annonce paco
               echo "cas5 ";
               $this->inPaco($qte); 
               break;*/
         }
      } 
   }
}

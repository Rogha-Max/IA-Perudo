       
        <?php
require_once("Humain.php"); //AB
require_once("Pique.php"); //AB
require_once("Coeur.php"); //AB
require_once("Carreau.php"); //AB
require_once("Trefle.php"); //AB

class Partie { //AB

	
	private $coupsJoues=array(); //ArrayList<int[]>
	private $nbDesParJoueur=array(); 
	private $noteParJoueur; 
	private $joueursElimines; //tableau de boolean
	private $desParJoueur; //matrice de récupération des dés pour les joueurs. Premier indice le nom du joueur, second : la face de dé
	private $joueurs= array(); //tableau de stockage des instances de la classe Joueur dans l'ordre de leur tour de jeu
	private $lesDes; //tableau indicé comptant le nombre de dés de chaque face (paco, deux, trois, quatre, cinq et six)
	private $total; //pour stocker le nombre de dés dans la phase de comptage, en fonction de si on a demandé des paco ou pas (car il ne faut pas compter les paco deux fois)

	
	
	

		
	
	
	public function __construct($J1, $J2, $J3, $J4){


			//func_get_arg(1) permet de récupérer la valeur de $J2
		
			//les classes des étudiants doivent hériter de la classe Joueur
			//$coupsJoues = new ArrayList<int[]>();
			//pour ajouter des éléments : $stack = array("orange", "banana"); array_push($stack, "apple", "raspberry"); //ajoute à la fin (comme une pile)
			//parcours for (int i = 0; i<sizeof($coupsJoues);i++)
			//echo array_shift($stack); //retourne orange, puis banana

			$this->nbDesParJoueur = array(5,5,5,5);
			$this->noteParJoueur=array(14,14,14,14);
			$this->joueursElimines = array(false,false,false,false);
			$this->desParJoueur=array(); 
			for ($i=0;$i<4;$i++) $this->desParJoueurs[$i]=array(); //tableau de 4x5
			

			//IMPORTANT : dans les tableaux, chaque joueur garde toujours sa place. Quand un joueur est éliminé on ne décalle pas le tableau
			//$desParJoueur= new int[4][]; //tableau de récupération des dés pour les joueurs. Premier indice le num du joueur, second : les dés

		
			
			//{"carreau","coeur","trefle","pique"};		
			//{"trefle","carreau","coeur","pique"};		
			//{"carreau","pique","trefle","coeur"};		
			//{"coeur","trefle","pique","carreau"};
			//$nomJoueur=array($J1,$J2,$J3,$J4);

			

			for ($j=0;$j<4;$j++){
				$classeAappeler=func_get_arg($j);
				$this->joueurs[$j]=new $classeAappeler($j); //appelle les constructeurs $J1 $J2... ce qui signifie que les arguments du constructeur doivent être écrits exactement comme le nom des classes (casse incluse)
			}
			
			$this->lesDes= array(); //lesDes[0] : nbTotal de dés, lesDes[1] : nb 1 sur la table, lesDes[2] : nb 2 sur la table...
			$this->lesDes[0]=20;
			
			
			
	}
	

public function quelJoueurApres($joueurQuiJoue){
	// déterminer joueurApres;
	$joueurApres=$joueurQuiJoue;
	$h=1;
	$continuue=true;
	while ($h<4){
		if($continuue && !$this->joueursElimines[($joueurQuiJoue+$h)%4]){
			$joueurApres=($joueurQuiJoue+$h)%4;
			$continuue=false;
		}
		$h++;
	}
	return $joueurApres;
}
public function quelJoueurAvant($joueurQuiJoue){
	//déterminer joueurAvant;
	$joueurAvant=$joueurQuiJoue;
	$h=1;
	$continuue=true;
	while ($continuue && $h<4){
		if(!$this->joueursElimines[($joueurQuiJoue+(4-$h))%4]){
			$joueurAvant=($joueurQuiJoue+(4-$h))%4;
			$continuue=false;
		}
		$h++;
	}
	return $joueurAvant;
}
	
public function main(){   
		
		$finTour=false;
		$finPartie=false;
		$deuxUn = false; //dit qu'on passe de deux dés à un seul
		$joueurQuiJoue=0; //celui qui parle dans le tour
		$joueurQuiRelance=0;//celui qui démarre un tour
		$qte=0;
		$val=0;
		$palifico=false;
		$reponseJoueur= array(); //2 cases
		$nbJoueursActifs=4;		
		$joueurAvant=3;
		$joueurApres=1;
        $fichier = fopen('partie.json', 'w+');


		
    /*SYSTEME DE NOTATION :
    PERTE DE 1 POINT
    - si vous dites "exactement" et que c'est faux
    - si vous perdez un dé parce que vous avez bluffé
    - si vous accusez un advesaire de bluff alors que c'est faux
    
    GAIN DE 1 POINT
    - si vous dites "exactement" et que c'est vrai
    - si vous accusez un advesaire de bluff alors que c'est vrai
    - celui qui gagne la partie, gagne 1 point
    */
		//echo "<h1>En lice : 0:".$this->joueurs[0]->getName()." - 1:".$this->joueurs[1]->getName()." - 2:".$this->joueurs[2]->getName()." - 3:".$this->joueurs[3]."</h1>";

		
    $identite = '{"identite" : ["'.$this->joueurs[0]->getName().'","'.$this->joueurs[1]->getName().'","'.$this->joueurs[2]->getName().'","'.$this->joueurs[3]->getName().'"],';
		fwrite($fichier, $identite);
         fwrite($fichier, '"tours" : { ');
	//jouer
        $t=0;
		while(!$finPartie){//jusqu'à ce qu'il n'y ait plus qu'un seul joueur
            $t++;
            if($t<2) fwrite($fichier, '"tour'.$t.'" : { ');
            else fwrite($fichier, ',"tour'.$t.'" : { ');
            
			//vider le tableau des dés
			for($joueur=0;$joueur<4;$joueur++)
				for ($des=0;$des<5;$des++)
					$this->desParJoueur[$joueur][$des]=0;
			
			//vider le tableau de cumul des dés
			for ($a=0;$a<7;$a++)
				$this->lesDes[$a]=0;
			
			//afficher les paris des joueurs comme null
			//CSS
			
			
			//faire lancer les dés aux 4 joueurs et répartir les résultats par valeur dans lesDes
            
            fwrite($fichier, '"lesDes" : {');
			for($j=0;$j<4;$j++){
				if (! $this->joueursElimines[$j]){
                    fwrite($fichier, '"Des'.$j.'" : ['.$j.','.$this->joueurs[$j]->getNbDes());
					$this->desParJoueur[$j]=$this->joueurs[$j]->lancerlesDes();
					$this->lesDes[0]+=$this->nbDesParJoueur[$j];
					for ($i=0;$i<$this->nbDesParJoueur[$j];$i++){
						$this->lesDes[$this->desParJoueur[$j][$i]]++;
                        fwrite($fichier, ','.$this->desParJoueur[$j][$i]);
					} //cas où le nombre de dés du joueurs est inférieur à 5, alors on complete les valeurs par des 0 dans le json
                    for ($i=$this->nbDesParJoueur[$j];$i<5;$i++){
                        fwrite($fichier, ',0');
					}
                    if ($j<3) fwrite($fichier, '],');
                    else fwrite($fichier, ']},');
				}
                else if ($j<3) fwrite($fichier, '"Des'.$j.'" : ['.$j.',0,0,0,0,0,0],');
                    else  fwrite($fichier, '"Des'.$j.'" : ['.$j.',0,0,0,0,0,0]},');
			}
            //print_r($this->desParJoueur);

            echo "<br><br>nb dés sur la table : ".$this->lesDes[0]." => ";
            for($j=1;$j<7;$j++) echo $this->lesDes[$j]." $j - ";
            echo"<br>";
            
            
			//Mettre à jour les quantités de chaque dés ($lesDes)
			//CSS
			
				
			
            //try { sleep(1);} catch (Exception $e) {echo 'Exception reçue : ',  $e->getMessage(), "\n";}

			$finTour=false;

			//initialiser le tableau des coups joués à vide
			$this->coupsJoues=array();
						
			$qte=0;
			$val=0;
			//SOUCIS : IL SE PEUT QUE CELUI QUI RELANCE SOIT ELIMINE !
			$joueurQuiJoue=$joueurQuiRelance;
			while($this->joueursElimines[$joueurQuiJoue])
				$joueurQuiJoue=$this->quelJoueurApres($joueurQuiJoue);
			
			fwrite($fichier, '"annonces" : {');
            $premiertour=true;
			while(!$finTour){//un tour c'est un joueur qui fait une annonce
                if($premiertour) $premiertour=false;
                else fwrite($fichier, ',');
		//APPELER ICI LES METHODES QUIAPRES ET QUIAVANT
				$joueurApres=$this->quelJoueurApres($joueurQuiJoue);
				$joueurAvant=$this->quelJoueurAvant($joueurQuiJoue);				
				
				if(!$this->joueursElimines[$joueurQuiJoue]){
					
				$this->joueurs[$joueurQuiJoue]->historique($this->coupsJoues,$this->nbDesParJoueur); //envoyer l'historique ainsi que le nb de dés de chaque joueur
                    
				$reponseJoueur=$this->joueurs[$joueurQuiJoue]->evaluer($qte, $val, $palifico,$this->lesDes[0]);                   
                echo $this->joueurs[$joueurQuiJoue]->getName()." dit : ". $reponseJoueur[0]."-".$reponseJoueur[1]."<br>";
                $coup=array($joueurQuiJoue, $reponseJoueur[0], $reponseJoueur[1]);
                
                fwrite($fichier, '"say'.$joueurQuiJoue.'" : ['.$joueurQuiJoue.','.$reponseJoueur[0].','.$reponseJoueur[1].']');
                    
				array_push($this->coupsJoues,$coup);
				
				//CSS	
				//Afficher les paris des joueurs			
				


				  // On ralentit l'algorithme artifiellement afin de mieux voir ce  qui se passe
              //      try { sleep(2);} catch (Exception $e) {echo 'Exception reçue : ',  $e->getMessage(), "\n";}
			    
				if($reponseJoueur[0]==-1){ // si c'est "tu bluffes"

					//echo "Joueur "+joueurQuiJoue+" dit qu'il y a bluff";
					if(!$palifico){
						//si on est pas en paco ou pas
						if ($val!=1) $this->total = $this->lesDes[$val]+$this->lesDes[1];
						else $this->total = $this->lesDes[1];
                        
						if ($qte<=$this->total){ //il n'y a pas bluff
							if ($this->nbDesParJoueur[$joueurQuiJoue]==2) $deuxUn=true;
							$this->nbDesParJoueur[$joueurQuiJoue]=$this->joueurs[$joueurQuiJoue]->perdreUnDe(); 
							//CSS
							//tousLesLabels[joueurQuiJoue][nbDesParJoueur[joueurQuiJoue]].setText("X");
							//tousLesLabels[joueurQuiJoue][nbDesParJoueur[joueurQuiJoue]].setForeground(Color.red);
							$this->joueursElimines[$joueurQuiJoue]=($this->nbDesParJoueur[$joueurQuiJoue]==0);
							$this->noteParJoueur[$joueurQuiJoue]--;//NOTATION 2019
							$joueurQuiRelance=$joueurQuiJoue;
							$this->lesDes[0]--;
							$finTour=true;
						}
						else{ //s'il y a bien bluff
							if ($this->nbDesParJoueur[$joueurAvant]==2) $deuxUn=true;
							$this->nbDesParJoueur[$joueurAvant]=$this->joueurs[$joueurAvant]->perdreUnDe();//celui qui jouait avant le joueur en cours	
							$this->noteParJoueur[$joueurAvant]--; //NOTATION 2019 il a bluffé, il a perdu un dé, il perd un point
							//CSS
							//tousLesLabels[joueurAvant][nbDesParJoueur[joueurAvant]].setText("X");
							//tousLesLabels[joueurAvant][nbDesParJoueur[joueurAvant]].setForeground(Color.red);
							$this->joueursElimines[$joueurAvant]=($this->nbDesParJoueur[$joueurAvant]==0); 
							$this->noteParJoueur[$joueurQuiJoue]++; //NOTATION 2019
							$joueurQuiRelance=$joueurAvant;
							$this->lesDes[0]--;
							$finTour=true;
							$joueurQuiRelance=$joueurAvant;
						}
					}
					else{
						//echo "palifico"; //CSS
						if ($qte<=$this->lesDes[$val]){
							if ($this->nbDesParJoueur[$joueurQuiJoue]==2) $deuxUn=true;
							$this->nbDesParJoueur[$joueurQuiJoue]=$this->joueurs[$joueurQuiJoue]->perdreUnDe(); 
							//CSS
							//tousLesLabels[joueurQuiJoue][nbDesParJoueur[joueurQuiJoue]].setText("X");
							//tousLesLabels[joueurQuiJoue][nbDesParJoueur[joueurQuiJoue]].setForeground(Color.red);
							$this->joueursElimines[$joueurQuiJoue]=($this->nbDesParJoueur[$joueurQuiJoue]==0);
							$this->noteParJoueur[$joueurQuiJoue]--;//NOTATION 2019
							$joueurQuiRelance=$joueurQuiJoue;
							$this->lesDes[0]--;
							$finTour=true;
						}
						else{
							if ($this->nbDesParJoueur[$joueurAvant]==2) $deuxUn=true;
							$this->nbDesParJoueur[$joueurAvant]=$this->joueurs[$joueurAvant]->perdreUnDe();//celui qui jouait avant le joueur en cours
							$this->noteParJoueur[$joueurAvant]--; //NOTATION 2019 il a bluffé, il a perdu un dé, il perd un point 
							//CSS
							//tousLesLabels[joueurAvant][nbDesParJoueur[joueurAvant]].setText("X");
							//tousLesLabels[joueurAvant][nbDesParJoueur[joueurAvant]].setForeground(Color.red);
							$this->joueursElimines[$joueurAvant]=($this->nbDesParJoueur[$joueurAvant]==0); 
							$this->noteParJoueur[$joueurQuiJoue]++; //NOTATION 2019
							$joueurQuiRelance=$joueurAvant;
							$this->lesDes[0]--;
							$finTour=true;
						}
					}
					
				}
				else
					if($reponseJoueur[1]==-1){ // si c'est "exactement"
						//le joueur qui rejouera sera forcément celui qui joue actuellement
						//CSS
						echo "Joueur ".$this->joueurs[$joueurQuiJoue]->getName()." dit exactement !"; 
						$joueurQuiRelance=$joueurQuiJoue;
						if(!$palifico){//si on est pas en paco ou pas
							if ($val!=1) $this->total = $this->lesDes[$val]+$this->lesDes[1];
							else $this->total = $this->lesDes[1];
                            
							if ($qte==$this->total){ //si c'est bien exactement
                                $mem=$this->nbDesParJoueur[$joueurQuiJoue];
								$this->nbDesParJoueur[$joueurQuiJoue]=$this->joueurs[$joueurQuiJoue]->gagnerUnDe();//celui qui joue gagne
								$this->noteParJoueur[$joueurQuiJoue]++; //NOTATION 2019
								if($mem>$this->nbDesParJoueur[$joueurQuiJoue]) $this->lesDes[0]++;
								$finTour=true;
							}
							else{//si c'est pas exactement
								if ($this->nbDesParJoueur[$joueurQuiJoue]==2) $deuxUn=true;
								$this->nbDesParJoueur[$joueurQuiJoue]=$this->joueurs[$joueurQuiJoue]->perdreUnDe(); //celui qui joue perd    
								$this->noteParJoueur[$joueurQuiJoue]--; //NOTATION 2019
								//CSS
								//tousLesLabels[joueurQuiJoue][nbDesParJoueur[joueurQuiJoue]].setText("X");
								//tousLesLabels[joueurQuiJoue][nbDesParJoueur[joueurQuiJoue]].setForeground(Color.red);
								$this->joueursElimines[$joueurQuiJoue]=($this->nbDesParJoueur[$joueurQuiJoue]==0);
								//$this->noteParJoueur[$joueurAvant]++; //l'autre gagne donc un point NOTATION 2019
								$this->lesDes[0]--;
								$finTour=true;
							}
						}
						else{ //en cas de palifico
							if ($qte==$this->lesDes[$val]){ //si c'est bien exactement
                                $mem=$this->nbDesParJoueur[$joueurQuiJoue];
								$this->nbDesParJoueur[$joueurQuiJoue]=$this->joueurs[$joueurQuiJoue]->gagnerUnDe();//celui qui joue gagne
								$this->noteParJoueur[$joueurQuiJoue]++; //NOTATION 2019
								if($mem>$this->nbDesParJoueur[$joueurQuiJoue]) $this->lesDes[0]++;
								$finTour=true;
							}
							else{//si c'est pas exactement
								if ($this->nbDesParJoueur[$joueurQuiJoue]==2) $deuxUn=true;
								$this->nbDesParJoueur[$joueurQuiJoue]=$this->joueurs[$joueurQuiJoue]->perdreUnDe(); 
								$this->noteParJoueur[$joueurQuiJoue]--; //NOTATION 2019
								//CSS
								//tousLesLabels[joueurQuiJoue][nbDesParJoueur[joueurQuiJoue]].setText("X");
								//tousLesLabels[joueurQuiJoue][nbDesParJoueur[joueurQuiJoue]].setForeground(Color.red);
								$this->joueursElimines[$joueurQuiJoue]=($this->nbDesParJoueur[$joueurQuiJoue]==0);
								//$this->noteParJoueur[$joueurAvant]++; //NOTATION 2019
								$this->lesDes[0]--;
								$finTour=true;
							}
						}

					}

				}//fin if
				//Compter combien de joueurs restent à la fin du tour
				$nbJoueursActifs=0;
				for ($i=0;$i<4;$i++)
					if(!$this->joueursElimines[$i])
						$nbJoueursActifs++;
					else if($joueurApres==$i) $joueurApres=($joueurApres+1)%4;
				
				$qte=$reponseJoueur[0];
				$val=$reponseJoueur[1];
				$joueurQuiJoue=$joueurApres;
				
				//evaluer si prochain tour est palifico
                if($finTour){
                    if($deuxUn){ $palifico=true; echo " => palifico !";} //CSS
                    else $palifico=false;
                    $deuxUn=false;
                }
			}//finTour
			
			     fwrite($fichier, '}');
			//tester si fin de partie (s'il ne reste qu'un seul joueur)
			$finPartie=($nbJoueursActifs==1);
			//System.out.println("fin de partie ? "+finPartie);
			//System.out.println("qui relance ? "+joueurQuiRelance);
			fwrite($fichier, '}');	
		}//finPartie
    
       
		echo "<br><br>Bilan du match :<br>"; //CSS
                fwrite($fichier, '}, "notes" : [');
    $gagnant =0;
		for($i=0;$i<4;$i++){
                fwrite($fichier, $this->noteParJoueur[$i]);
                if ($i<3) fwrite($fichier, ',');
                else fwrite($fichier, '],');
			echo "joueur ".$this->joueurs[$i]->getName()." : "; //REVOIR
			if($this->joueursElimines[$i]) {echo "perdu. Note : ".$this->noteParJoueur[$i]."<br>";
                                           }
			else {
				$this->noteParJoueur[$i]++; //gain en cas de victoire NOTATION 2019
				echo "Victoire. Note : ".$this->noteParJoueur[$i]."<br>";
                $gagnant = $i;
			}
		
		}
    
                fwrite($fichier, '"gagnant" : '.$gagnant);
		
                fwrite($fichier, '}');	
		fclose($fichier);
        }//fin main
}//fin classe
?>
<?php

abstract class Joueur {
	protected $name;
	protected $nbDes;
	protected $mesDes;
	
	public function __construct($nom){//AB
		$this->name=$nom;
		$this->nbDes=5;
		$this->mesDes=array(rand(1,6), rand(1,6),rand(1,6),rand(1,6),rand(1,6));
	}
	
	public function getName(){
		return $this->name;
	}
	
	public function getNbDes(){
		return $this->nbDes;
  	}
	
	public function perdreUnDe(){
		$this->nbDes--;
		echo "$this->name : Bouhh je perds un dé";
		return $this->nbDes;
		
	}
	
	public function gagnerUnDe(){
		$this->nbDes++;
		if ($this->nbDes>5)
			$this->nbDes=5;
        else echo "$this->nom : Yes! Je gagne un dé";
		return $this->nbDes;
	}

	public function lancerlesDes(){
		for ($i=0;$i<$this->nbDes;$i++){
			$this->mesDes[$i]=rand(1,6);
		}
		return $this->mesDes;
	}
    
    

	abstract protected function historique($coupsJoues,$nbDesParJoueur);
	//$coupsJoues est un tableau de l'historique des coups
	//$nbDesParJoueur est un tableau d'entiers
	

	abstract protected function evaluer($qte, $val, $palifico, $nbDes);
	//$palifico est un booleen, retourne un tableau de 2 cases contenant la nouvelle quantité et la nouvelle valeur
	
}
?>
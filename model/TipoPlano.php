<?php

class TipoPlano {

    private  $id;
    private  $titulo;
    private  $idOperadora;
    private  $idCategoriaPlano;
    private  $reembolso;

    // public function __construct($id, $titulo, $idOperadora, $idCategoriaPlano, $reembolso){
    //     $this->id = $id;
    //     $this->titulo = $titulo;
    //     $this->idOperadora = $idOperadora;
    //     $this->idCategoriaPlano = $idCategoriaPlano;
    //     $this->reembolso = $reembolso;
    // }

    public function getId(){
		return $this->id;
	}

	public function setId($id){
		$this->id = $id;
	}

	public function getTitulo(){
		return $this->titulo;
	}

	public function setTitulo($titulo){
		$this->titulo = $titulo;
	}

	public function getIdOperadora(){
		return $this->idOperadora;
	}

	public function setIdOperadora($idOperadora){
		$this->idOperadora = $idOperadora;
	}

	public function getIdCategoriaPlano(){
		return $this->idCategoriaPlano;
	}

	public function setIdCategoriaPlano($idCategoriaPlano){
		$this->idCategoriaPlano = $idCategoriaPlano;
	}

	public function getReembolso(){
		return $this->reembolso;
	}

	public function setReembolso($reembolso){
		$this->reembolso = $reembolso;
	}
}
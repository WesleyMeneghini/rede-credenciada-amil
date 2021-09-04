<?php

class EstadoCidadeBairro
{

    private  $idEstado;
    private  $idCidade;
    private  $idBairro;

    /**
     * Get the value of idEstado
     */ 
    public function getIdEstado()
    {
        return $this->idEstado;
    }

    /**
     * Set the value of idEstado
     *
     * @return  self
     */ 
    public function setIdEstado($idEstado)
    {
        $this->idEstado = $idEstado;

        return $this;
    }

    /**
     * Get the value of idCidade
     */ 
    public function getIdCidade()
    {
        return $this->idCidade;
    }

    /**
     * Set the value of idCidade
     *
     * @return  self
     */ 
    public function setIdCidade($idCidade)
    {
        $this->idCidade = $idCidade;

        return $this;
    }

    /**
     * Get the value of idBairro
     */ 
    public function getIdBairro()
    {
        return $this->idBairro;
    }

    /**
     * Set the value of idBairro
     *
     * @return  self
     */ 
    public function setIdBairro($idBairro)
    {
        $this->idBairro = $idBairro;

        return $this;
    }
}

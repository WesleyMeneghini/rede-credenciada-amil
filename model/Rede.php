<?php

class Rede
{

    private  $id;
    private  $nome;
    private  $idOperadora;


    /**
     * Get the value of id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set the value of id
     *
     * @return  self
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get the value of nome
     */
    public function getNome()
    {
        return $this->nome;
    }

    /**
     * Set the value of nome
     *
     * @return  self
     */
    public function setNome($nome)
    {
        $this->nome = $nome;

        return $this;
    }

    /**
     * Get the value of idOperadora
     */
    public function getIdOperadora()
    {
        return $this->idOperadora;
    }

    /**
     * Set the value of idOperadora
     *
     * @return  self
     */
    public function setIdOperadora($idOperadora)
    {
        $this->idOperadora = $idOperadora;

        return $this;
    }
}

<?php
namespace Projet\Models;

class Response {
    private $id;
    private $ordre;

    public function __construct($id, $ordre) {
        $this->id = $id;
        $this->ordre = $ordre;
    }

    public function getId() {
        return $this->id;
    }

    public function getOrdre() {
        return $this->ordre;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setOrdre($ordre) {
        $this->ordre = $ordre;
    }
}


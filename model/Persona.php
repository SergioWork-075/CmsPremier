<?php
namespace App\Model;

class Persona {

    //Variables o atributos
    var $id;
    var $persona;
    var $clave;
    var $fecha_acceso;
    var $activo;
    var $personas;
    var $equipos;

    function __construct($data=null){

        $this->id = ($data) ? $data->id : null;
        $this->persona = ($data) ? $data->persona : null;
        $this->clave = ($data) ? $data->clave : null;
        $this->fecha_acceso = ($data) ? $data->fecha_acceso : null;
        $this->activo = ($data) ? $data->activo : null;
        $this->personas = ($data) ? $data->personas : null;
        $this->equipos = ($data) ? $data->equipos : null;

    }

}
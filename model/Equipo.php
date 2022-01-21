<?php
namespace App\Model;

class Equipo
{
    //Variables o atributos
    var $id;
    var $nombre;
    var $slug;
    var $introduccion;
    var $descripcion;
    var $activo;
    var $home;
    var $fecha;
    var $provincia;
    var $imagen;

    function __construct($data=null){

        $this->id = ($data) ? $data->id : null;
        $this->nombre = ($data) ? $data->nombre : null;
        $this->slug = ($data) ? $data->slug : null;
        $this->introduccion = ($data) ? $data->introduccion : null;
        $this->descripcion = ($data) ? $data->descripcion : null;
        $this->activo = ($data) ? $data->activo : null;
        $this->home = ($data) ? $data->home : null;
        $this->fecha = ($data) ? $data->fecha : null;
        $this->provincia = ($data) ? $data->provincia : null;
        $this->imagen = ($data) ? $data->imagen : null;

    }

}
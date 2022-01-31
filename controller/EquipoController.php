<?php
namespace App\Controller;

use App\Helper\ViewHelper;
use App\Helper\DbHelper;
use App\Model\equipo;


class EquipoController
{
    var $db;
    var $view;

    function __construct()
    {
        //Conexión a la BBDD
        $dbHelper = new DbHelper();
        $this->db = $dbHelper->db;

        //Instancio el ViewHelper
        $viewHelper = new ViewHelper();
        $this->view = $viewHelper;
    }

    //Listado de equipos
    public function index(){

        //Permisos
        $this->view->permisos("equipos");

        //Recojo las equipos de la base de datos
        $rowset = $this->db->query("SELECT * FROM equipos ORDER BY fecha DESC");

        //Asigno resultados a un array de instancias del modelo
        $equipos = array();
        while ($row = $rowset->fetch(\PDO::FETCH_OBJ)){
            array_push($equipos,new Equipo($row));
        }

        $this->view->vista("admin","equipos/index", $equipos);

    }

    //Para activar o desactivar
    public function activar($id){

        //Permisos
        $this->view->permisos("equipos");

        //Obtengo la equipo
        $rowset = $this->db->query("SELECT * FROM equipos WHERE id='$id' LIMIT 1");
        $row = $rowset->fetch(\PDO::FETCH_OBJ);
        $equipo = new Equipo($row);

        if ($equipo->activo == 1){

            //Desactivo la equipo
            $consulta = $this->db->exec("UPDATE equipos SET activo=0 WHERE id='$id'");

            //Mensaje y redirección
            ($consulta > 0) ? //Compruebo consulta para ver que no ha habido errores
                $this->view->redireccionConMensaje("admin/equipos","green","El equipo <strong>$equipo->nombre</strong> se ha desactivado correctamente.") :
                $this->view->redireccionConMensaje("admin/equipos","red","Hubo un error al guardar en la base de datos.");
        }

        else{

            //Activo la equipo
            $consulta = $this->db->exec("UPDATE equipos SET activo=1 WHERE id='$id'");

            //Mensaje y redirección
            ($consulta > 0) ? //Compruebo consulta para ver que no ha habido errores
                $this->view->redireccionConMensaje("admin/equipos","green","El equipo <strong>$equipo->nombre</strong> se ha activado correctamente.") :
                $this->view->redireccionConMensaje("admin/equipos","red","Hubo un error al guardar en la base de datos.");
        }

    }

    //Para mostrar o no en la home
    public function home($id){

        //Permisos
        $this->view->permisos("equipos");

        //Obtengo la equipo
        $rowset = $this->db->query("SELECT * FROM equipos WHERE id='$id' LIMIT 1");
        $row = $rowset->fetch(\PDO::FETCH_OBJ);
        $equipo = new Equipo($row);

        if ($equipo->home == 1){

            //Quito la equipo de la home
            $consulta = $this->db->exec("UPDATE equipos SET home=0 WHERE id='$id'");

            //Mensaje y redirección
            ($consulta > 0) ? //Compruebo consulta para ver que no ha habido errores
                $this->view->redireccionConMensaje("admin/equipos","green","El equipo <strong>$equipo->nombre</strong> ya no se muestra en la home.") :
                $this->view->redireccionConMensaje("admin/equipos","red","Hubo un error al guardar en la base de datos.");
        }

        else{

            //Muestro la equipo en la home
            $consulta = $this->db->exec("UPDATE equipos SET home=1 WHERE id='$id'");

            //Mensaje y redirección
            ($consulta > 0) ? //Compruebo consulta para ver que no ha habido errores
                $this->view->redireccionConMensaje("admin/equipos","green","El equipo <strong>$equipo->nombre</strong> ahora se muestra en la home.") :
                $this->view->redireccionConMensaje("admin/equipos","red","Hubo un error al guardar en la base de datos.");
        }

    }

    public function borrar($id){

        //Permisos
        $this->view->permisos("equipos");

        //Obtengo la equipo
        $rowset = $this->db->query("SELECT * FROM equipos WHERE id='$id' LIMIT 1");
        $row = $rowset->fetch(\PDO::FETCH_OBJ);
        $equipo = new Equipo($row);

        //Borro la equipo
        $consulta = $this->db->exec("DELETE FROM equipos WHERE id='$id'");

        //Borro la imagen asociada
        $archivo = $_SESSION['public']."img/".$equipo->imagen;
        $descripcion_imagen = "";
        if (is_file($archivo)){
            unlink($archivo);
            $descripcion_imagen = " y se ha borrado la imagen asociada";
        }

        //Mensaje y redirección
        ($consulta > 0) ? //Compruebo consulta para ver que no ha habido errores
            $this->view->redireccionConMensaje("admin/equipos","green","El equipo se ha borrado correctamente$descripcion_imagen.") :
            $this->view->redireccionConMensaje("admin/equipos","red","Hubo un error al guardar en la base de datos.");

    }

    public function crear(){

        //Permisos
        $this->view->permisos("equipos");

        //Creo un nuevo usuario vacío
        $equipo = new Equipo();

        //Llamo a la ventana de edición
        $this->view->vista("admin","equipos/editar", $equipo);

    }

    public function editar($id){

        //Permisos
        $this->view->permisos("equipos");

        //Si ha pulsado el botón de guardar
        if (isset($_POST["guardar"])){

            //Recupero los datos del formulario
            $nombre = filter_input(INPUT_POST, "nombre", FILTER_SANITIZE_STRING);
            $introduccion = filter_input(INPUT_POST, "introduccion", FILTER_SANITIZE_STRING);
            $provincia = filter_input(INPUT_POST, "provincia", FILTER_SANITIZE_STRING);
            $fecha = filter_input(INPUT_POST, "fecha", FILTER_SANITIZE_STRING);
            $descripcion = filter_input(INPUT_POST, "descripcion", FILTER_SANITIZE_FULL_SPECIAL_CHARS);

            //Formato de fecha para SQL
            $fecha = \DateTime::createFromFormat("d-m-Y", $fecha)->format("Y-m-d H:i:s");

            //Genero slug (url amigable)
            $slug = $this->view->getSlug($nombre);

            //Imagen
            $imagen_recibida = $_FILES['imagen'];
            $imagen = ($_FILES['imagen']['name']) ? $_FILES['imagen']['name'] : "";
            $imagen_subida = ($_FILES['imagen']['name']) ? '/var/www/html'.$_SESSION['public']."img/".$_FILES['imagen']['name'] : "";
            $descripcion_img = ""; //Para el mensaje

            if ($id == "nuevo"){

                //Creo una nueva equipo
                $consulta = $this->db->exec("INSERT INTO equipos 
                    (nombre, introduccion, provincia, fecha, descripcion, slug, imagen) VALUES 
                    ('$nombre','$introduccion','$provincia','$fecha','$descripcion','$slug','$imagen')");

                //Subo la imagen
                if ($imagen){
                    if (is_uploaded_file($imagen_recibida['tmp_name']) && move_uploaded_file($imagen_recibida['tmp_name'], $imagen_subida)){
                        $descripcion_img = " La imagen se ha subido correctamente.";
                    }
                    else{
                        $descripcion_img = " Hubo un problema al subir la imagen.";
                    }
                }

                //Mensaje y redirección
                ($consulta > 0) ?
                    $this->view->redireccionConMensaje("admin/equipos","green","El equipo <strong>$nombre</strong> se creado correctamente.".$descripcion_img) :
                    $this->view->redireccionConMensaje("admin/equipos","red","Hubo un error al guardar en la base de datos.");
            }
            else{

                //Actualizo la equipo
                $this->db->exec("UPDATE equipos SET 
                    nombre='$nombre',introduccion='$introduccion',provincia='$provincia',
                    fecha='$fecha',descripcion='$descripcion',slug='$slug' WHERE id='$id'");

                //Subo y actualizo la imagen
                if ($imagen){
                    if (is_uploaded_file($imagen_recibida['tmp_name']) && move_uploaded_file($imagen_recibida['tmp_name'], $imagen_subida)){
                        $descripcion_img = " La imagen se ha subido correctamente.";
                        $this->db->exec("UPDATE equipos SET imagen='$imagen' WHERE id='$id'");
                    }
                    else{
                        $descripcion_img = " Hubo un problema al subir la imagen.";
                    }
                }

                //Mensaje y redirección
                $this->view->redireccionConMensaje("admin/equipos","green","El equipo <strong>$nombre</strong> se guardado correctamente.".$descripcion_img);

            }
        }

        //Si no, obtengo equipo y muestro la ventana de edición
        else{

            //Obtengo la equipo
            $rowset = $this->db->query("SELECT * FROM equipos WHERE id='$id' LIMIT 1");
            $row = $rowset->fetch(\PDO::FETCH_OBJ);
            $equipo = new Equipo($row);

            //Llamo a la ventana de edición
            $this->view->vista("admin","equipos/editar", $equipo);
        }

    }

}
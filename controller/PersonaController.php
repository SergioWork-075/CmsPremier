<?php
namespace App\Controller;

use App\Helper\ViewHelper;
use App\Helper\DbHelper;
use App\Model\Persona;


class PersonaController
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

    public function admin(){

        //Compruebo permisos
        $this->view->permisos();

        //LLamo a la vista
        $this->view->vista("admin","index");

    }

    public function entrar(){

        //Si ya está autenticado, le llevo a la página de inicio del panel
        if (isset($_SESSION['Persona'])){

            $this->admin();

        }
        //Si ha pulsado el botón de acceder, tramito el formulario
        else if (isset($_POST["acceder"])){

            //Recupero los datos del formulario
            $campo_Persona = filter_input(INPUT_POST, "Persona", FILTER_SANITIZE_STRING);
            $campo_clave = filter_input(INPUT_POST, "clave", FILTER_SANITIZE_STRING);

            //Busco al Persona en la base de datos
            $rowset = $this->db->query("SELECT * FROM Personas WHERE Persona='$campo_Persona' AND activo=1 LIMIT 1");

            //Asigno resultado a una instancia del modelo
            $row = $rowset->fetch(\PDO::FETCH_OBJ);
            $Persona = new Persona($row);

            //Si existe el Persona
            if ($Persona){
                //Compruebo la clave
                if (password_verify($campo_clave,$Persona->clave)) {

                    //Asigno el Persona y los permisos la sesión
                    $_SESSION["Persona"] = $Persona->Persona;
                    $_SESSION["Personas"] = $Persona->Personas;
                    $_SESSION["equipos"] = $Persona->equipos;

                    //Guardo la fecha de último acceso
                    $ahora = new \DateTime("now", new \DateTimeZone("Europe/Madrid"));
                    $fecha = $ahora->format("Y-m-d H:i:s");
                    $this->db->exec("UPDATE Personas SET fecha_acceso='$fecha' WHERE Persona='$campo_Persona'");

                    //Redirección con mensaje
                    $this->view->redireccionConMensaje("admin","green","Bienvenido al panel de administración.");
                }
                else{
                    //Redirección con mensaje
                    $this->view->redireccionConMensaje("admin","red","Contraseña incorrecta.");
                }
            }
            else{
                //Redirección con mensaje
                $this->view->redireccionConMensaje("admin","red","No existe ningún Persona con ese nombre.");
            }
        }
        //Le llevo a la página de acceso
        else{
            $this->view->vista("admin","Personas/entrar");
        }

    }

    public function salir(){

        //Borro al Persona de la sesión
        unset($_SESSION['Persona']);

        //Redirección con mensaje
        $this->view->redireccionConMensaje("admin","green","Te has desconectado con éxito.");

    }

    //Listado de Personas
    public function index(){

        //Permisos
        $this->view->permisos("Personas");

        //Recojo los Personas de la base de datos
        $rowset = $this->db->query("SELECT * FROM Personas ORDER BY Persona ASC");

        //Asigno resultados a un array de instancias del modelo
        $Personas = array();
        while ($row = $rowset->fetch(\PDO::FETCH_OBJ)){
            array_push($Personas,new Persona($row));
        }

        $this->view->vista("admin","Personas/index", $Personas);

    }

    //Para activar o desactivar
    public function activar($id){

        //Permisos
        $this->view->permisos("Personas");

        //Obtengo el Persona
        $rowset = $this->db->query("SELECT * FROM Personas WHERE id='$id' LIMIT 1");
        $row = $rowset->fetch(\PDO::FETCH_OBJ);
        $Persona = new Persona($row);

        if ($Persona->activo == 1){

            //Desactivo el Persona
            $consulta = $this->db->exec("UPDATE Personas SET activo=0 WHERE id='$id'");

            //Mensaje y redirección
            ($consulta > 0) ? //Compruebo consulta para ver que no ha habido errores
                $this->view->redireccionConMensaje("admin/Personas","green","El Persona <strong>$Persona->Persona</strong> se ha desactivado correctamente.") :
                $this->view->redireccionConMensaje("admin/Personas","red","Hubo un error al guardar en la base de datos.");
        }

        else{

            //Activo el Persona
            $consulta = $this->db->exec("UPDATE Personas SET activo=1 WHERE id='$id'");

            //Mensaje y redirección
            ($consulta > 0) ? //Compruebo consulta para ver que no ha habido errores
                $this->view->redireccionConMensaje("admin/Personas","green","El Persona <strong>$Persona->Persona</strong> se ha activado correctamente.") :
                $this->view->redireccionConMensaje("admin/Personas","red","Hubo un error al guardar en la base de datos.");
        }

    }

    public function borrar($id){

        //Permisos
        $this->view->permisos("Personas");

        //Borro el Persona
        $consulta = $this->db->exec("DELETE FROM Personas WHERE id='$id'");

        //Mensaje y redirección
        ($consulta > 0) ? //Compruebo consulta para ver que no ha habido errores
            $this->view->redireccionConMensaje("admin/Personas","green","El Persona se ha borrado correctamente.") :
            $this->view->redireccionConMensaje("admin/Personas","red","Hubo un error al guardar en la base de datos.");

    }

    public function crear(){

        //Permisos
        $this->view->permisos("Personas");

        //Creo un nuevo Persona vacío
        $Persona = new Persona();

        //Llamo a la ventana de edición
        $this->view->vista("admin","Personas/editar", $Persona);

    }

    public function editar($id){

        //Permisos
        $this->view->permisos("Personas");

        //Si ha pulsado el botón de guardar
        if (isset($_POST["guardar"])){

            //Recupero los datos del formulario
            $Persona = filter_input(INPUT_POST, "Persona", FILTER_SANITIZE_STRING);
            $clave = filter_input(INPUT_POST, "clave", FILTER_SANITIZE_STRING);
            $Personas = (filter_input(INPUT_POST, 'Personas', FILTER_SANITIZE_STRING) == 'on') ? 1 : 0;
            $equipos = (filter_input(INPUT_POST, 'equipos', FILTER_SANITIZE_STRING) == 'on') ? 1 : 0;
            $cambiar_clave = (filter_input(INPUT_POST, 'cambiar_clave', FILTER_SANITIZE_STRING) == 'on') ? 1 : 0;

            //Encripto la clave
            $clave_encriptada = ($clave) ? password_hash($clave,  PASSWORD_BCRYPT, ['cost'=>12]) : "";

            if ($id == "nuevo"){

                //Creo un nuevo Persona
                $this->db->exec("INSERT INTO Personas (Persona, clave, equipos, Personas) VALUES ('$Persona','$clave_encriptada',$equipos,$Personas)");

                //Mensaje y redirección
                $this->view->redireccionConMensaje("admin/Personas","green","El Persona <strong>$Persona</strong> se creado correctamente.");
            }
            else{

                //Actualizo el Persona
                ($cambiar_clave) ?
                    $this->db->exec("UPDATE Personas SET Persona='$Persona',clave='$clave_encriptada',equipos=$equipos,Personas=$Personas WHERE id='$id'") :
                    $this->db->exec("UPDATE Personas SET Persona='$Persona',equipos=$equipos,Personas=$Personas WHERE id='$id'");

                //Mensaje y redirección
                $this->view->redireccionConMensaje("admin/Personas","green","El Persona <strong>$Persona</strong> se actualizado correctamente.");
            }
        }

        //Si no, obtengo Persona y muestro la ventana de edición
        else{

            //Obtengo el Persona
            $rowset = $this->db->query("SELECT * FROM Personas WHERE id='$id' LIMIT 1");
            $row = $rowset->fetch(\PDO::FETCH_OBJ);
            $Persona = new Persona($row);

            //Llamo a la ventana de edición
            $this->view->vista("admin","Personas/editar", $Persona);
        }

    }

}
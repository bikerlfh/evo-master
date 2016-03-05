<?php
namespace Application\Model\Clases;

class ValoresSesion
{
    // Contenedor de la instancia del singleton
    private static $instancia;
    public static $idUsuarioSesion;
    public static $username;
    public static $tipousuario;
    
    private function __construct() {
	
    }
    
    // método singleton
    public static function obtenerInstancia()
    {
        if (!isset(self::$instancia)) {
            $miclase = __CLASS__;
            self::$instancia = new $miclase;
        } 
        return self::$instancia;
    }
    
    public static function destruirSesion()
    {
        self::$instancia =  null;
    }
    
    // Evita que el objeto se pueda clonar
    public function __clone()
    {
        trigger_error('La clonación de este objeto no está permitida', E_USER_ERROR);
    }
}
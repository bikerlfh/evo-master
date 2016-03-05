<?php
namespace Application\Model\Clases;
use Zend\Session\Container;
class ValoresSesion
{
    // Contenedor de la instancia del singleton
    private static $instancia;
    public static $idUsuarioSesion;
    public static $username;
    public static $tipousuario;
    public static $Container;
    
    private function __construct() {
    }
 
   //Private clone method to prevent cloning of the instance of the  Singleton instance.
    private function __clone() {
    }
    
    // método singleton
    public static function obtenerInstancia()
    {
        if (!isset(self::$instancia)) {
            self::$instancia = new ValoresSesion();
        }
        return self::$instancia;
    }
    
    public static function destruirSesion()
    {
        self::$instancia =  null;
    }
}
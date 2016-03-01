<?php
namespace Application\Model\Entity;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Adapter\Adapter;

class Usuario extends AbstractTableGateway
{
    private $idUsuario;
    private $idTipoUsuario;
    Private $idDatoBasicoTercero;
    private $clave;
    private $email;
    public function __construct(Adapter $adapter = null)
    {
        $this->adapter = $adapter;
        $this->table =  new \Zend\Db\Sql\TableIdentifier('Usuario', 'Seguridad');
    }

    public function getclave(){
        return $this->clave;
    }
    public function setclave($clave){
        $this->clave=$clave;
    }
    public function getemail(){
        return $this->email;
    }
    public function setemail($email){
        $this->email=$email;
    }
    public function getidDatoBasicoTercero(){
        return $this->idDatoBasicoTercero;
    }
    public function setidDatoBasicoTercero($idDatoBasicoTercero){
        $this->idDatoBasicoTercero=$idDatoBasicoTercero;
    }
    public function getidTipoUsuario(){
        return $this->idTipoUsuario;
    }
    public function setidTipoUsuario($idTipoUsuario){
        $this->idTipoUsuario=$idTipoUsuario;
    }
    public function getidUsuario(){
        return $this->idUsuario;
    }
    public function setidUsuario($idUsuario){
        $this->idUsuario=$idUsuario;
    }

    public function guardarUsuario($clave,$email,$idDatoBasicoTercero,$idTipoUsuario)
    {
        $datos=array(
                'clave'=> $clave,
                'email'=> $email,
                'idDatoBasicoTercero'=> $idDatoBasicoTercero,
                'idTipoUsuario'=> $idTipoUsuario,
                'idUsuario'=> $idUsuario
        );
        $result=$this->insert($datos);
        if($result>0)
            return true;
        return false;
    }

    public function modificarUsuario($clave,$email,$idDatoBasicoTercero,$idTipoUsuario,$idUsuario)
    {
        $datos=array(
                'clave'=> $clave,
                'email'=> $email,
                'idDatoBasicoTercero'=> $idDatoBasicoTercero,
                'idTipoUsuario'=> $idTipoUsuario,
                'idUsuario'=> $idUsuario
        );
        $result=$this->update($datos,array('idUsuario'=>$idUsuario));
        if($result>0)
            return true;
        return false;
    }

    public function consutlarTodoUsuario()
    {
        return $this->select()->toArray();
    }
    public function consultarUsuarioPoridUsuario($idUsuario)
    {
        $result=$this->select(array('idusuario'=>$idUsuario))->current();
        if($result)
        {
            $this->clave=$result['clave'];
            $this->email=$result['email'];
            $this->idDatoBasicoTercero=$result['idDatoBasicoTercero'];
            $this->idTipoUsuario=$result['idTipoUsuario'];
            $this->idUsuario=$result['idUsuario'];
            return true;
        }
        return false;
    }
}
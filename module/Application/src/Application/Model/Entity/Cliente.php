<?php
namespace Application\Model\Entity;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Adapter\Adapter;

class Cliente extends AbstractTableGateway
{
    private $idCliente;
    private $idDatoBasicoTercero;
    private $idMunicipio;
    private $email;
    private $direccion;
    private $telefono;
    
    public function __construct(Adapter $adapter = null)
    {
        $this->adapter = $adapter;
        $this->table =  new \Zend\Db\Sql\TableIdentifier('Cliente', 'Tercero');
    }

    public function gettelefono(){
        return $this->telefono;
    }
    public function settelefono($telefono){
        $this->telefono=$telefono;
    }
    public function getdireccion(){
        return $this->direccion;
    }
    public function setdireccion($direccion){
        $this->direccion=$direccion;
    }
    public function getemail(){
        return $this->email;
    }
    public function setemail($email){
        $this->email=$email;
    }
    public function getidMunicipio(){
        return $this->idMunicipio;
    }
    public function setidMunicipio($idMunicipio){
        $this->idMunicipio=$idMunicipio;
    }
    public function getidDatoBasicoTercero(){
        return $this->idDatoBasicoTercero;
    }
    public function setidDatoBasicoTercero($idDatoBasicoTercero){
        $this->idDatoBasicoTercero=$idDatoBasicoTercero;
    }
    public function getidCliente(){
        return $this->idCliente;
    }
    public function setidCliente($idCliente){
        $this->idCliente=$idCliente;
    }

    public function guardarCliente($idDatoBasicoTercero,$idMunicipio,$email,$direccion,$telefono)
    {
        $datos=array(
                'telefono'=> $telefono,
                'direccion'=> $direccion,
                'email'=> $email,
                'idMunicipio'=> $idMunicipio,
                'idDatoBasicoTercero'=> $idDatoBasicoTercero,
                'idCliente'=> $idCliente
        );
        $result=$this->insert($datos);
        if($result>0)
            return true;
        return false;
    }

    public function modificarCliente($idCliente,$idDatoBasicoTercero,$idMunicipio,$email,$direccion,$telefono)
    {
        $datos=array(
                'telefono'=> $telefono,
                'direccion'=> $direccion,
                'email'=> $email,
                'idMunicipio'=> $idMunicipio,
                'idDatoBasicoTercero'=> $idDatoBasicoTercero
        );
        $result=$this->update($datos,array('idCliente'=>$idCliente));
        if($result>0)
            return true;
        return false;
    }

    public function consultarTodoCliente()
    {
        return $this->select()->toArray();
    }
    public function consultarClientePoridCliente($idCliente)
    {
        $result=$this->select(array('idcliente'=>$idCliente))->current();
        if($result)
        {
            $this->LlenarEntidad($result);
            return true;
        }
        return false;
    }
    public function consultarClientePoridDatoBasicoTercero($idDatoBasicoTercero)
    {
        $result=$this->select(array('iddatobasicotercero'=>$idDatoBasicoTercero))->current();
        if($result)
        {
            $this->LlenarEntidad($result);
            return true;
        }
        return false;
    }
    public function consultarClientePoridMunicipio($idMunicipio)
    {
        $result=$this->select(array('idmunicipio'=>$idMunicipio))->current();
        if($result)
        {
            $this->LlenarEntidad($result);
            return true;
        }
        return false;
    }
    private function LlenarEntidad($result)
    {
        $this->idCliente=$result['idCliente'];
        $this->idDatoBasicoTercero=$result['idDatoBasicoTercero'];
        $this->idMunicipio=$result['idMunicipio'];
        $this->email=$result['email'];
        $this->direccion=$result['direccion'];
        $this->telefono=$result['telefono'];
    }
}
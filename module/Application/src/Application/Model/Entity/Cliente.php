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

    public function getTelefono(){
        return $this->telefono;
    }
    public function setTelefono($telefono){
        $this->telefono=$telefono;
    }
    public function getDireccion(){
        return $this->direccion;
    }
    public function setDireccion($direccion){
        $this->direccion=$direccion;
    }
    public function getEmail(){
        return $this->email;
    }
    public function setEmail($email){
        $this->email=$email;
    }
    public function getIdMunicipio(){
        return $this->idMunicipio;
    }
    public function setIdMunicipio($idMunicipio){
        $this->idMunicipio=$idMunicipio;
    }
    public function getIdDatoBasicoTercero(){
        return $this->idDatoBasicoTercero;
    }
    public function setIdDatoBasicoTercero($idDatoBasicoTercero){
        $this->idDatoBasicoTercero=$idDatoBasicoTercero;
    }
    public function getIdCliente(){
        return $this->idCliente;
    }
    public function setIdCliente($idCliente){
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
    public function consultarClientePorIdCliente($idCliente)
    {
        $result=$this->select(array('idcliente'=>$idCliente))->current();
        if($result)
        {
            $this->LlenarEntidad($result);
            return true;
        }
        return false;
    }
    public function consultarClientePorIdDatoBasicoTercero($idDatoBasicoTercero)
    {
        $result=$this->select(array('iddatobasicotercero'=>$idDatoBasicoTercero))->current();
        if($result)
        {
            $this->LlenarEntidad($result);
            return true;
        }
        return false;
    }
    public function consultarClientePorIdMunicipio($idMunicipio)
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
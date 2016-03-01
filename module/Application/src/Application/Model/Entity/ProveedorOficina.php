<?php
namespace Application\Model\Entity;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Adapter\Adapter;

class ProveedorOficina extends AbstractTableGateway
{

    private $idProveedorOficina;
    private $idProveedor;
    private $idMunicipio;
    private $email;
    private $direccion;
    private $telefono;
    
    public function __construct(Adapter $adapter = null)
    {
        $this->adapter = $adapter;
        $this->table =  new \Zend\Db\Sql\TableIdentifier('ProveedorOficina', 'Tercero');
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
    public function getidProveedor(){
        return $this->idProveedor;
    }
    public function setidProveedor($idProveedor){
        $this->idProveedor=$idProveedor;
    }
    public function getidProveedorOficina(){
        return $this->idProveedorOficina;
    }
    public function setidProveedorOficina($idProveedorOficina){
        $this->idProveedorOficina=$idProveedorOficina;
    }

    public function guardarProveedoroficina($idProveedor,$idMunicipio,$email,$direccion,$telefono)
    {
        $datos=array(
                'telefono'=> $telefono,
                'direccion'=> $direccion,
                'email'=> $email,
                'idMunicipio'=> $idMunicipio,
                'idProveedor'=> $idProveedor
        );
        $result=$this->insert($datos);
        if($result>0)
            return true;
        return false;
    }

    public function modificarProveedoroficina($idProveedorOficina,$idProveedor,$idMunicipio,$email,$direccion,$telefono)
    {
        $datos=array(
                'telefono'=> $telefono,
                'direccion'=> $direccion,
                'email'=> $email,
                'idMunicipio'=> $idMunicipio,
                'idProveedor'=> $idProveedor
        );
        $result=$this->update($datos,array('idProveedorOficina'=>$idProveedorOficina));
        if($result>0)
            return true;
        return false;
    }

    public function consultarProveedoroficina()
    {
        return $this->select()->toArray();
    }
    public function consultarroveedoroficinaPoridProveedorOficina($idProveedorOficina)
    {
        $result=$this->select(array('idproveedoroficina'=>$idProveedorOficina))->current();
        if($result)
        {
            $this->LlenarEntidad($result);
            return true;
        }
        return false;
    }
    public function consultarProveedoroficinaPoridProveedor($idProveedor)
    {
        $result=$this->select(array('idproveedor'=>$idProveedor))->current();
        if($result)
        {
            $this->LlenarEntidad($result);
            return true;
        }
        return false;
    }
    private function LlenarEntidad($result)
    {
        $this->telefono=$result['telefono'];
        $this->direccion=$result['direccion'];
        $this->email=$result['email'];
        $this->idMunicipio=$result['idMunicipio'];
        $this->idProveedor=$result['idProveedor'];
        $this->idProveedorOficina=$result['idProveedorOficina'];
    }
}
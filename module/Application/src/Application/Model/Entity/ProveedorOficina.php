<?php
namespace Application\Model\Entity;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Adapter\Adapter;
use Application\Model\Entity\Proveedor;
use Zend\Db\Sql\Sql;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\TableIdentifier;
use Zend\Db\Sql\Expression;

class ProveedorOficina extends AbstractTableGateway
{

    private $idProveedorOficina;
    private $idProveedor;
    private $idMunicipio;
    private $email;
    private $direccion;
    private $telefono;
    
    public $Proveedor;

    public function __construct(Adapter $adapter = null)
    {
        $this->adapter = $adapter;
        $this->table =  new \Zend\Db\Sql\TableIdentifier('ProveedorOficina', 'Tercero');
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
    public function getIdProveedor(){
        return $this->idProveedor;
    }
    public function setIdProveedor($idProveedor){
        $this->idProveedor=$idProveedor;
    }
    public function getIdProveedorOficina(){
        return $this->idProveedorOficina;
    }
    public function setIdProveedorOficina($idProveedorOficina){
        $this->idProveedorOficina=$idProveedorOficina;
    }

    public function guardarProveedorOficina($idProveedor,$idMunicipio,$email,$direccion,$telefono)
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

    public function modificarProveedorOficina($idProveedorOficina,$idProveedor,$idMunicipio,$email,$direccion,$telefono)
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
    public function eliminarProveedorOficina($idProveedorOficina)
    {
        if ($this->delete(array('idProveedorOficina'=>$idProveedorOficina))>0) 
            return true;
        return false;
    }
    
    public function consultarTodoProveedorOficina()
    {
        $sql = new Sql($this->adapter);
        $select = $sql->select()->
                from(array('po'=>  $this->table))->
                join(array("p"=> new TableIdentifier("Proveedor","Tercero")),
                                    "p.idProveedor = po.idProveedor")->
                join(array("d"=> new TableIdentifier("DatoBasicoTercero","Tercero")),
                                    "p.idDatoBasicoTercero = d.idDatoBasicoTercero",
                                    array("descripcionTercero"=> new Expression("convert(varchar,d.nit) + ' - ' + d.descripcion")))->
                join(array("m"=> new TableIdentifier("Municipio","Tercero")),
                                    "po.idMunicipio = m.idMunicipio",
                                    array("descripcionMunicipio"=> new Expression("m.codigo + ' - ' + m.descripcion")));
        
        $results = $sql->prepareStatementForSqlObject($select)->execute();
        $resultsSet = new ResultSet();
        return $resultsSet->initialize($results)->toArray();
    }
    public function consultarProveedorOficinaPorIdProveedorOficina($idProveedorOficina)
    {
        $result=$this->select(array('idProveedorOficina'=>$idProveedorOficina))->current();
        if($result)
        {
            $this->LlenarEntidad($result);
            return true;
        }
        return false;
    }
    public function consultarProveedorOficinaPorIdProveedor($idProveedor)
    {
        $result=$this->select(array('idProveedor'=>$idProveedor))->current();
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
    
    private function cargarEmbebidos()
    {
        $this->Proveedor = new Proveedor($this->adapter);
        $this->Proveedor->consultarProveedorPorIdProveedor($this->idProveedor);
    }
}
<?php
namespace Application\Model\Entity;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Adapter\Adapter;

class Departamento extends AbstractTableGateway
{
    private $descripcion;
    private $codigo;
    private $idPais;
    private $idDepartamento;
    public function __construct(Adapter $adapter = null)
    {
        $this->adapter = $adapter;
        $this->table =  new \Zend\Db\Sql\TableIdentifier('Departamento', 'Tercero');
    }

    public function getdescripcion(){
        return $this->descripcion;
    }
    public function setdescripcion($descripcion){
        $this->descripcion=$descripcion;
    }
    public function getcodigo(){
        return $this->codigo;
    }
    public function setcodigo($codigo){
        $this->codigo=$codigo;
    }
    public function getidPais(){
        return $this->idPais;
    }
    public function setidPais($idPais){
        $this->idPais=$idPais;
    }
    public function getidDepartamento(){
        return $this->idDepartamento;
    }
    public function setidDepartamento($idDepartamento){
        $this->idDepartamento=$idDepartamento;
    }

    public function guardarDepartamento($idPais,$codigo,$descripcion)
    {
        $datos=array(
                'descripcion'=> $descripcion,
                'codigo'=> $codigo,
                'idPais'=> $idPais
        );
        $result=$this->insert($datos);
        if($result>0)
            return true;
        return false;
    }

    public function modificarDepartamento($idDepartamento,$idPais,$codigo,$descripcion)
    {
        $datos=array(
                'descripcion'=> $descripcion,
                'codigo'=> $codigo,
                'idPais'=> $idPais
        );
        $result=$this->update($datos,array('idDepartamento'=>$idDepartamento));
        if($result>0)
            return true;
        return false;
    }
    public function eliminarDepartamento($idDepartamento)
    {
        if ($this->delete(array('idDepartamento'=>$idDepartamento))>0) 
            return true;
        return false;
    }
    public function consultarTodoDepartamento()
    {
        return $this->select()->toArray();
    }
    public function consultarDepartamentoPoridDepartamento($idDepartamento)
    {
        $result=$this->select(array('iddepartamento'=>$idDepartamento))->current();
        if($result)
        {
            $this->LlenarEntidad($result);           
            return true;
        }
        return false;
    }
    public function consultarDepartamentoPoridPais($idPais)
    {
        $result=$this->select(array('idpais'=>$idPais))->current();
        if($result)
        {
            $this->LlenarEntidad($result);       
            return true;
        }
        return false;
    }
    public function consultarDepartamentoPorcodigo($codigo)
    {
        $result=$this->select(array('codigo'=>$codigo))->current();
        if($result)
        {
            $this->LlenarEntidad($result);         
            return true;
        }
        return false;
    }
    
    private function LlenarEntidad($result)
    {
        $this->idDepartamento=$result['idDepartamento'];
        $this->idPais=$result['idPais'];
        $this->codigo=$result['codigo'];
        $this->descripcion=$result['descripcion'];
    }
}
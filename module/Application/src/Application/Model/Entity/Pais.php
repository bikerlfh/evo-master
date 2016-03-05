<?php
namespace Application\Model\Entity;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Adapter\Adapter;

class Pais extends AbstractTableGateway
{
    private $idPais;
    private $codigo;
    private $descripcion;
   
    public function __construct(Adapter $adapter = null)
    {
        $this->adapter = $adapter;
        $this->table =  new \Zend\Db\Sql\TableIdentifier('Pais', 'Tercero');
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

    public function guardarPais($codigo,$descripcion)
    {
        $datos=array(
                'descripcion'=> $descripcion,
                'codigo'=> $codigo
        );
        $result=$this->insert($datos);
        if($result>0)
            return true;
        return false;
    }

    public function modificarPais($idPais,$codigo,$descripcion)
    {
        $datos=array(
                'descripcion'=> $descripcion,
                'codigo'=> $codigo
        );
        $result=$this->update($datos,array('idPais'=>$idPais));
        if($result>0)
            return true;
        return false;
    }
    public function eliminarPais($idPais)
    {
        if ($this->delete(array('idPais'=>$idPais))>0)
            return true;
        return false;
    }

    public function consultarTodoPais()
    {
        return $this->select()->toArray();
    }
    public function consultarPaisPoridPais($idPais)
    {
        $result=$this->select(array('idPais'=>$idPais))->current();
        if($result)
        {
            $this->LlenarEntidad($result);
            return true;
        }
        return false;
    }
    public function consultarPaisPorcodigo($codigo)
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
        $this->idPais=$result['idPais'];
        $this->codigo=$result['codigo'];
        $this->descripcion=$result['descripcion'];
    }
}
<?php
namespace Application\Model\Entity;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Adapter\Adapter;

class ViaPago extends AbstractTableGateway
{
    private $idViaPago;
    private $codigo;
    private $descripcion;
    
    public function __construct(Adapter $adapter = null)
    {
        $this->adapter = $adapter;
        $this->table =  new \Zend\Db\Sql\TableIdentifier('ViaPago', 'Compra');
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
    public function getidViaPago(){
        return $this->idViaPago;
    }
    public function setidViaPago($idViaPago){
        $this->idViaPago=$idViaPago;
    }

    public function guardarViapago($codigo,$descripcion)
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

    public function modificarViapago($idViaPago,$codigo,$descripcion)
    {
        $datos=array(
                'descripcion'=> $descripcion,
                'codigo'=> $codigo
        );
        $result=$this->update($datos,array('idViaPago'=>$idViaPago));
        if($result>0)
            return true;
        return false;
    }

    public function getViapago()
    {
        return $this->select()->toArray();
    }
    public function consutlarViapagoPoridViaPago($idViaPago)
    {
        $result=$this->select(array('idviapago'=>$idViaPago))->current();
        if($result)
        {
            $this->idViaPago=$result['idviapago'];
            $this->descripcion=$result['descripcion'];
            $this->codigo=$result['codigo'];
            return true;
        }
        return false;
    }
    public function consultarViapagoPorcodigo($codigo)
    {
        $result=$this->select(array('codigo'=>$codigo))->current();
        if($result)
        {
            $this->idViaPago=$result['idviapago'];
            $this->descripcion=$result['descripcion'];
            $this->codigo=$result['codigo'];
            return true;
        }
        return false;
    }
}
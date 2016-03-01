<?php
namespace Application\Model\Entity;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Adapter\Adapter;

class EstadoPedido extends AbstractTableGateway
{
    private $idEstadoPedido;
    private $codigo;
    private $descripcion;
    
    public function __construct(Adapter $adapter = null)
    {
        $this->adapter = $adapter;
        $this->table =  new \Zend\Db\Sql\TableIdentifier('EstadoPedido', 'Compra');
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
    public function getidEstadoPedido(){
        return $this->idEstadoPedido;
    }
    public function setidEstadoPedido($idEstadoPedido){
        $this->idEstadoPedido=$idEstadoPedido;
    }

    public function insertEstadoPedido($codigo,$descripcion)
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

    public function modificarEstadoPedido($idEstadoPedido,$codigo,$descripcion)
    {
        $datos=array(
                'descripcion'=> $descripcion,
                'codigo'=> $codigo
        );
        $result=$this->update($datos,array('idEstadoPedido'=>$idEstadoPedido));
        if($result>0)
            return true;
        return false;
    }

    public function consultarEstadoPedido()
    {
        return $this->select()->toArray();
    }
    public function consultarEstadoPedidoPoridEstadoPedido($idEstadoPedido)
    {
        $result=$this->select(array('idestadopedido'=>$idEstadoPedido))->current();
        if($result)
        {
            $this->idEstadoPedido=$result['idestadopedido'];
            $this->codigo=$result['codigo'];
            $this->descripcion=$result['descripcion'];
            return true;
        }
        return false;
    }
    public function consultarEstadoPedidoPorcodigo($codigo)
    {
        $result=$this->select(array('codigo'=>$codigo))->current();
        if($result)
        {
            $this->idEstadoPedido=$result['idestadopedido'];
            $this->codigo=$result['codigo'];
            $this->descripcion=$result['descripcion'];
            return true;
        }
        return false;
    }
}
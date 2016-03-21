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

    public function getDescripcion(){
        return $this->descripcion;
    }
    public function setDescripcion($descripcion){
        $this->descripcion=$descripcion;
    }
    public function getCodigo(){
        return $this->codigo;
    }
    public function setCodigo($codigo){
        $this->codigo=$codigo;
    }
    public function getIdEstadoPedido(){
        return $this->idEstadoPedido;
    }
    public function setIdEstadoPedido($idEstadoPedido){
        $this->idEstadoPedido=$idEstadoPedido;
    }

    public function guardarEstadoPedido($codigo,$descripcion)
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
    public function eliminarEstadoPedido($idEstadoPedido)
    {
        if ($this->delete(array('idEstadoPedido'=>$idEstadoPedido))) 
            return true;
        return false;
    }
    public function consultarTodoEstadoPedido()
    {
        return $this->select()->toArray();
    }
    public function consultarEstadoPedidoPorIdEstadoPedido($idEstadoPedido)
    {
        $result=$this->select(array('idEstadoPedido'=>$idEstadoPedido))->current();
        if($result)
        {
            $this->idEstadoPedido=$result['idEstadoPedido'];
            $this->codigo=$result['codigo'];
            $this->descripcion=$result['descripcion'];
            return true;
        }
        return false;
    }
    public function consultarEstadoPedidoPorCodigo($codigo)
    {
        $result=$this->select(array('codigo'=>$codigo))->current();
        if($result)
        {
            $this->idEstadoPedido=$result['idEstadoPedido'];
            $this->codigo=$result['codigo'];
            $this->descripcion=$result['descripcion'];
            return true;
        }
        return false;
    }
    public function generarOptionsSelect($where = null)
    {
        $objs=$this->select($where)->toArray();
        $options=array(null);
        for($i=0;$i<count($objs);$i++)
        {
            $options[$objs[$i]['idEstadoPedido']]=$objs[$i]['codigo']." - ".$objs[$i]['descripcion'];
        }
        return $options;
    }
}
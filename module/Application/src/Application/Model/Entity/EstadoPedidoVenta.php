<?php
namespace Application\Model\Entity;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Adapter\Adapter;

class EstadoPedidoVenta extends AbstractTableGateway
{
    private $idEstadoPedidoVenta;
    private $codigo;
    private $descripcion;
    
    public function __construct(Adapter $adapter = null)
    {
        $this->adapter = $adapter;
        $this->table =  new \Zend\Db\Sql\TableIdentifier('EstadoPedidoVenta', 'Venta');
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
    public function getIdEstadoPedidoVenta(){
        return $this->idEstadoPedidoVenta;
    }
    public function setIdEstadoPedidoVenta($idEstadoPedidoVenta){
        $this->idEstadoPedidoVenta=$idEstadoPedidoVenta;
    }

    public function guardarEstadoPedidoVenta($codigo,$descripcion)
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

    public function modificarEstadoPedidoVenta($idEstadoPedidoVenta,$codigo,$descripcion)
    {
        $datos=array(
                'descripcion'=> $descripcion,
                'codigo'=> $codigo
        );
        $result=$this->update($datos,array('idEstadoPedidoVenta'=>$idEstadoPedidoVenta));
        if($result>0)
            return true;
        return false;
    }
    public function eliminarEstadoPedidoVenta($idEstadoPedidoVenta)
    {
        if ($this->delete(array('idEstadoPedidoVenta'=>$idEstadoPedidoVenta))) 
            return true;
        return false;
    }
    public function consultarTodoEstadoPedidoVenta()
    {
        return $this->select()->toArray();
    }
    public function consultarEstadoPedidoVentaPorIdEstadoPedidoVenta($idEstadoPedidoVenta)
    {
        $result=$this->select(array('idEstadoPedidoVenta'=>$idEstadoPedidoVenta))->current();
        if($result)
        {
            LlenarEntidad($result);
            return true;
        }
        return false;
    }
    public function consultarEstadoPedidoVentaPorcodigo($codigo)
    {
        $result=$this->select(array('codigo'=>$codigo))->current();
        if($result)
        {
            LlenarEntidad($result);
            return true;
        }
        return false;
    }
    
    private function LlenarEntidad($result)
    {
        $this->idEstadoPedidoVenta=$result['idEstadoPedidoVenta'];
        $this->codigo=$result['codigo'];
        $this->descripcion=$result['descripcion'];
    }
    
    public function generarOptionsSelect($where = null)
    {
        $objs=$this->select($where)->toArray();
        $options=array(null);
        for($i=0;$i<count($objs);$i++)
        {
            $options[$objs[$i]['idEstadoPedidoVenta']]=$objs[$i]['codigo']." - ".$objs[$i]['descripcion'];
        }
        return $options;
    }
}
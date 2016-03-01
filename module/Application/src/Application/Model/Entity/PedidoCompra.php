<?php
namespace Application\Model\Entity;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Adapter\Adapter;

class PedidoCompra extends AbstractTableGateway
{
    private $idPedidoCompra;
    private $idEstadoPedido;
    private $idProveedor;
    private $fechaPedido;
    private $idUsuarioCreacion;
    
    public function __construct(Adapter $adapter = null)
    {
        $this->adapter = $adapter;
        $this->table =  new \Zend\Db\Sql\TableIdentifier('PedidoCompra', 'Compra');
    }

    public function getidUsuarioCreacion(){
        return $this->idUsuarioCreacion;
    }
    public function setidUsuarioCreacion($idUsuarioCreacion){
        $this->idUsuarioCreacion=$idUsuarioCreacion;
    }
    public function getfechaPedido(){
        return $this->fechaPedido;
    }
    public function setfechaPedido($fechaPedido){
        $this->fechaPedido=$fechaPedido;
    }
    public function getidProveedor(){
        return $this->idProveedor;
    }
    public function setidProveedor($idProveedor){
        $this->idProveedor=$idProveedor;
    }
    public function getidEstadoPedido(){
        return $this->idEstadoPedido;
    }
    public function setidEstadoPedido($idEstadoPedido){
        $this->idEstadoPedido=$idEstadoPedido;
    }
    public function getidPedidoCompra(){
        return $this->idPedidoCompra;
    }
    public function setidPedidoCompra($idPedidoCompra){
        $this->idPedidoCompra=$idPedidoCompra;
    }

    public function guardarPedidocompra($idEstadoPedido,$idProveedor,$fechaPedido,$idUsuarioCreacion)
    {
        $datos=array(
                'idUsuarioCreacion'=> $idUsuarioCreacion,
                'fechaPedido'=> $fechaPedido,
                'idProveedor'=> $idProveedor,
                'idEstadoPedido'=> $idEstadoPedido
        );
        $result=$this->insert($datos);
        if($result>0)
            return true;
        return false;
    }

    public function modificarPedidocompra($idPedidoCompra,$idEstadoPedido,$idProveedor,$fechaPedido,$idUsuarioCreacion)
    {
        $datos=array(
                'idUsuarioCreacion'=> $idUsuarioCreacion,
                'fechaPedido'=> $fechaPedido,
                'idProveedor'=> $idProveedor,
                'idEstadoPedido'=> $idEstadoPedido
        );
        $result=$this->update($datos,array('idPedidoCompra'=>$idPedidoCompra));
        if($result>0)
            return true;
        return false;
    }

    public function consultarTodoPedidocompra()
    {
        return $this->select()->toArray();
    }
    public function consultarPedidocompraPoridPedidoCompra($idPedidoCompra)
    {
        $result=$this->select(array('idpedidocompra'=>$idPedidoCompra))->current();
        if($result)
        {
            $this->LlenarEntidad($result);
            return true;
        }
        return false;
    }
    public function consultarPedidocompraPoridEstadoPedido($idEstadoPedido)
    {
        $result=$this->select(array('idestadopedido'=>$idEstadoPedido))->current();
        if($result)
        {
            $this->LlenarEntidad($result);
            return true;
        }
        return false;
    }
    public function consultarPedidocompraPoridProveedor($idProveedor)
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
        $this->idUsuarioCreacion=$result['idUsuarioCreacion'];
        $this->fechaPedido=$result['fechaPedido'];
        $this->idProveedor=$result['idProveedor'];
        $this->idEstadoPedido=$result['idEstadoPedido'];
        $this->idPedidoCompra=$result['idPedidoCompra'];
    }
}
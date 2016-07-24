<?php
namespace Application\Model\Entity;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Adapter\Adapter;

class MovimientoInventario extends AbstractTableGateway
{
    private $idMovimientoInventario;
    private $idPedidoCompra;
    private $idPedidoVenta;
    private $fecha;
    private $idUsuarioCreacion;
    
    public $MovimientoInventarioPosicion;
    
    public function __construct(Adapter $adapter = null)
    {
        $this->adapter = $adapter;
        $this->table =  new \Zend\Db\Sql\TableIdentifier('MovimientoInventario', 'Inventario');
    }

    function getIdMovimientoInventario() {
        return $this->idMovimientoInventario;
    }

    function getIdPedidoCompra() {
        return $this->idPedidoCompra;
    }

    function getIdPedidoVenta() {
        return $this->idPedidoVenta;
    }

    function getFecha() {
        return $this->fecha;
    }

    function getIdUsuarioCreacion() {
        return $this->idUsuarioCreacion;
    }

    function setIdMovimientoInventario($idMovimientoInventario) {
        $this->idMovimientoInventario = $idMovimientoInventario;
    }

    function setIdPedidoCompra($idPedidoCompra) {
        $this->idPedidoCompra = $idPedidoCompra;
    }

    function setIdPedidoVenta($idPedidoVenta) {
        $this->idPedidoVenta = $idPedidoVenta;
    }

    function setFecha($fecha) {
        $this->fecha = $fecha;
    }

    function setIdUsuarioCreacion($idUsuarioCreacion) {
        $this->idUsuarioCreacion = $idUsuarioCreacion;
    }

   
    public function consultarTodoMovimientoInventario()
    {
        return $this->select()->toArray();
    }
    public function consultarMovimientoInventarioPorIdMovimientoInventario($idMovimientoInventario)
    {
        $result=$this->select(array('idMovimientoInventario'=>$idMovimientoInventario))->current();
        if($result)
        {
            $this->LlenarEntidad($result);
            return true;
        }
        return false;
    }
    private function LlenarMovimientoInventarioPosicion()
    {
        $MovimientoInventarioPosicion = new MovimientoInventario($this->adapter);
        $this->MovimientoInventarioPosicion = $MovimientoInventarioPosicion->consultarMovimientoInventarioPorIdMovimientoInventario($this->idMovimientoInventario);
    }
    private function LlenarEntidad($result)
    {
        $this->idMovimientoInventario=$result['idMovimientoInventario'];
        $this->idPedidoCompra=$result['idPedidoCompra'];
        $this->idPedidoVenta=$result['idPedidoVenta'];
        $this->fecha=$result['fecha'];
        $this->idUsuarioCreacion=$result['idUsuarioCreacion'];
        
        $this->LlenarMovimientoInventarioPosicion();
    }
}
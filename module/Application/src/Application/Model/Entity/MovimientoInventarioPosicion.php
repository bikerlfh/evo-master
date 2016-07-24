<?php
namespace Application\Model\Entity;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Adapter\Adapter;

class MovimientoInventarioPosicion extends AbstractTableGateway
{
    private $idMovimientoInventarioPosicion;
    private $idMovimientoInventario;
    private $idProducto;
    private $idProveedor;
    private $entradaSalida;
    private $cantidad;
    private $valorMovimiento;
    
    public function __construct(Adapter $adapter = null)
    {
        $this->adapter = $adapter;
        $this->table =  new \Zend\Db\Sql\TableIdentifier('MovimientoInventarioPosicion', 'Inventario');
    }
    function getIdMovimientoInventarioPosicion() {
        return $this->idMovimientoInventarioPosicion;
    }

    function getIdMovimientoInventario() {
        return $this->idMovimientoInventario;
    }

    function getIdProducto() {
        return $this->idProducto;
    }

    function getIdProveedor() {
        return $this->idProveedor;
    }

    function getEntradaSalida() {
        return $this->entradaSalida;
    }

    function getCantidad() {
        return $this->cantidad;
    }

    function getValorMovimiento() {
        return $this->valorMovimiento;
    }

    function setIdMovimientoInventarioPosicion($idMovimientoInventarioPosicion) {
        $this->idMovimientoInventarioPosicion = $idMovimientoInventarioPosicion;
    }

    function setIdMovimientoInventario($idMovimientoInventario) {
        $this->idMovimientoInventario = $idMovimientoInventario;
    }

    function setIdProducto($idProducto) {
        $this->idProducto = $idProducto;
    }

    function setIdProveedor($idProveedor) {
        $this->idProveedor = $idProveedor;
    }

    function setEntradaSalida($entradaSalida) {
        $this->entradaSalida = $entradaSalida;
    }

    function setCantidad($cantidad) {
        $this->cantidad = $cantidad;
    }

    function setValorMovimiento($valorMovimiento) {
        $this->valorMovimiento = $valorMovimiento;
    }


    public function consultarMovimientoInventarioPosicionPoridMovimientoInventario($idMovimientoInventario)
    {
        $result=$this->select(array('idMovimientoInventario'=>$idMovimientoInventario))->current();
        if($result)
        {
            $this->LlenarEntidad($result);
            return true;
        }
        return false;
    }
    private function LlenarEntidad($result)
    {
        $this->idMovimientoInventarioPosicion=$result['idMovimientoInventarioPosicion'];
        $this->idMovimientoInventario=$result['idMovimientoInventario'];
        $this->idProducto=$result['idProducto'];
        $this->idProveedor=$result['idProveedor'];
        $this->entradaSalida=$result['entradaSalida'];
        $this->cantidad=$result['cantidad'];
        $this->valorMovimiento=$result['valorMovimiento'];
    }
}
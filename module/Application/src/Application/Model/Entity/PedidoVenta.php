<?php

namespace Application\Model\Entity;

use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Adapter\Adapter;
use Application\Model\Clases\StoredProcedure;

class PedidoVenta extends AbstractTableGateway {

    private $idPedidoVenta;
    private $numeroPedidoVenta;
    private $idCliente;
    private $idEstadoPedidoVenta;
    private $idViaPago;
    private $fechaPedido;
    private $urlDocumentoPago;
    private $idUsuarioCreacion;
    public $PedidoVentaPosicion;
    //Entidades embebidas
    public $ViaPago;
    public $EstadoPedidoVenta;

    public function __construct(Adapter $adapter = null) {
        $this->adapter = $adapter;
        $this->table = new \Zend\Db\Sql\TableIdentifier('PedidoVenta', 'Venta');
    }

    function getIdPedidoVenta() {
        return $this->idPedidoVenta;
    }

    function getIdCliente() {
        return $this->idCliente;
    }

    function getIdEstadoPedidoVenta() {
        return $this->idEstadoPedidoVenta;
    }

    function getIdViaPago() {
        return $this->idViaPago;
    }

    function getFechaPedido() {
        return $this->fechaPedido;
    }

    function getUrlDocumentoPago() {
        return $this->urlDocumentoPago;
    }

    function getIdUsuarioCreacion() {
        return $this->idUsuarioCreacion;
    }

    function setIdPedidoVenta($idPedidoVenta) {
        $this->idPedidoVenta = $idPedidoVenta;
    }

    function setIdCliente($idCliente) {
        $this->idCliente = $idCliente;
    }

    function setIdEstadoPedidoVenta($idEstadoPedidoVenta) {
        $this->idEstadoPedidoVenta = $idEstadoPedidoVenta;
    }

    function setIdViaPago($idViaPago) {
        $this->idViaPago = $idViaPago;
    }

    function setFechaPedido($fechaPedido) {
        $this->fechaPedido = $fechaPedido;
    }

    function setUrlDocumentoPago($urlDocumentoPago) {
        $this->urlDocumentoPago = $urlDocumentoPago;
    }

    function setIdUsuarioCreacion($idUsuarioCreacion) {
        $this->idUsuarioCreacion = $idUsuarioCreacion;
    }

    function getNumeroPedidoVenta() {
        return $this->numeroPedidoVenta;
    }

    function setNumeroPedidoVenta($numeroPedidoVenta) {
        $this->numeroPedidoVenta = $numeroPedidoVenta;
    }

    public function guardarPedidoVenta($idEstadoPedidoVenta, $idCliente, $urlDocumentoPago, $idUsuarioCreacion) {
        $stored = new StoredProcedure($this->adapter);
        // Venta.GuardarPedidoVenta @idEstadoPedidoVenta smallint,@idCliente bigint,@urlDocumentoPago varchar,@idUsuarioCreacion bigint
        $idPedidoVenta = $stored->execProcedureReturnDatos("Venta.GuardarPedidoVenta ?,?,?,?", array($idEstadoPedidoVenta, $idCliente, $urlDocumentoPago, $idUsuarioCreacion))->current();
        unset($stored);
        if ($idPedidoVenta['idPedidoVenta'] > 0) {
            $this->idPedidoVenta = $idPedidoVenta['idPedidoVenta'];
            foreach ($this->PedidoVentaPosicion as $posicion) {
                $posicion->setIdPedidoVenta($this->idPedidoVenta);
                $resultado = $posicion->guardarPedidoVentaPosicion();
                if ($resultado != 'true') {
                    $this->eliminarPedidoVenta($this->idPedidoVenta);
                    return $resultado;
                }
            }
            return 'true';
        }
        return 'false';
    }

    public function modificarPedidoVenta($idPedidoVenta, $idEstadoPedidoVenta, $idCliente, $urlDocumentoPago) {
        $datos = array(
            'urlDocumentoPago' => $urlDocumentoPago,
            'idCliente' => $idCliente,
            'idEstadoPedidoVenta' => $idEstadoPedidoVenta);
        $result = $this->update($datos, array('idPedidoVenta' => $idPedidoVenta));
        if ($result > 0)
            return true;
        return false;
    }

    public function modificarEstadoPedidoVenta($idPedidoVenta, $idEstadoPedidoVenta) {
        $datos = array('idEstadoPedidoVenta' => $idEstadoPedidoVenta);
        $result = $this->update($datos, array('idPedidoVenta' => $idPedidoVenta));
        if ($result > 0)
            return true;
        return false;
    }

    private function eliminarPedidoVenta($idPedidoVenta) {
        if ($this->delete(array('idPedidoVenta' => $idPedidoVenta)) > 0) {
            return true;
        }
        return false;
    }

    public function consultarTodoPedidoVenta() {
        return $this->select()->toArray();
    }

    public function consultarPedidoVentaPorIdPedidoVenta($idPedidoVenta) {
        $result = $this->select(array('idPedidoVenta' => $idPedidoVenta))->current();
        if ($result) {
            $this->LlenarEntidad($result);
            $this->LlenarPedidoVentaPosicion();
            return true;
        }
        return false;
    }

    public function consultarPedidoVentaPorIdEstadoPedidoVenta($idEstadoPedidoVenta) {
        return $this->select(array('idEstadoPedidoVenta' => $idEstadoPedidoVenta))->toArray();
    }

    public function consultaAvanzadaPedidoVenta($numeroPedidoVenta, $idCliente, $idEstadoPedidoVenta) {
        $numeroPedidoVenta = $numeroPedidoVenta > 0 ? $numeroPedidoVenta : null;
        $idCliente = $idCliente > 0 ? $idCliente : null;
        $idEstadoPedidoVenta = $idEstadoPedidoVenta > 0 ? $idEstadoPedidoVenta : null;
        $stored = new StoredProcedure($this->adapter);
        return $stored->execProcedureReturnDatos("Venta.ConsultaAvanzadaPedidoVenta ?,?,?", array($numeroPedidoVenta, $idCliente, (int) $idEstadoPedidoVenta));
    }

    private function LlenarPedidoVentaPosicion() {
        $PedidoVentaPosicion = new PedidoVentaPosicion($this->adapter);
        $this->PedidoVentaPosicion = $PedidoVentaPosicion->consultarPedidoVentaPosicionPorIdPedidoVenta($this->idPedidoVenta);
    }

    private function LlenarEntidad($result) {
        $this->idPedidoVenta = $result['idPedidoVenta'];
        $this->numeroPedidoVenta = $result['numeroPedido'];
        $this->idCliente = $result['idCliente'];
        $this->idEstadoPedidoVenta = $result['idEstadoPedidoVenta'];
        $this->idViaPago = $result['idViaPago'];
        $this->fechaPedido = $result['fechaPedido'];
        $this->urlDocumentoPago = $result['urlDocumentoPago'];
        $this->idUsuarioCreacion = $result['idUsuarioCreacion'];

        $this->CargarEmbebidos();
    }

    //<===============================================================>
    //<--- Carga los objetos completos relacionados a este objeto ====>
    //<===============================================================>
    private function CargarEmbebidos() {
        //Via Pago
        $this->ViaPago = new Entity\ViaPago(parent::getAdapter());
        $this->ViaPago->consutlarViapagoPoridViaPago($this->idViaPago);

        //Estado Pedido
        $this->EstadoPedidoVenta = new Entity\EstadoPedidoVenta(parent::getAdapter());
        $this->EstadoPedidoVenta->consultarEstadoPedidoVentaPorId($this->idEstadoPedidoVenta);
    }

}

<?php

namespace Application\Model\Entity;

use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\TableIdentifier;
use Zend\Db\Sql\Expression;
use Application\Model\Clases\StoredProcedure;
use Application\Model\Entity\ViaPago;
use Application\Model\Entity\EstadoPedidoVenta;

class PedidoVenta extends AbstractTableGateway {

    private $idPedidoVenta;
    private $numeroPedido;
    private $idCliente;
    private $idEstadoPedidoVenta;
    private $idViaPago;
    private $fechaPedido;
    private $urlDocumentoPago;
    private $idUsuarioCreacion;
    public $PedidoVentaPosicion;
    private $nombreCliente;
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
        return $this->numeroPedido;
    }

    function setNumeroPedidoVenta($numeroPedidoVenta) {
        $this->numeroPedido = $numeroPedidoVenta;
    }

    function getNombreCliente() {
        return $this->nombreCliente;
    }

    public function guardarPedidoVenta($idEstadoPedidoVenta, $idCliente, $urlDocumentoPago, $idUsuarioCreacion) {
        $stored = new StoredProcedure($this->adapter);
        // Venta.GuardarPedidoVenta @idEstadoPedidoVenta smallint,@idCliente bigint,@urlDocumentoPago varchar,@idUsuarioCreacion bigint
        $resultado = $stored->execProcedureReturnDatos("Venta.GuardarPedidoVenta ?,?,?,?", array($idEstadoPedidoVenta, $idCliente, $urlDocumentoPago, $idUsuarioCreacion))->current();
        unset($stored);
        if ($resultado['idPedidoVenta'] > 0) {
            $this->idPedidoVenta = $resultado['idPedidoVenta'];
            $this->numeroPedido = $resultado['numeroPedido'];
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

    public function autorizarPedidoVenta($idPedidoVenta, $urlDocumentoPago, $idUsuario) {
        $stored = new StoredProcedure($this->adapter);
        // Compra.AutorizarPedidoCompra	@idPedidoCompra bigint,@urlDocumentoPago varchar(150),	@idUsuario bigint
        $result = $stored->execProcedureReturnDatos("Venta.AutorizarPedidoVenta ?,?,?", array($idPedidoVenta, $urlDocumentoPago, $idUsuario))->current();
        unset($stored);
        return $result['result'];
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
        $sql = new Sql($this->adapter);
        $select = $sql->select()->
                from(array('pedido' => $this->table))->
                join(array('cliente' => new TableIdentifier('Cliente', 'Tercero')), 'pedido.idCliente = cliente.idCliente')->
                join(array('dbt' => new TableIdentifier('DatoBasicoTercero', 'Tercero')), 'cliente.idDatoBasicoTercero = dbt.idDatoBasicoTercero', array('nombreCliente' => new Expression("CONVERT(VARCHAR,dbt.nit )+' - '+ dbt.descripcion")))->
                where(array('pedido.idPedidoVenta' => $idPedidoVenta));
        $result = $sql->prepareStatementForSqlObject($select)->execute();
        $resultsSet = new ResultSet();
        $result = $resultsSet->initialize($result)->current();
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
        $where = array();
        if ($numeroPedidoVenta > 0 ) {
            array_push($where,'numeroPedido='.$numeroPedidoVenta);
        }
        if ($idCliente > 0 ) {
            array_push($where,'idCliente='.$idCliente);
        }
        if ($idEstadoPedidoVenta > 0 ) {
            array_push($where,'idEstadoPedidoVenta='.$idEstadoPedidoVenta);
        }
        $sql = new Sql($this->adapter);
        $select = $sql->select(new TableIdentifier("vConsultaAvanzadaPedidoVenta", "venta"))
                               ->where($where);
        
        $result = $sql->prepareStatementForSqlObject($select)->execute();
        $resultsSet = new ResultSet();
        return $resultsSet->initialize($result)->toArray();
    }

    private function LlenarPedidoVentaPosicion() {
        $PedidoVentaPosicion = new PedidoVentaPosicion($this->adapter);
        $this->PedidoVentaPosicion = $PedidoVentaPosicion->consultarPedidoVentaPosicionPorIdPedidoVenta($this->idPedidoVenta);
    }

    private function LlenarEntidad($result) {
        $this->idPedidoVenta = $result['idPedidoVenta'];
        $this->numeroPedido = $result['numeroPedido'];
        $this->idCliente = $result['idCliente'];
        $this->idEstadoPedidoVenta = $result['idEstadoPedidoVenta'];
        $this->idViaPago = $result['idViaPago'];
        $this->fechaPedido = $result['fechaPedido'];
        $this->urlDocumentoPago = $result['urlDocumentoPago'];
        $this->idUsuarioCreacion = $result['idUsuarioCreacion'];
        $this->nombreCliente = $result['nombreCliente'];
        $this->CargarEmbebidos();
    }

    //<===============================================================>
    //<--- Carga los objetos completos relacionados a este objeto ====>
    //<===============================================================>
    private function CargarEmbebidos() {

        if ($this->idViaPago != null) {
            //Via Pago
            $this->ViaPago = new ViaPago(parent::getAdapter());
            $this->ViaPago->consutlarViapagoPoridViaPago($this->idViaPago);
        }

        //Estado Pedido
        $this->EstadoPedidoVenta = new EstadoPedidoVenta(parent::getAdapter());
        $this->EstadoPedidoVenta->consultarEstadoPedidoVentaPorIdEstadoPedidoVenta($this->idEstadoPedidoVenta);
    }

}

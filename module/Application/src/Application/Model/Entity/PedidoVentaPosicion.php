<?php
namespace Application\Model\Entity;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Adapter\Adapter;
use Application\Model\Clases\StoredProcedure;

class PedidoVentaPosicion extends AbstractTableGateway
{
    private $idPedidoVentaPosicion;
    private $idPedidoVenta;
    private $idProducto;
    private $cantidad;
    private $valorVenta;
    private $idUsuarioCreacion;   
    public function __construct(Adapter $adapter = null)
    {
        $this->adapter = $adapter;
        $this->table =  new \Zend\Db\Sql\TableIdentifier('PedidoVentaPosicion', 'Venta');
    }
    function getIdPedidoVentaPosicion() {
        return $this->idPedidoVentaPosicion;
    }

    function getIdPedidoVenta() {
        return $this->idPedidoVenta;
    }

    function getIdProducto() {
        return $this->idProducto;
    }

    function getCantidad() {
        return $this->cantidad;
    }

    function getValorVenta() {
        return $this->valorVenta;
    }

    function getIdUsuarioCreacion() {
        return $this->idUsuarioCreacion;
    }

    function setIdPedidoVentaPosicion($idPedidoVentaPosicion) {
        $this->idPedidoVentaPosicion = $idPedidoVentaPosicion;
    }

    function setIdPedidoVenta($idPedidoVenta) {
        $this->idPedidoVenta = $idPedidoVenta;
    }

    function setIdProducto($idProducto) {
        $this->idProducto = $idProducto;
    }

    function setCantidad($cantidad) {
        $this->cantidad = $cantidad;
    }

    function setValorVenta($valorVenta) {
        $this->valorVenta = $valorVenta;
    }

    function setIdUsuarioCreacion($idUsuarioCreacion) {
        $this->idUsuarioCreacion = $idUsuarioCreacion;
    }

    public function guardarPedidoVentaPosicion()
    {
        $stored = new StoredProcedure($this->adapter);
        // Venta.GuardarPedidoVentaPosicion @idPedidoVenta bigint,@idProducto bigint,@cantidad bigint,@valorVenta decimal(10,2),@idUsuarioCreacion bigint
        $result = $stored->execProcedureReturnDatos("Venta.GuardarPedidoVentaPosicion ?,?,?,?,?",array($this->$idPedidoVenta,$this->idProducto,$this->cantidad,$this->valorVenta,$this->idUsuarioCreacion))->current();
        return $result['result'];
    }

    public function modificarPedidoVentaPosicion($idPedidoVentaPosicion,$idPedidoVenta,$idProducto,$cantidad,$valorVenta)
    {
        $datos=array(
                'valorVenta'=> $valorVenta,
                'cantidad'=> $cantidad,
                'idProducto'=> $idProducto,
                'idPedidoVenta'=> $idPedidoVenta
        );
        $result=$this->update($datos,array('idPedidoVentaPosicion'=>$idPedidoVentaPosicion));
        if($result>0)
            return true;
        return false;
    }

    public function consultarPedidoVentaPosicion()
    {
        return $this->select()->toArray();
    }
    public function consultarPedidoVentaPosicionPorIdPedidoVentaPosicion($idPedidoVentaPosicion)
    {
        $result=$this->select(array('idPedidoVentaPosicion'=>$idPedidoVentaPosicion))->current();
        if($result)
        {
            $this->LlenarEntidad($result);
            return true;
        }
        return false;
    }
    public function consultarPedidoVentaPosicionPorIdPedidoVenta($idPedidoVenta)
    {
        $result = $this->select(array('idPedidoVenta'=>$idPedidoVenta))->toArray();
        $objects = null;
      
        foreach ($result as $value) {
            $this->consultarPedidoVentaPosicionPorIdPedidoVentaPosicion($value['idPedidoVentaPosicion']);
            $tmp = $this;
            $objects[]=$tmp;
        }
        return $objects; 
    }
    private function LlenarEntidad($result)
    {
        $this->idPedidoVentaPosicion=$result['idPedidoVentaPosicion'];
        $this->idPedidoVenta=$result['idPedidoVenta'];
        $this->cantidad=$result['cantidad'];
        $this->idProducto=$result['idProducto'];
        $this->valorVenta=$result['valorVenta'];
        $this->idUsuarioCreacion=$result['idUsuarioCreacion'];
    }
}
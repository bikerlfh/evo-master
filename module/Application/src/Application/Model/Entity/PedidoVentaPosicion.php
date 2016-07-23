<?php
namespace Application\Model\Entity;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\TableIdentifier;
use Zend\Db\Sql\Expression;
use Application\Model\Clases\StoredProcedure;

class PedidoVentaPosicion extends AbstractTableGateway
{
    private $idPedidoVentaPosicion;
    private $idPedidoVenta;
    private $idSaldoInventario;
    private $idProducto;
    private $cantidad;
    private $valorVenta;
    private $idUsuarioCreacion;   
    
    private $nombreProducto;
    private $nombreProveedor;
    
    public function __construct(Adapter $adapter = null)
    {
        $this->adapter = $adapter;
        $this->table =  new \Zend\Db\Sql\TableIdentifier('PedidoVentaPosicion', 'Venta');
    }
    
    function getIdSaldoInventario() {
        return $this->idSaldoInventario;
    }

    function setIdSaldoInventario($idSaldoInventario) {
        $this->idSaldoInventario = $idSaldoInventario;
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

    function getNombreProducto() {
        return $this->nombreProducto;
    }
    function getNombreProveedor(){
        return $this->nombreProveedor;
    }
        public function guardarPedidoVentaPosicion()
    {
        $stored = new StoredProcedure($this->adapter);
        // Venta.GuardarPedidoVentaPosicion @idPedidoVenta bigint, @idSaldoInventario bigint ,@idProducto bigint,@cantidad bigint,@valorVenta decimal(10,2),@idUsuarioCreacion bigint
        $result = $stored->execProcedureReturnDatos("Venta.GuardarPedidoVentaPosicion ?,?,?,?,?,?",array($this->idPedidoVenta, $this->idSaldoInventario,$this->idProducto,$this->cantidad,$this->valorVenta,$this->idUsuarioCreacion))->current();
        return $result['result'];
    }

    public function modificarPedidoVentaPosicion($idPedidoVentaPosicion,$idPedidoVenta,$idSaldoInventario, $idProducto,$cantidad,$valorVenta)
    {
        $datos=array(
                'valorVenta'=> $valorVenta,
                'cantidad'=> $cantidad,
                'idProducto'=> $idProducto,
                'idPedidoVenta'=> $idPedidoVenta,
                'idSaldoInventario'=> $idSaldoInventario
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
        $sql = new Sql($this->adapter);
        $select = $sql->select()->
                            from(array('pedido'=>$this->table))->
                            columns(array("*"))->
                            //columns(array("idPedidoVentaPosicion" => "pedido.idPedidoVentaPosicion",
                                         // "pedido.idPedidoVenta","pedido.idSaldoInventario","p.nombreProducto","nombreProveedor","pedido.cantidad","pedido.idProducto","pedido.valorVenta","pedido.idUsuarioCreacion"))->
                            join(array('p'=> new TableIdentifier("Producto", "Producto")),
                                       'pedido.idProducto = p.idProducto', 
                                       array('nombreProducto' => new Expression("p.codigo +' - '+ p.nombre")))->
                            join(array('saldoInventario'=> new TableIdentifier("SaldoInventario","Inventario")),
                                       'pedido.idSaldoInventario = saldoInventario.idSaldoInventario',
                                        array())->
                            join(array('proveedor' => new TableIdentifier("Proveedor","tercero")),
                                       'saldoInventario.idProveedor = proveedor.idProveedor',
                                        array())->
                            join(array('dbt'=>new TableIdentifier("DatoBasicoTercero","Tercero")),
                                       'proveedor.idDatoBasicoTercero = dbt.idDatoBasicoTercero',
                                       array('nombreProveedor' => new Expression("CONVERT(VARCHAR,dbt.nit) + ' - ' + dbt.descripcion")))->
                            where(array("idPedidoVentaPosicion"=>$idPedidoVentaPosicion));

        $results = $sql->prepareStatementForSqlObject($select)->execute();
        $resultsSet = new ResultSet();
        $result =  $resultsSet->initialize($results)->current();   
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
        $this->idSaldoInventario = $result['idSaldoInventario'];
        $this->nombreProducto = $result['nombreProducto'];
        $this->nombreProveedor = $result['nombreProveedor'];
        $this->cantidad=$result['cantidad'];
        $this->idProducto=$result['idProducto'];
        $this->valorVenta=$result['valorVenta'];
        $this->idUsuarioCreacion=$result['idUsuarioCreacion'];
        
    }
}
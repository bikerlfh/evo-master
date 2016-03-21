<?php
namespace Application\Model\Entity;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Adapter\Adapter; 
use Application\Model\Clases\StoredProcedure;
use Zend\Db\Sql\Sql;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\TableIdentifier;
use Zend\Db\Sql\Expression;

class PedidoCompra extends AbstractTableGateway
{
    private $idPedidoCompra;
    private $numeroPedido;
    private $idEstadoPedido;
    private $idProveedor;
    private $fechaPedido;
    private $urlDocumentoPago;
    private $idUsuarioCreacion;
    
    private $nombreProveedor;
    public $PedidoCompraPosicion;
    
    public function __construct(Adapter $adapter = null)
    {
        $this->adapter = $adapter;
        $this->table =  new \Zend\Db\Sql\TableIdentifier('PedidoCompra', 'Compra');
        $this->PedidoCompraPosicion = array();
    }
    
    public function getIdPedidoCompra(){ return $this->idPedidoCompra; }
    public function setIdPedidoCompra($idPedidoCompra){ $this->idPedidoCompra=$idPedidoCompra; }
    public function getNumeroPedido() { return $this->numeroPedido; }
    public function setNumeroPedido($numeroPedido) { $this->numeroPedido = $numeroPedido; }
    public function getIdEstadoPedido(){ return $this->idEstadoPedido; }
    public function setIdEstadoPedido($idEstadoPedido){ $this->idEstadoPedido=$idEstadoPedido; }
    public function getIdProveedor(){ return $this->idProveedor; }
    public function setIdProveedor($idProveedor){ $this->idProveedor=$idProveedor; }
    public function getFechaPedido(){ return $this->fechaPedido; }
    public function setFechaPedido($fechaPedido){ $this->fechaPedido=$fechaPedido; }
    public function getUrlDocumentoPago() { return $this->urlDocumentoPago; }
    public function setUrlDocumentoPago($urlDocumentoPago) { $this->urlDocumentoPago = $urlDocumentoPago; }
    public function getIdUsuarioCreacion(){ return $this->idUsuarioCreacion; }
    public function setIdUsuarioCreacion($idUsuarioCreacion){ $this->idUsuarioCreacion=$idUsuarioCreacion; }

    public function getNombreProveedor() { return $this->nombreProveedor; }
    
    public function guardarPedidoCompra($idEstadoPedido,$idProveedor,$urlDocumentoPago,$idUsuarioCreacion)
    {
        $stored = new StoredProcedure($this->adapter);
        // Compra.GuardarPedidoCompra @idEstadoPedido smallint,	@idProveedor bigint,@urlDocumentoPago varchar,@idUsuarioCreacion bigint
        $idPedidoCompra = $stored->execProcedureReturnDatos("Compra.GuardarPedidoCompra ?,?,?,?",array($idEstadoPedido,$idProveedor,$urlDocumentoPago,$idUsuarioCreacion))->current();
        unset($stored);
        if ($idPedidoCompra['idPedidoCompra'] > 0) 
        {
            $this->idPedidoCompra = $idPedidoCompra['idPedidoCompra'];
            foreach ($this->PedidoCompraPosicion as $posicion)
            {
                $posicion->setIdPedidoCompra($this->idPedidoCompra);
                $resultado = $posicion->guardarPedidoCompraPosicion();
                if ($resultado != 'true')
                {
                    $this->eliminarPedidoCompra($idPedidoCompra);
                    return $resultado;
                }
            }
            return 'true';
        }
        return 'false';
    }
    public function autorizarPedidoCompra($idPedidoCompra,$urlDocumentoPago,$idUsuario)
    {
        $stored = new StoredProcedure($this->adapter);
        // Compra.AutorizarPedidoCompra	@idPedidoCompra bigint,@urlDocumentoPago varchar(150),	@idUsuario bigint
        $result =  $stored->execProcedureReturnDatos("Compra.AutorizarPedidoCompra ?,?,?",array($idPedidoCompra,$urlDocumentoPago,$idUsuario))->current();
        unset($stored);
        return $result['result'];
    }
    public function modificarPedidoCompra($idPedidoCompra,$idEstadoPedido,$idProveedor,$urlDocumentoPago)
    {
        $datos=array(
                'urlDocumentoPago' => $urlDocumentoPago,
                'idProveedor'=> $idProveedor,
                'idEstadoPedido'=> $idEstadoPedido);
        $result=$this->update($datos,array('idPedidoCompra'=>$idPedidoCompra));
        if($result>0)
            return true;
        return false;
    }
    public function modificarEstadoPedidoCompra($idPedidoCompra,$idEstadoPedido)
    {
        $datos=array('idEstadoPedido'=> $idEstadoPedido);
        $result=$this->update($datos,array('idPedidoCompra'=>$idPedidoCompra));
        if($result>0)
            return true;
        return false;
    }
    private function eliminarPedidoCompra($idPedidoCompra)
    {
        if ($this->delete(array('idPedidoCompra'=>$idPedidoCompra))>0) {
            return true;
        }
        return false;
    }
    public function consultarTodoPedidoCompra()
    {
        return $this->select()->toArray();
    }
    public function consultarPedidoCompraPorIdPedidoCompra($idPedidoCompra)
    {
        $sql = new Sql($this->adapter);
        $select = $sql->select()->
                        from(array('pedido'=>$this->table))->
                        join(array('prov'=> new TableIdentifier('Proveedor','Tercero')),
                                   'pedido.idProveedor = prov.idProveedor')->
                        join(array('dbt'=>new TableIdentifier('DatoBasicoTercero','Tercero')),
                                   'prov.idDatoBasicoTercero = dbt.idDatoBasicoTercero',
                                   array('nombreProveedor' => new Expression("CONVERT(VARCHAR,dbt.nit )+' - '+ dbt.descripcion")));
        $results = $sql->prepareStatementForSqlObject($select)->execute();
        $resultsSet = new ResultSet();
        $result =  $resultsSet->initialize($results)->current();   
        if($result)
        {
            $this->LlenarEntidad($result);
            $this->LlenarPedidoCompraPosicion();
            return true;
        }
        return false;
    }
    public function consultarPedidoCompraPorIdEstadoPedido($idEstadoPedido)
    {
        return $this->select(array('idestadopedido'=>$idEstadoPedido))->toArray();
    }
   
    public function consultaAvanzadaPedidoCompra($numeroPedido,$idProveedor,$idEstadoPedido)
    {
        $numeroPedido = $numeroPedido > 0? $numeroPedido:null;
        $idProveedor = $idProveedor > 0? $idProveedor:null;
        $idEstadoPedido = $idEstadoPedido > 0? $idEstadoPedido:null;
        $stored = new StoredProcedure($this->adapter);
        return $stored->execProcedureReturnDatos("Compra.ConsultaAvanzadaPedidoCompra ?,?,?",array($numeroPedido, $idProveedor,(int)$idEstadoPedido));
    }
    private function LlenarPedidoCompraPosicion()
    {
        $PedidoCompraPosicion = new PedidoCompraPosicion($this->adapter);
        $this->PedidoCompraPosicion = $PedidoCompraPosicion->consultarPedidoCompraPosicionPorIdPedidoCompra($this->idPedidoCompra);
        
    }
    private function LlenarEntidad($result)
    {
        $this->idPedidoCompra=$result['idPedidoCompra'];
        $this->idEstadoPedido=$result['idEstadoPedido'];
        $this->numeroPedido = $result['numeroPedido'];
        $this->idProveedor=$result['idProveedor'];
        $this->fechaPedido=$result['fechaPedido'];
        $this->idUsuarioCreacion=$result['idUsuarioCreacion'];
        $this->nombreProveedor = $result['nombreProveedor'];
    }
}
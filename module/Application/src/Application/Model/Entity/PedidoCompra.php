<?php
namespace Application\Model\Entity;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Adapter\Adapter; 
use Application\Model\Clases\StoredProcedure;

class PedidoCompra extends AbstractTableGateway
{
    private $idPedidoCompra;
    private $idEstadoPedido;
    private $idProveedor;
    private $fechaPedido;
    private $urlDocumentoPago;
    private $idUsuarioCreacion;
    
    public $PedidoCompraPosicion;
    
    public function __construct(Adapter $adapter = null)
    {
        $this->adapter = $adapter;
        $this->table =  new \Zend\Db\Sql\TableIdentifier('PedidoCompra', 'Compra');
        $this->PedidoCompraPosicion = array();
    }

    public function getIdUsuarioCreacion(){
        return $this->idUsuarioCreacion;
    }
    public function setIdUsuarioCreacion($idUsuarioCreacion){
        $this->idUsuarioCreacion=$idUsuarioCreacion;
    }
    function getUrlDocumentoPago() {
        return $this->urlDocumentoPago;
    }
    
    function setUrlDocumentoPago($urlDocumentoPago) {
        $this->urlDocumentoPago = $urlDocumentoPago;
    }
    
    public function getFechaPedido(){
        return $this->fechaPedido;
    }
    public function setFechaPedido($fechaPedido){
        $this->fechaPedido=$fechaPedido;
    }
    public function getIdProveedor(){
        return $this->idProveedor;
    }
    public function setIdProveedor($idProveedor){
        $this->idProveedor=$idProveedor;
    }
    public function getIdEstadoPedido(){
        return $this->idEstadoPedido;
    }
    public function setIdEstadoPedido($idEstadoPedido){
        $this->idEstadoPedido=$idEstadoPedido;
    }
    public function getIdPedidoCompra(){
        return $this->idPedidoCompra;
    }
    public function setIdPedidoCompra($idPedidoCompra){
        $this->idPedidoCompra=$idPedidoCompra;
    }

    public function guardarPedidoCompra($idEstadoPedido,$idProveedor,$urlDocumentoPago,$idUsuarioCreacion)
    {
        $stored = new StoredProcedure($this->adapter);
        // Compra.GuardarPedidoCompra @idEstadoPedido smallint,	@idProveedor bigint,@urlDocumentoPago varchar,@idUsuarioCreacion bigint
        $idPedidoCompra = $stored->execProcedureReturnDatos("Compra.GuardarPedidoCompra ?,?,?,?",array($idEstadoPedido,$idProveedor,$urlDocumentoPago,$idUsuarioCreacion))->current();
        if ($idPedidoCompra['idPedidoCompra'] > 0) 
        {
            foreach ($this->PedidoCompraPosicion as $posicion) 
            {
                $posicion->setIdPedidoCompra($idPedidoCompra['idPedidoCompra']);
                if (!$posicion->guardarPedidoCompraPosicion()) 
                {
                    return false;
                }
            }
            return true;
        }
        return false;
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
    public  function eliminarPedidoCompra($idPedidoCompra)
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
        $result=$this->select(array('idpedidocompra'=>$idPedidoCompra))->current();
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
        return $stored->execProcedureReturnDatos("Compra.ConsultaAvanzadaPedidoCompra ?,?,?",array($numeroPedido, $idProveedor,$idEstadoPedido));
    }
    private function LlenarPedidoCompraPosicion()
    {
        $PedidoCompraPosicion = new PedidoCompraPosicion($this->adapter);
        $this->PedidoCompraPosicion = $PedidoCompraPosicion->consultarPedidoCompraPosicionPorIdPedidoCompra($this->idPedidoCompra);
        
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
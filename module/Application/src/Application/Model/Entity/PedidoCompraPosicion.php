<?php
namespace Application\Model\Entity;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Adapter\Adapter;

class PedidoCompraPosicion extends AbstractTableGateway
{
    private $idPedidoCompraPosicion;
    private $idPedidoCompra;
    private $idProducto;
    private $cantidad;
    private $idUsuarioCreacion;   
    public function __construct(Adapter $adapter = null)
    {
        $this->adapter = $adapter;
        $this->table =  new \Zend\Db\Sql\TableIdentifier('PedidoCompraPosicion', 'Compra');
    }

    public function getIdUsuarioCreacion(){
        return $this->idUsuarioCreacion;
    }
    public function setIdUsuarioCreacion($idUsuarioCreacion){
        $this->idUsuarioCreacion=$idUsuarioCreacion;
    }
    public function getCantidad(){
        return $this->cantidad;
    }
    public function setCantidad($cantidad){
        $this->cantidad=$cantidad;
    }
    public function getIdProducto(){
        return $this->idProducto;
    }
    public function setIdProducto($idProducto){
        $this->idProducto=$idProducto;
    }
    public function getIdPedidoCompra(){
        return $this->idPedidoCompra;
    }
    public function setIdPedidoCompra($idPedidoCompra){
        $this->idPedidoCompra=$idPedidoCompra;
    }
    public function getIdPedidoCompraPosicion(){
        return $this->idPedidoCompraPosicion;
    }
    public function setIdPedidoCompraPosicion($idPedidoCompraPosicion){
        $this->idPedidoCompraPosicion=$idPedidoCompraPosicion;
    }

    public function guardarPedidoCompraPosicion()
    {
        $datos=array(
                'idUsuarioCreacion'=> $this->idUsuarioCreacion,
                'cantidad'=> $this->cantidad,
                'idProducto'=> $this->idProducto,
                'idPedidoCompra'=> $this->idPedidoCompra
        );
        $result=$this->insert($datos);
        if($result>0)
            return true;
        return false;
    }

    public function modificarPedidoCompraPosicion($idPedidoCompraPosicion,$idPedidoCompra,$idProducto,$cantidad)
    {
        $datos=array(
                'cantidad'=> $cantidad,
                'idProducto'=> $idProducto,
                'idPedidoCompra'=> $idPedidoCompra
        );
        $result=$this->update($datos,array('idPedidoCompraPosicion'=>$idPedidoCompraPosicion));
        if($result>0)
            return true;
        return false;
    }

    public function consultarPedidoCompraPosicion()
    {
        return $this->select()->toArray();
    }
    public function consultarPedidoCompraPosicionPorIdPedidoCompraPosicion($idPedidoCompraPosicion)
    {
        $result=$this->select(array('idPedidoCompraPosicion'=>$idPedidoCompraPosicion))->current();
        if($result)
        {
            $this->LlenarEntidad($result);
            return true;
        }
        return false;
    }
    public function consultarPedidoCompraPosicionPorIdPedidoCompra($idPedidoCompra)
    {
        $result = $this->select(array('idPedidoCompra'=>$idPedidoCompra))->toArray();
        $objects = null;
      
        foreach ($result as $value) {
            $this->consultarPedidoCompraPosicionPorIdPedidoCompraPosicion($value['idPedidoCompraPosicion']);
            $tmp = $this;
            $objects[]=$tmp;
        }
        return $objects; 
    }
    private function LlenarEntidad($result)
    {
        $this->idUsuarioCreacion=$result['idUsuarioCreacion'];
        $this->cantidad=$result['cantidad'];
        $this->idProducto=$result['idProducto'];
        $this->idPedidoCompra=$result['idPedidoCompra'];
        $this->idPedidoCompraPosicion=$result['idPedidoCompraPosicion'];
    }
}
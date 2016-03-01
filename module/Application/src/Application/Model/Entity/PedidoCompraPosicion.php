<?php
namespace Application\Model\Entity;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Adapter\Adapter;;
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

    public function getidUsuarioCreacion(){
        return $this->idUsuarioCreacion;
    }
    public function setidUsuarioCreacion($idUsuarioCreacion){
        $this->idUsuarioCreacion=$idUsuarioCreacion;
    }
    public function getcantidad(){
        return $this->cantidad;
    }
    public function setcantidad($cantidad){
        $this->cantidad=$cantidad;
    }
    public function getidProducto(){
        return $this->idProducto;
    }
    public function setidProducto($idProducto){
        $this->idProducto=$idProducto;
    }
    public function getidPedidoCompra(){
        return $this->idPedidoCompra;
    }
    public function setidPedidoCompra($idPedidoCompra){
        $this->idPedidoCompra=$idPedidoCompra;
    }
    public function getidPedidoCompraPosicion(){
        return $this->idPedidoCompraPosicion;
    }
    public function setidPedidoCompraPosicion($idPedidoCompraPosicion){
        $this->idPedidoCompraPosicion=$idPedidoCompraPosicion;
    }

    public function guardarPedidocompraposicion($idPedidoCompra,$idProducto,$cantidad,$idUsuarioCreacion)
    {
        $datos=array(
                'idUsuarioCreacion'=> $idUsuarioCreacion,
                'cantidad'=> $cantidad,
                'idProducto'=> $idProducto,
                'idPedidoCompra'=> $idPedidoCompra
        );
        $result=$this->insert($datos);
        if($result>0)
            return true;
        return false;
    }

    public function modificarPedidocompraposicion($idPedidoCompraPosicion,$idPedidoCompra,$idProducto,$cantidad,$idUsuarioCreacion)
    {
        $datos=array(
                'idUsuarioCreacion'=> $idUsuarioCreacion,
                'cantidad'=> $cantidad,
                'idProducto'=> $idProducto,
                'idPedidoCompra'=> $idPedidoCompra
        );
        $result=$this->update($datos,array('idPedidoCompraPosicion'=>$idPedidoCompraPosicion));
        if($result>0)
            return true;
        return false;
    }

    public function consultarPedidocompraposicion()
    {
        return $this->select()->toArray();
    }
    public function consultarPedidocompraposicionPoridPedidoCompraPosicion($idPedidoCompraPosicion)
    {
        $result=$this->select(array('idpedidocompraposicion'=>$idPedidoCompraPosicion))->current();
        if($result)
        {
            $this->LlenarEntidad($result);
            return true;
        }
        return false;
    }
    public function consultarPedidocompraposicionPoridPedidoCompra($idPedidoCompra)
    {
        $result=$this->select(array('idpedidocompra'=>$idPedidoCompra))->current();
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
        $this->cantidad=$result['cantidad'];
        $this->idProducto=$result['idProducto'];
        $this->idPedidoCompra=$result['idPedidoCompra'];
        $this->idPedidoCompraPosicion=$result['idPedidoCompraPosicion'];
    }
}
<?php
namespace Application\Model\Entity;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Adapter\Adapter;;
class SaldoInventario extends AbstractTableGateway
{
    private $idSaldoInventario;
    private $idProducto;
    private $idProveedor;
    private $cantidad;
    private $valorCompra;
    private $valorVenta;
    private $idUsuarioCreacion;
    private $idUsuarioModificacion;
    private $fechaCreacion;
    private $fechaModificacion;
    
    public function __construct(Adapter $adapter = null)
    {
        $this->adapter = $adapter;
        $this->table =  new \Zend\Db\Sql\TableIdentifier('SaldoInventario', 'Inventario');
    }

    public function getfechaModificacion(){
        return $this->fechaModificacion;
    }
    public function setfechaModificacion($fechaModificacion){
        $this->fechaModificacion=$fechaModificacion;
    }
    public function getfechaCreacion(){
        return $this->fechaCreacion;
    }
    public function setfechaCreacion($fechaCreacion){
        $this->fechaCreacion=$fechaCreacion;
    }
    public function getidUsuarioModificacion(){
        return $this->idUsuarioModificacion;
    }
    public function setidUsuarioModificacion($idUsuarioModificacion){
        $this->idUsuarioModificacion=$idUsuarioModificacion;
    }
    public function getidUsuarioCreacion(){
        return $this->idUsuarioCreacion;
    }
    public function setidUsuarioCreacion($idUsuarioCreacion){
        $this->idUsuarioCreacion=$idUsuarioCreacion;
    }
    public function getvalorVenta(){
        return $this->valorVenta;
    }
    public function setvalorVenta($valorVenta){
        $this->valorVenta=$valorVenta;
    }
    public function getvalorCompra(){
        return $this->valorCompra;
    }
    public function setvalorCompra($valorCompra){
        $this->valorCompra=$valorCompra;
    }
    public function getcantidad(){
        return $this->cantidad;
    }
    public function setcantidad($cantidad){
        $this->cantidad=$cantidad;
    }
    public function getidProveedor(){
        return $this->idProveedor;
    }
    public function setidProveedor($idProveedor){
        $this->idProveedor=$idProveedor;
    }
    public function getidProducto(){
        return $this->idProducto;
    }
    public function setidProducto($idProducto){
        $this->idProducto=$idProducto;
    }
    public function getidSaldoInventario(){
        return $this->idSaldoInventario;
    }
    public function setidSaldoInventario($idSaldoInventario){
        $this->idSaldoInventario=$idSaldoInventario;
    }

    public function guardarSaldoinventario($idProducto,$idProveedor,$cantidad,$valorCompra,$valorVenta,$idUsuarioCreacion,$idUsuarioModificacion,$fechaCreacion,$fechaModificacion)
    {
        $datos=array(
                'fechaModificacion'=> $fechaModificacion,
                'fechaCreacion'=> $fechaCreacion,
                'idUsuarioModificacion'=> $idUsuarioModificacion,
                'idUsuarioCreacion'=> $idUsuarioCreacion,
                'valorVenta'=> $valorVenta,
                'valorCompra'=> $valorCompra,
                'cantidad'=> $cantidad,
                'idProveedor'=> $idProveedor,
                'idProducto'=> $idProducto
        );
        $result=$this->insert($datos);
        if($result>0)
            return true;
        return false;
    }

    public function modificarSaldoinventario($idSaldoInventario,$idProducto,$idProveedor,$cantidad,$valorCompra,$valorVenta,$idUsuarioCreacion,$idUsuarioModificacion,$fechaCreacion,$fechaModificacion)
    {
        $datos=array(
                'fechaModificacion'=> $fechaModificacion,
                'fechaCreacion'=> $fechaCreacion,
                'idUsuarioModificacion'=> $idUsuarioModificacion,
                'idUsuarioCreacion'=> $idUsuarioCreacion,
                'valorVenta'=> $valorVenta,
                'valorCompra'=> $valorCompra,
                'cantidad'=> $cantidad,
                'idProveedor'=> $idProveedor,
                'idProducto'=> $idProducto
        );
        $result=$this->update($datos,array('idSaldoInventario'=>$idSaldoInventario));
        if($result>0)
            return true;
        return false;
    }

    public function consultarTodotSaldoinventario()
    {
        return $this->select()->toArray();
    }
    public function consultarSaldoinventarioPoridSaldoInventario($idSaldoInventario)
    {
        $result=$this->select(array('idsaldoinventario'=>$idSaldoInventario))->current();
        if($result)
        {
            $this->LlenarEntidad($result);
            return true;
        }
        return false;
    }
    public function consultarSaldoinventarioPoridProducto($idProducto)
    {
        $result=$this->select(array('idproducto'=>$idProducto))->current();
        if($result)
        {
            $this->LlenarEntidad($result);
            return true;
        }
        return false;
    }
    public function consultarSaldoinventarioPoridProveedor($idProveedor)
    {
        $result=$this->select(array('idproveedor'=>$idProveedor))->current();
        if($result)
        {
            $this->LlenarEntidad($result);
            return true;
        }
        return false;
    }
    private function LlenarEntidad($result)
    {
        $this->fechaModificacion=$result['fechaModificacion'];
        $this->fechaCreacion=$result['fechaCreacion'];
        $this->idUsuarioModificacion=$result['idUsuarioModificacion'];
        $this->idUsuarioCreacion=$result['idUsuarioCreacion'];
        $this->valorVenta=$result['valorVenta'];
        $this->valorCompra=$result['valorCompra'];
        $this->cantidad=$result['cantidad'];
        $this->idProveedor=$result['idProveedor'];
        $this->idProducto=$result['idProducto'];
        $this->idSaldoInventario=$result['idSaldoInventario'];
    }
}
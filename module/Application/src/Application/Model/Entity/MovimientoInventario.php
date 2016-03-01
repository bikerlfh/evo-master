<?php
namespace Application\Model\Entity;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Adapter\Adapter;

class MovimientoInventario extends AbstractTableGateway
{
    private $idMovimientoInventario;
    private $idSaldoInventario;
    private $entradaSalida;
    private $cantidad;
    private $valorMovimiento;
    private $fecha;
    private $idUsuarioCreacion;
    public function __construct(Adapter $adapter = null)
    {
        $this->adapter = $adapter;
        $this->table =  new \Zend\Db\Sql\TableIdentifier('MovimientoInventario', 'Inventario');
    }

    public function getidUsuarioCreacion(){
        return $this->idUsuarioCreacion;
    }
    public function setidUsuarioCreacion($idUsuarioCreacion){
        $this->idUsuarioCreacion=$idUsuarioCreacion;
    }
    public function getfecha(){
        return $this->fecha;
    }
    public function setfecha($fecha){
        $this->fecha=$fecha;
    }
    public function getvalorMovimiento(){
        return $this->valorMovimiento;
    }
    public function setvalorMovimiento($valorMovimiento){
        $this->valorMovimiento=$valorMovimiento;
    }
    public function getcantidad(){
        return $this->cantidad;
    }
    public function setcantidad($cantidad){
        $this->cantidad=$cantidad;
    }
    public function getentradaSalida(){
        return $this->entradaSalida;
    }
    public function setentradaSalida($entradaSalida){
        $this->entradaSalida=$entradaSalida;
    }
    public function getidSaldoInventario(){
        return $this->idSaldoInventario;
    }
    public function setidSaldoInventario($idSaldoInventario){
        $this->idSaldoInventario=$idSaldoInventario;
    }
    public function getidMovimientoInventario(){
        return $this->idMovimientoInventario;
    }
    public function setidMovimientoInventario($idMovimientoInventario){
        $this->idMovimientoInventario=$idMovimientoInventario;
    }

    public function guardarMovimientoInventario($idSaldoInventario,$entradaSalida,$cantidad,$valorMovimiento,$fecha,$idUsuarioCreacion)
    {
        $datos=array(
                'idUsuarioCreacion'=> $idUsuarioCreacion,
                'fecha'=> $fecha,
                'valorMovimiento'=> $valorMovimiento,
                'cantidad'=> $cantidad,
                'entradaSalida'=> $entradaSalida,
                'idSaldoInventario'=> $idSaldoInventario
        );
        $result=$this->insert($datos);
        if($result>0)
            return true;
        return false;
    }

    public function modificarMovimientoInventario($idMovimientoInventario,$idSaldoInventario,$entradaSalida,$cantidad,$valorMovimiento,$fecha,$idUsuarioCreacion)
    {
        $datos=array(
                'idUsuarioCreacion'=> $idUsuarioCreacion,
                'fecha'=> $fecha,
                'valorMovimiento'=> $valorMovimiento,
                'cantidad'=> $cantidad,
                'entradaSalida'=> $entradaSalida,
                'idSaldoInventario'=> $idSaldoInventario
        );
        $result=$this->update($datos,array('idMovimientoInventario'=>$idMovimientoInventario));
        if($result>0)
            return true;
        return false;
    }

    public function consultarTodoMovimientoInventario()
    {
        return $this->select()->toArray();
    }
    public function consultarMovimientoInventarioPoridMovimientoInventario($idMovimientoInventario)
    {
        $result=$this->select(array('idmovimientoinventario'=>$idMovimientoInventario))->current();
        if($result)
        {
            $this->LlenarEntidad($result);
            return true;
        }
        return false;
    }
    public function consultarMovimientoInventarioPoridSaldoInventario($idSaldoInventario)
    {
        $result=$this->select(array('idsaldoinventario'=>$idSaldoInventario))->current();
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
        $this->fecha=$result['fecha'];
        $this->valorMovimiento=$result['valorMovimiento'];
        $this->cantidad=$result['cantidad'];
        $this->entradaSalida=$result['entradaSalida'];
        $this->idSaldoInventario=$result['idSaldoInventario'];
        $this->idMovimientoInventario=$result['idMovimientoInventario'];
    }
}
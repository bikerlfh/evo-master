<?php
namespace Application\Model\Entity;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Adapter\Adapter;

class Producto extends AbstractTableGateway
{
    private $idProducto;
    private $idMarca;
    private $idCategoria;
    private $codigo;
    private $nombre;
    private $referencia;
    private $descripcion;
    private $especificacion;
    private $idUsuarioCreacion;
    private $fechaCreacion;
    public function __construct(Adapter $adapter = null)
    {
        $this->adapter = $adapter;
        $this->table =  new \Zend\Db\Sql\TableIdentifier('Producto', 'Producto');
    }

    public function getfechaCreacion(){
        return $this->fechaCreacion;
    }
    public function setfechaCreacion($fechaCreacion){
        $this->fechaCreacion=$fechaCreacion;
    }
    public function getidUsuarioCreacion(){
        return $this->idUsuarioCreacion;
    }
    public function setidUsuarioCreacion($idUsuarioCreacion){
        $this->idUsuarioCreacion=$idUsuarioCreacion;
    }
    public function getespecificacion(){
        return $this->especificacion;
    }
    public function setespecificacion($especificacion){
        $this->especificacion=$especificacion;
    }
    public function getdescripcion(){
        return $this->descripcion;
    }
    public function setdescripcion($descripcion){
        $this->descripcion=$descripcion;
    }
    public function getreferencia(){
        return $this->referencia;
    }
    public function setreferencia($referencia){
        $this->referencia=$referencia;
    }
    public function getnombre(){
        return $this->nombre;
    }
    public function setnombre($nombre){
        $this->nombre=$nombre;
    }
    public function getcodigo(){
        return $this->codigo;
    }
    public function setcodigo($codigo){
        $this->codigo=$codigo;
    }
    public function getidCategoria(){
        return $this->idCategoria;
    }
    public function setidCategoria($idCategoria){
        $this->idCategoria=$idCategoria;
    }
    public function getidMarca(){
        return $this->idMarca;
    }
    public function setidMarca($idMarca){
        $this->idMarca=$idMarca;
    }
    public function getidProducto(){
        return $this->idProducto;
    }
    public function setidProducto($idProducto){
        $this->idProducto=$idProducto;
    }

    public function guardarProducto($idMarca,$idCategoria,$codigo,$nombre,$referencia,$descripcion,$especificacion,$idUsuarioCreacion,$fechaCreacion)
    {
        $datos=array(
                'fechaCreacion'=> $fechaCreacion,
                'idUsuarioCreacion'=> $idUsuarioCreacion,
                'especificacion'=> $especificacion,
                'descripcion'=> $descripcion,
                'referencia'=> $referencia,
                'nombre'=> $nombre,
                'codigo'=> $codigo,
                'idCategoria'=> $idCategoria,
                'idMarca'=> $idMarca
        );
        $result=$this->insert($datos);
        if($result>0)
            return true;
        return false;
    }

    public function modificarProducto($idProducto,$idMarca,$idCategoria,$codigo,$nombre,$referencia,$descripcion,$especificacion,$idUsuarioCreacion,$fechaCreacion)
    {
        $datos=array(
                'fechaCreacion'=> $fechaCreacion,
                'idUsuarioCreacion'=> $idUsuarioCreacion,
                'especificacion'=> $especificacion,
                'descripcion'=> $descripcion,
                'referencia'=> $referencia,
                'nombre'=> $nombre,
                'codigo'=> $codigo,
                'idCategoria'=> $idCategoria,
                'idMarca'=> $idMarca
        );
        $result=$this->update($datos,array('idProducto'=>$idProducto));
        if($result>0)
            return true;
        return false;
    }

    public function consultarTodoProducto()
    {
        return $this->select()->toArray();
    }
    public function consultarProductoPoridProducto($idProducto)
    {
        $result=$this->select(array('idproducto'=>$idProducto))->current();
        if($result)
        {
            $this->LlenarEntidad($result);
            return true;
        }
        return false;
    }
    public function consultarProductoPoridMarca($idMarca)
    {
        $result=$this->select(array('idmarca'=>$idMarca))->current();
        if($result)
        {
            $this->LlenarEntidad($result);
            return true;
        }
        return false;
    }
    public function consultarProductoPoridCategoria($idCategoria)
    {
        $result=$this->select(array('idcategoria'=>$idCategoria))->current();
        if($result)
        {
            $this->LlenarEntidad($result);
            return true;
        }
        return false;
    }
    public function consultarProductoPorcodigo($codigo)
    {
        $result=$this->select(array('codigo'=>$codigo))->current();
        if($result)
        {
            $this->LlenarEntidad($result);
            return true;
        }
        return false;
    }
    
    private function LlenarEntidad($result)
    {
        $this->fechaCreacion=$result['fechaCreacion'];
        $this->idUsuarioCreacion=$result['idUsuarioCreacion'];
        $this->especificacion=$result['especificacion'];
        $this->descripcion=$result['descripcion'];
        $this->referencia=$result['referencia'];
        $this->nombre=$result['nombre'];
        $this->codigo=$result['codigo'];
        $this->idCategoria=$result['idCategoria'];
        $this->idMarca=$result['idMarca'];
        $this->idProducto=$result['idProducto'];
    }
}
<?php
namespace Application\Model\Entity;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\TableIdentifier;
use Zend\Db\Sql\Expression;
use Application\Model\Clases;

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

    public function getFechaCreacion(){
        return $this->fechaCreacion;
    }
    public function setFechaCreacion($fechaCreacion){
        $this->fechaCreacion=$fechaCreacion;
    }
    public function getIdUsuarioCreacion(){
        return $this->idUsuarioCreacion;
    }
    public function setIdUsuarioCreacion($idUsuarioCreacion){
        $this->idUsuarioCreacion=$idUsuarioCreacion;
    }
    public function getEspecificacion(){
        return $this->especificacion;
    }
    public function setEspecificacion($especificacion){
        $this->especificacion=$especificacion;
    }
    public function getDescripcion(){
        return $this->descripcion;
    }
    public function setDescripcion($descripcion){
        $this->descripcion=$descripcion;
    }
    public function getReferencia(){
        return $this->referencia;
    }
    public function setReferencia($referencia){
        $this->referencia=$referencia;
    }
    public function getNombre(){
        return $this->nombre;
    }
    public function setNombre($nombre){
        $this->nombre=$nombre;
    }
    public function getCodigo(){
        return $this->codigo;
    }
    public function setCodigo($codigo){
        $this->codigo=$codigo;
    }
    public function getIdCategoria(){
        return $this->idCategoria;
    }
    public function setIdCategoria($idCategoria){
        $this->idCategoria=$idCategoria;
    }
    public function getIdMarca(){
        return $this->idMarca;
    }
    public function setIdMarca($idMarca){
        $this->idMarca=$idMarca;
    }
    public function getIdProducto(){
        return $this->idProducto;
    }
    public function setIdProducto($idProducto){
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

    public function modificarProducto($idProducto,$idMarca,$idCategoria,$codigo,$nombre,$referencia,$descripcion,$especificacion)
    {
        $datos=array(
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
    
    public function eliminarProducto($idProducto)
    {
        if($this->delete(array('idProducto'=>$idProducto))>0)
            return true;
        return false;
    }
    
    public function consultarTodoProducto()
    {
        $sql = new Sql($this->adapter);        
        $select = $sql->select()->
                        from(array('p'=> $this->table))->
                        join(array("m"=> new TableIdentifier("Marca","Producto")),
                                    "m.idMarca = p.idMarca",
                                    array("descripcionMarca"=> new Expression("m.codigo + ' - ' + m.descripcion")))->
                        join(array("c"=> new TableIdentifier("Categoria","Producto")),
                                    "c.idCategoria = p.idCategoria",
                                    array("descripcionCategoria"=> new Expression("c.codigo + ' - ' + c.descripcion")));
        
        $results = $sql->prepareStatementForSqlObject($select)->execute();
        $resultsSet = new ResultSet();
        return $resultsSet->initialize($results)->toArray();
    }
    public function consultarProductoPorIdProductoSimple($idProducto)
    {
        $sql = new Sql($this->adapter);        
        $select = $sql->select()->
                        from(array('p'=> $this->table))->
                        join(array("m"=> new TableIdentifier("Marca","Producto")),
                                    "m.idMarca = p.idMarca",
                                    array("descripcionMarca"=>"descripcion"))->
                        join(array("c"=> new TableIdentifier("Categoria","Producto")),
                                    "c.idCategoria = p.idCategoria",
                                    array("descripcionCategoria"=> "descripcion"))->
                        where(array('p.idProducto' => $idProducto));
        
        $results = $sql->prepareStatementForSqlObject($select)->execute();
        $resultsSet = new ResultSet();
        return $resultsSet->initialize($results)->current();
    }
    public function consultarProductoPorIdProducto($idProducto)
    {
        $result=$this->select(array('idproducto'=>$idProducto))->current();
        if($result)
        {
            $this->LlenarEntidad($result);
            return true;
        }
        return false;
    }
    public function consultarProductoPorIdMarca($idMarca)
    {
        return $this->select(array('idMarca'=>$idMarca))->toArray();
    }
    public function consultarProductoPorIdCategoria($idCategoria)
    {
        return $this->select(array('idCategoria'=>$idCategoria))->toArray();
    }
    public function consultarProductoPorCodigo($codigo)
    {
        $result=$this->select(array('codigo'=>$codigo))->current();
        if($result)
        {
            $this->LlenarEntidad($result);
            return true;
        }
        return false;
    }
    //public function consultaAvanzadaProducto($referencia,$codigo,$nombre,$idMarca,$idCategoria)
    public function consultaAvanzadaProducto($idMarca,$idCategoria,$referencia,$codigo,$nombre)
    {
        $where = array();
        if ($idMarca > 0) {
            array_push($where,"p.idMarca=".$idMarca);
        }
        if ($idCategoria > 0) {
            array_push($where,"p.idCategoria=".$idCategoria);
        }
        if (strlen($referencia) > 0) {
            array_push($where,"p.referencia like '%".$referencia."'");
        }
        if (strlen($codigo) > 0) {
            array_push($where,"p.codigo like '%".$codigo."'");
        }
        if (strlen($nombre) > 0) {
            array_push($where,"p.nombre like '%".$nombre."'");
        }
        
        $sql = new Sql($this->adapter);
        $select = $sql->select()->
                        from(array('p'=> $this->table))->
                        join(array("m"=> new TableIdentifier("Marca","Producto")),
                                    "m.idMarca = p.idMarca",
                                    array("descripcionMarca"=> new Expression("m.codigo + ' - ' + m.descripcion")))->
                        join(array("c"=> new TableIdentifier("Categoria","Producto")),
                                    "c.idCategoria = p.idCategoria",
                                    array("descripcionCategoria"=> new Expression("c.codigo + ' - ' + c.descripcion")))->
                        where($where);
        $result = $sql->prepareStatementForSqlObject($select)->execute();
        $resultsSet = new ResultSet();
        return $resultsSet->initialize($result)->toArray();
        
    }
    public function generarOptionsSelect($where = null)
    {
        $objs=$this->select($where)->toArray();
        $options=array(null);
        for($i=0;$i<count($objs);$i++)
        {
            $options[$objs[$i]['idProducto']]=$objs[$i]['codigo']." - ".$objs[$i]['nombre'];
        }
        return $options;
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
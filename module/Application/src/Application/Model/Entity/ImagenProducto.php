<?php
namespace Application\Model\Entity;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\TableIdentifier;
use Zend\Db\Sql\Expression;

class ImagenProducto extends AbstractTableGateway
{   
    private $idImagenProducto;
    private $idProducto;
    private $url;
    private $idUsuarioCreacion;
    
    public function __construct(Adapter $adapter = null)
    {
        $this->adapter = $adapter;
        $this->table =  new \Zend\Db\Sql\TableIdentifier('ImagenProducto', 'Producto');
    }

    public function getIdUsuarioCreacion(){
        return $this->idUsuarioCreacion;
    }
    public function setIdUsuarioCreacion($idUsuarioCreacion){
        $this->idUsuarioCreacion=$idUsuarioCreacion;
    }
    public function getUrl(){
        return $this->url;
    }
    public function setUrl($url){
        $this->url=$url;
    }
    public function getIdProducto(){
        return $this->idProducto;
    }
    public function setIdProducto($idProducto){
        $this->idProducto=$idProducto;
    }
    public function getidImagenProducto(){
        return $this->idImagenProducto;
    }
    public function setidImagenProducto($idImagenProducto){
        $this->idImagenProducto=$idImagenProducto;
    }

    public function guardarImagenProducto($idProducto,$url,$idUsuarioCreacion)
    {
        $datos=array(
                'idUsuarioCreacion'=> $idUsuarioCreacion,
                'url'=> $url,
                'idProducto'=> $idProducto
        );
        $result=$this->insert($datos);
        if($result>0)
            return true;
        return false;
    }

    public function modificarImagenProducto($idImagenProducto,$idProducto,$url,$idUsuarioCreacion)
    {
        $datos=array(
                'idUsuarioCreacion'=> $idUsuarioCreacion,
                'url'=> $url,
                'idProducto'=> $idProducto
        );
        $result=$this->update($datos,array('idImagenProducto'=>$idImagenProducto));
        if($result>0)
            return true;
        return false;
    }
    public function eliminarImagenProducto($idImagenProducto)
    {
        if($this->delete(array('idImagenProducto'=>$idImagenProducto))>0)
            return true;
        return false;
    }
    public function consultarTodoImagenProducto()
    {
        $sql = new Sql($this->adapter);        
        $select = $sql->select()->
                         from(array('i'=> $this->table))->
                         join(array("p"=> new TableIdentifier("Producto","Producto")),
                                    "i.idProducto = p.idProducto",
                                    array("descripcionProducto"=> new Expression("p.codigo + ' - ' + p.nombre")));
        
        $results = $sql->prepareStatementForSqlObject($select)->execute();
        $resultsSet = new ResultSet();
        return $resultsSet->initialize($results)->toArray();
    }
    public function consultarImagenProductoPoridImagenProducto($idImagenProducto)
    {
        $result=$this->select(array('idImagenProducto'=>$idImagenProducto))->current();
        if($result)
        {
            $this->LlenarEntidad($result);
            return true;
        }
        return false;
    }
    public function consultarImagenProductoPorIdProducto($idProducto)
    {
        
        $sql = new Sql($this->adapter);
        $select = $sql->select()->
                        from(array('i'=> $this->table))->
                        join(array("p"=> new TableIdentifier("Producto","Producto")),
                                    "i.idProducto = p.idProducto",
                                    array("descripcionProducto"=> new Expression("p.codigo + ' - ' + p.nombre")))->
                        where(array('i.idProducto'=> $idProducto));
        $results = $sql->prepareStatementForSqlObject($select)->execute();
        $resultsSet = new ResultSet();
        return $resultsSet->initialize($results)->toArray();
        
        //return $this->select(array('idProducto'=>$idProducto))->toArray();
    }
    public function consultarImagenProductoPorUrl($url)
    {
        $result=$this->select(array('url'=>$url))->current();
        if($result)
        {
            return true;
        }
        return false;
    }
    private function LlenarEntidad($result)
    {
        $this->idUsuarioCreacion=$result['idUsuarioCreacion'];
        $this->url=$result['url'];
        $this->idProducto=$result['idProducto'];
        $this->idImagenProducto=$result['idImagenProducto'];
    }
}
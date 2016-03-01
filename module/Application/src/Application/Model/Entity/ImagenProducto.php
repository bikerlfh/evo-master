<?php
namespace Application\Model\Entity;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Adapter\Adapter;

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

    public function getidUsuarioCreacion(){
        return $this->idUsuarioCreacion;
    }
    public function setidUsuarioCreacion($idUsuarioCreacion){
        $this->idUsuarioCreacion=$idUsuarioCreacion;
    }
    public function geturl(){
        return $this->url;
    }
    public function seturl($url){
        $this->url=$url;
    }
    public function getidProducto(){
        return $this->idProducto;
    }
    public function setidProducto($idProducto){
        $this->idProducto=$idProducto;
    }
    public function getidImagenProducto(){
        return $this->idImagenProducto;
    }
    public function setidImagenProducto($idImagenProducto){
        $this->idImagenProducto=$idImagenProducto;
    }

    public function guardarImagenproducto($idProducto,$url,$idUsuarioCreacion)
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

    public function modificarImagenproducto($idImagenProducto,$idProducto,$url,$idUsuarioCreacion)
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

    public function consultarImagenproducto()
    {
        return $this->select()->toArray();
    }
    public function consultarImagenproductoPoridImagenProducto($idImagenProducto)
    {
        $result=$this->select(array('idimagenproducto'=>$idImagenProducto))->current();
        if($result)
        {
            $this->LlenarEntidad($result);
            return true;
        }
        return false;
    }
    public function consultarImagenproductoPoridProducto($idProducto)
    {
        $result=$this->select(array('idproducto'=>$idProducto))->current();
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
        $this->url=$result['url'];
        $this->idProducto=$result['idProducto'];
        $this->idImagenProducto=$result['idImagenProducto'];
    }
}
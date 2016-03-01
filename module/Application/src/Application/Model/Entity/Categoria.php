<?php
namespace Application\Model\Entity;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Adapter\Adapter;

class Categoria extends AbstractTableGateway
{
    private $idUsuarioCreacion;
    private $descripcion;
    private $codigo;
    private $idCategoria;
    public function __construct(Adapter $adapter = null)
    {
        $this->adapter = $adapter;
        $this->table =  new \Zend\Db\Sql\TableIdentifier('Categoria', 'Producto');
    }

    public function getidUsuarioCreacion(){
        return $this->idUsuarioCreacion;
    }
    public function setidUsuarioCreacion($idUsuarioCreacion){
        $this->idUsuarioCreacion=$idUsuarioCreacion;
    }
    public function getdescripcion(){
        return $this->descripcion;
    }
    public function setdescripcion($descripcion){
        $this->descripcion=$descripcion;
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

    public function guardarCategoria($codigo,$descripcion,$idUsuarioCreacion)
    {
        $datos=array(
                'idUsuarioCreacion'=> $idUsuarioCreacion,
                'descripcion'=> $descripcion,
                'codigo'=> $codigo,
        );
        $result=$this->insert($datos);
        if($result>0)
            return true;
        return false;
    }

    public function modificarCategoria($idCategoria,$codigo,$descripcion,$idUsuarioCreacion)
    {
        $datos=array(
                'idUsuarioCreacion'=> $idUsuarioCreacion,
                'descripcion'=> $descripcion,
                'codigo'=> $codigo
        );
        $result=$this->update($datos,array('idCategoria'=>$$idCategoria));
        if ($result > 0) {
            return true;
        }
        return false;
    }

    public function consultarTodoCategoria()
    {
        return $this->select()->toArray();
    }
    public function consultarategoriaPoridCategoria($idCategoria)
    {
        $result=$this->select(array('idcategoria'=>$idCategoria))->current();
        if($result)
        {
            $this->idCategoria=$result['idCategoria'];
            $this->codigo=$result['codigo'];
            $this->descripcion=$result['descripcion'];
            $this->idUsuarioCreacion=$result['idUsuarioCreacion'];
            return true;
        }
        return false;
    }
    public function consultarCategoriaPorcodigo($codigo)
    {
        $result=$this->select(array('codigo'=>$codigo))->current();
        if($result)
        {
            $this->idCategoria=$result['idCategoria'];
            $this->codigo=$result['codigo'];
            $this->descripcion=$result['descripcion'];
            $this->idUsuarioCreacion=$result['idUsuarioCreacion'];
            return true;
        }
        return false;
    }
}
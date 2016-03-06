<?php
namespace Application\Model\Entity;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Adapter\Adapter;

class Categoria extends AbstractTableGateway
{
    private $idCategoria;
    private $codigo;
    private $descripcion;
    
    public function __construct(Adapter $adapter = null)
    {
        $this->adapter = $adapter;
        $this->table =  new \Zend\Db\Sql\TableIdentifier('Categoria', 'Producto');
    }
    public function getDescripcion(){
        return $this->descripcion;
    }
    public function setDescripcion($descripcion){
        $this->descripcion=$descripcion;
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

    public function guardarCategoria($codigo,$descripcion)
    {
        $datos=array(
                'descripcion'=> $descripcion,
                'codigo'=> $codigo);
        $result=$this->insert($datos);
        if($result>0)
            return true;
        return false;
    }

    public function modificarCategoria($idCategoria,$codigo,$descripcion)
    {
        $datos=array(
                'descripcion'=> $descripcion,
                'codigo'=> $codigo
        );
        $result=$this->update($datos,array('idCategoria'=>$idCategoria));
        if ($result > 0) {
            return true;
        }
        return false;
    }
    public function eliminarCategoria($idCategoria)
    {
        if ($this->delete(array('idCategoria'=>$idCategoria)) > 0)
            return true;
        return false;
    }

    public function consultarTodoCategoria()
    {
        return $this->select()->toArray();
    }
    public function consultarCategoriaPorIdCategoria($idCategoria)
    {
        $result=$this->select(array('idcategoria'=>$idCategoria))->current();
        if($result)
        {
            $this->idCategoria=$result['idCategoria'];
            $this->codigo=$result['codigo'];
            $this->descripcion=$result['descripcion'];
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
            return true;
        }
        return false;
    }
    
    public function generarOptionsSelect($where = null)
    {
        $objs=$this->select($where)->toArray();
        $options=array(null);
        for($i=0;$i<count($objs);$i++)
        {
            $options[$objs[$i]['idCategoria']]=$objs[$i]['codigo']." - ".$objs[$i]['descripcion'];
        }
        return $options;
    }
}
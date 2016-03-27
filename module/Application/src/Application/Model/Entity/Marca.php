<?php
namespace Application\Model\Entity;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Adapter\Adapter;

class Marca extends AbstractTableGateway
{
    private $idMarca;
    private $codigo;
    private $descripcion;
    
    public function __construct(Adapter $adapter = null)
    {
        $this->adapter = $adapter;
        $this->table =  new \Zend\Db\Sql\TableIdentifier('Marca', 'Producto');
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
    public function getIdMarca(){
        return $this->idMarca;
    }
    public function setIdMarca($idMarca){
        $this->idMarca=$idMarca;
    }

    public function guardarMarca($codigo,$descripcion)
    {
        $datos=array(
                'descripcion'=> $descripcion,
                'codigo'=> $codigo
        );
        $result=$this->insert($datos);
        if($result>0)
            return true;
        return false;
    }

    public function modificarMarca($idMarca,$codigo,$descripcion)
    {
        $datos=array(
                'descripcion'=> $descripcion,
                'codigo'=> $codigo
        );
        $result=$this->update($datos,array('idMarca'=>$idMarca));
        if($result>0)
            return true;
        return false;
    }
    
    public function eliminarMarca($idMarca)
    {
        if ($this->delete(array('idMarca'=>$idMarca))>0)
            return true;
        return false;
    }
    
    public function consultarTodoMarca()
    {
        return $this->select()->toArray();
    }
    public function consultarTodoMarcaCountNumeroProductos()
    {
        $select = "select m.*, (select count(*) from Producto.Producto p where p.idMarca = m.idMarca) as 'numProducto' from Producto.Marca m order by m.descripcion";
        $stmt = $this->adapter->createStatement()->setSql($select);
        return $stmt->execute();
    }
    public function consultarMarcaPoridMarca($idMarca)
    {
        $result=$this->select(array('idMarca'=>$idMarca))->current();
        if($result)
        {
            $this->idMarca=$result['idMarca'];
            $this->codigo=$result['codigo'];
            $this->descripcion=$result['descripcion'];
            return true;
        }
        return false;
    }
    
    public function consultarMarcaPorCodigo($codigo)
    {
        $result=$this->select(array('codigo'=>$codigo))->current();
        if($result)
        {
            $this->idMarca=$result['idMarca'];
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
            $options[$objs[$i]['idMarca']]=$objs[$i]['codigo']." - ".$objs[$i]['descripcion'];
        }
        return $options;
    }
}
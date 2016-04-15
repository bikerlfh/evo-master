<?php
namespace Application\Model\Entity;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\TableIdentifier;
use Zend\Db\Sql\Expression;

class Categoria extends AbstractTableGateway
{
    private $idCategoria;
    private $idCategoriaCentral;
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
    function getIdCategoriaCentral() {
        return $this->idCategoriaCentral;
    }

    function setIdCategoriaCentral($idCategoriaCentral) {
        $this->idCategoriaCentral = $idCategoriaCentral;
    }

    public function getIdCategoria(){
        return $this->idCategoria;
    }
    public function setIdCategoria($idCategoria){
        $this->idCategoria=$idCategoria;
    }

    public function guardarCategoria($idCategoriaCentral,$codigo,$descripcion)
    {
        $datos=array(
                'descripcion'=> $descripcion,
                'codigo'=> $codigo);
        
        if ($idCategoriaCentral != null) {
            $datos['idCategoriaCentral'] = $idCategoriaCentral;
        }
        $result=$this->insert($datos);
        if($result>0)
            return true;
        return false;
    }

    public function modificarCategoria($idCategoria,$idCategoriaCentral,$codigo,$descripcion)
    {
        $datos=array(
                'descripcion'=> $descripcion,
                'codigo'=> $codigo
        );
        if ($idCategoriaCentral != null) {
            $datos['idCategoriaCentral'] = $idCategoriaCentral;
        }
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
        /*$sql = new Sql($this->adapter);        
        $select = $sql->select()->
                         from(array('c'=> $this->table))->
                         columns(array('idCategoria','idCategoriaCentral','codigo','descripcion'))->
                         join(array("cat2"=> new TableIdentifier("Categoria","Producto")),
                                    "cat2.idCategoria = c.idCategoriaCentral",
                                    array("descripcionCategoriaCentral"=> new Expression("(cat2.codigo + ' - ' + cat2.descripcion)")),'left');
        
        $results = $sql->prepareStatementForSqlObject($select)->execute();
        $resultsSet = new ResultSet();
        $resultsSet->initialize($results)->toArray();
        return   $resultsSet;*/
    }
    
    public function consultarTodoCategoriaCountNumeroProductos()
    {
        $select = "select c.*, 
                    (select count(*) from Producto.Producto p 
                    inner join Inventario.SaldoInventario si on p.idProducto = si.idProducto and si.estado = 1
                    where p.idCategoria = c.idCategoria) as 'numProducto' 
                    from Producto.Categoria c order by c.descripcion";
        $stmt = $this->adapter->createStatement()->setSql($select);
        return $stmt->execute();
    }
    
    public function consultarCategoriaPorIdCategoria($idCategoria)
    {
        $result=$this->select(array('idCategoria'=>$idCategoria))->current();
        if($result)
        {
            $this->LlenarEntidad($result);
            return true;
        }
        return false;
    }
    public function consultarCategoriaPorIdCategoriaCentral($idCategoriaCentral)
    {
        return $this->select(array('idCategoriaCentral'=>$idCategoriaCentral))->toArray();
    }
    public function consultarCategoriaPorCodigo($codigo)
    {
        $result=$this->select(array('codigo'=>$codigo))->current();
        if($result)
        {
            $this->LlenarEntidad($result);
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
    private function LlenarEntidad($result)
    {
        $this->idCategoria=$result['idCategoria'];
        $this->idCategoriaCentral=$result['idCategoriaCentral'];
        $this->codigo=$result['codigo'];
        $this->descripcion=$result['descripcion'];
    }
}
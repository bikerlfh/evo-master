<?php

namespace Application\Model\Entity;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\TableIdentifier;
use Application\Model\Clases\StoredProcedure;
use Zend\Db\Sql\Expression;

class Promocion extends AbstractTableGateway
{
    
    private $idPromocion;
    private $idSaldoInventario;
    private $valorAnterior;
    private $valorPromocion;
    private $fechaDesde;
    private $fechaHasta;
    private $estado;
    private $idUsuarioCreacion;
    
    public $idProducto;
    public $nombreProducto;
    public function __construct(Adapter $adapter = null)
    {
        $this->adapter = $adapter;
        $this->table =  new \Zend\Db\Sql\TableIdentifier('Promocion', 'Producto');
    }
    
    function getIdPromocion() { return $this->idPromocion; }
    function getIdSaldoInventario() { return $this->idSaldoInventario; }
    function getValorAnterior() { return $this->valorAnterior; }
    function getValorPromocion() { return $this->valorPromocion; }
    function getFechaDesde() { return $this->fechaDesde; }
    function getFechaHasta() { return $this->fechaHasta; }
    function getEstado() { return $this->estado; }
    function getIdUsuarioCreacion() { return $this->idUsuarioCreacion; }
    function setIdPromocion($idPromocion) { $this->idPromocion = $idPromocion; }
    function setIdSaldoInventario($idSaldoInventario) { $this->idSaldoInventario = $idSaldoInventario; }
    function setValorAnterior($valorAnterior) { $this->valorAnterior = $valorAnterior; }
    function setValorPromocion($valorPromocion) { $this->valorPromocion = $valorPromocion; }
    function setFechaDesde($fechaDesde) { $this->fechaDesde = $fechaDesde; }
    function setFechaHasta($fechaHasta) { $this->fechaHasta = $fechaHasta; }
    function setEstado($estado) { $this->estado = $estado; }
    function setIdUsuarioCreacion($idUsuarioCreacion) { $this->idUsuarioCreacion = $idUsuarioCreacion; }

    public function guardarPromocion($idSaldoInventario,$valorAnterior,$valorPromocion,$fechaDesde,$fechaHasta,$estado,$idUsuarioCreacion)
    {
        $datos=array(
                'idSaldoInventario'=> $idSaldoInventario,
                'valorAnterior'=> $valorAnterior,
                'valorPromocion'=> $valorPromocion,
                'fechaDesde'=> $fechaDesde,
                'fechaHasta'=> $fechaHasta,
                'estado'=>$estado,
                'idUsuarioCreacion'=> $idUsuarioCreacion);
        
        $result=$this->insert($datos);
        if($result>0)
            return true;
        return false;
    }
    
    public function modificarPromocion($idPromocion,$idSaldoInventario,$valorAnterior,$valorPromocion,$fechaDesde,$fechaHasta,$estado)
    {
        $datos=array(
                'idSaldoInventario'=> $idSaldoInventario,
                'valorAnterior'=> $valorAnterior,
                'valorPromocion'=> $valorPromocion,
                'fechaDesde'=> $fechaDesde,
                'fechaHasta'=> $fechaHasta,
                'estado'=> $estado);
        
        $result=$this->update($datos,array('idPromocion'=>$idPromocion));
        if($result>0)
            return true;
        return false;
    }
    
    public function eliminarPromocion($idPromocion)
    {
        if ($this->delete(array('idPromocion'=>$idPromocion)) > 0)
            return true;
        return false;
    }
    
    public function consultarTodoPromocion()
    {
        $sql = new Sql($this->adapter);        
        $select = $sql->select()->
                         from(array('pro'=> $this->table))->
                         columns(array('idPromocion','idSaldoInventario','valorAnterior','valorPromocion','fechaDesde','fechaHasta','estado'))->
                         join(array('s'=> new TableIdentifier("SaldoInventario","Inventario")),
                                    's.idSaldoInventario = pro.idSaldoInventario')->
                         join(array("p"=> new TableIdentifier("Producto","Producto")),
                                    "p.idProducto = s.idProducto",
                                    array("nombreProducto"=> new Expression("(p.codigo + ' - ' + p.nombre)")));
        
        $result = $sql->prepareStatementForSqlObject($select)->execute();
        $resultsSet = new ResultSet();
        return $resultsSet->initialize($result)->toArray();
    }
    
    public function consultarPromocionPorIdPromocion($idPromocion)
    {
        $sql = new Sql($this->adapter);        
        $select = $sql->select()->
                         from(array('pro'=> $this->table))->
                         columns(array('idPromocion','idSaldoInventario','valorAnterior','valorPromocion','fechaDesde','fechaHasta','estado'))->
                         join(array('s'=> new TableIdentifier("SaldoInventario","Inventario")),
                                    's.idSaldoInventario = pro.idSaldoInventario')->
                         join(array("p"=> new TableIdentifier("Producto","Producto")),
                                    "p.idProducto = s.idProducto",
                                    array("nombreProducto"=> new Expression("p.codigo + ' - ' + p.nombre")))->
                        where(array('idPromocion'=>$idPromocion));
        
        $results = $sql->prepareStatementForSqlObject($select)->execute();
        $resultsSet = new ResultSet();
        $resultsSet->initialize($results)->current();
       // return   $resultsSet;
        if($resultsSet)
        {
            $this->LlenarEntidad($resultsSet);
            return true;
        }
        return false;
        //$result=$this->select(array('idPromocion'=>$idPromocion))->current();
        
    }
    
    public function consultarPromocionPorIdSaldoInventario($idSaldoInventario)
    {
        $result=$this->select(array('idSaldoInventario'=>$idSaldoInventario))->current();
        if($result)
        {
            $this->LlenarEntidad($result);
            return true;
        }
        return false;
    }
    public function consultaAvanzadaPromocion($idProducto,$idProveedor,$estado)
    {
        $estado = $estado > 0? $estado:null;
        $where = array();
        if ($idProducto > 0) {
            array_push($where,"s.idProducto=".$idProducto);
        }
        if ($idProveedor > 0) {
            array_push($where,"s.idProveedor=".$idProveedor);
        }
        if ($estado != null) {
            array_push($where,"pro.estado=".$estado);
        }
        
        $sql = new Sql($this->adapter);        
        $select = $sql->select()->
                         from(array('pro'=> $this->table))->
                         columns(array('idPromocion','idSaldoInventario','valorAnterior','valorPromocion','fechaDesde','fechaHasta','estado'))->
                         join(array('s'=> new TableIdentifier("SaldoInventario","Inventario")),
                                    's.idSaldoInventario = pro.idSaldoInventario')->
                         join(array("p"=> new TableIdentifier("Producto","Producto")),
                                    "p.idProducto = s.idProducto",
                                    array("nombreProducto"=> new Expression("(p.codigo + ' - ' + p.nombre)")))->
                         join(array("proveedor"=> new TableIdentifier("Proveedor","Tercero")),
                                    "s.idProveedor = proveedor.idProveedor")->
                         join(array("tercero"=> new TableIdentifier("DatoBasicoTercero","Tercero")),
                                    "tercero.idDatoBasicoTercero = proveedor.idDatoBasicoTercero",
                                    array("nombreProveedor"=> new Expression("CONVERT(VARCHAR,tercero.nit)+' - ' + tercero.descripcion")))->
                         where($where);
        
        $result = $sql->prepareStatementForSqlObject($select)->execute();
        $resultsSet = new ResultSet();
        return $resultsSet->initialize($result)->toArray();
    }
    /*
     *  Consulta la vista de promociones para el cliente 
     
    public function vistaConsultaPromocionCliente()
    {
        $sql = new Sql($this->adapter);  
        $select = $sql->select()->from(array('pro'=> new TableIdentifier("vConsultarPromocionCliente", "Venta")));
        $results = $sql->prepareStatementForSqlObject($select)->execute();
        $resultsSet = new ResultSet();
        return $resultsSet->initialize($results)->toArray();
    }*/
    
    private function LlenarEntidad($result)
    {
        $result = $result->getDataSource()->current();
        $this->idPromocion=$result['idPromocion'];
        $this->idSaldoInventario=$result['idSaldoInventario'];
        $this->idProducto = $result['idProducto'];
        $this->nombreProducto=$result['nombreProducto'];
        $this->valorAnterior=$result['valorAnterior'];
        $this->valorPromocion=$result['valorPromocion'];
        $this->fechaDesde=$result['fechaDesde'];
        $this->fechaHasta=$result['fechaHasta'];
        $this->estado=$result['estado'];
        $this->idUsuarioCreacion=$result['idUsuarioCreacion'];
    }
}
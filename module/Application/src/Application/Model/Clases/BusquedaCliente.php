<?php

/* 
 * Clase BusquedaCliente
 * Autor: Luis Fernando Henriquez Arciniegas
 * Fecha Creación: 14/04/2016
 * Descripción: Clase que contiene todas las busquedas que la vista del cliente necesita.
 * 
 */

namespace Application\Model\Clases;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\TableIdentifier;

class BusquedaCliente
{
    private $adapter;
    
    public $pageCount = 0;
    
    public function __construct(Adapter $adapter = null)
    {
        $this->adapter = $adapter;
    }
    /************************** BUSQUEDAS*****************************************/
    public function busquedaPromociones()
    {
        return $this->vistaConsultaProductoSimple(array("idPromocion IS NOT NULL "));
    }
    public function busquedaProductosNuevos()
    {
        return $this->vistaConsultaProductoSimple(array("fechaCreacionSaldoInventario >". date('Y-m-d', strtotime(date('Y-m-d H:i:s'). ' - 30 days'))));
    }
    public function busquedaProductosMasVendidos($where = array())
    {
        return $this->vistaConsultaProductoSimple(array("numeroVentas > 20"));
    }
    
    public function busquedaProductosSimilares($idProducto,$idCategoria)
    {
        $where = array('idProducto != '. $idProducto,
                       'idCategoria = '.$idCategoria);
        return $this->vistaConsultaProductoSimple($where);
    }
    
    
    public function vistaConsultaProducto($where = array())
    {
        return $this->ejecutarSelect(new TableIdentifier("vConsultaProducto", "Venta"),$where);
    }
    public function vistaConsultaProductoSimple($where = array())
    {
        return $this->ejecutarSelect(new TableIdentifier("vConsultaProductoSimple", "Venta"),$where);
    }
    /******************************************************************************/
    public function busquedaProductoPaginada($pageSize,$pageNumber,$idMarca,$idCategoria,$filtro)
    {
        $stored = new StoredProcedure($this->adapter);
        $stmt = $this->adapter->createStatement();
        $stmt->prepare('DECLARE @pageCount INT;EXEC venta.BusquedaProductoPaginada ?,?,@pageCount OUT,?,?,?;SELECT @pageCount AS pageCount');
        $stmt->getResource()->bindParam(1,$pageSize); 
        $stmt->getResource()->bindParam(2,$pageNumber); 
        $stmt->getResource()->bindParam(3,$idMarca); 
        $stmt->getResource()->bindParam(4,$idCategoria); 
        $stmt->getResource()->bindParam(5,$filtro); 
        $result = $stmt->execute(); 
        
        $statement = $result->getResource();

        // Result set 1
        $resultSet1 = $statement->fetchAll(\PDO::FETCH_ASSOC);
        $statement->nextRowSet(); // Avanza al segundo result set
        $resultSet2 = $statement->fetchAll(\PDO::FETCH_ASSOC);
        $this->pageCount =  $resultSet2[0]['pageCount'];

        //$result = $stored->execProcedureReturnDatos("venta.BusquedaProductoPaginada ?,?,?,?,?,? ",array($pageSize,$pageNumber,$pageCount +" OUT" ,$idMarca,$idCategoria,$filtro));
        return $resultSet1;
    }
    /**********************************************************************
     * Metodos Privados
     **********************************************************************/
    
    private function ejecutarSelect($tableIdentifier,$where = array())
    {
        $sql = new Sql($this->adapter);  
        $select = $sql->select()->from($tableIdentifier)->where($where);
        $results = $sql->prepareStatementForSqlObject($select)->execute();
        $resultsSet = new ResultSet();
        return $resultsSet->initialize($results)->toArray();
    }
}

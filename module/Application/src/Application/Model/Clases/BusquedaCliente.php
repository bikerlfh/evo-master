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
        return $this->vistaConsultaProductoSimple(array("fechaCreacionSaldoInventario >". date('Y-m-d', strtotime(date('d-m-Y H:i:s'). ' - 30 days'))));
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
    /******************************************************************************/
    
    /**********************************************************************
     * Metodos Privados
     **********************************************************************/
    private function vistaConsultaProductoSimple($where = array())
    {
        return $this->ejecutarSelect(new TableIdentifier("vConsultaProductoSimple", "Venta"),$where);
    }
    
    private function ejecutarSelect($tableIdentifier,$where = array())
    {
        $sql = new Sql($this->adapter);  
        $select = $sql->select()->from($tableIdentifier)->where($where);
        $results = $sql->prepareStatementForSqlObject($select)->execute();
        $resultsSet = new ResultSet();
        return $resultsSet->initialize($results)->toArray();
    }
}

<?php
namespace Application\Model\Entity;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Adapter\Adapter;
use Application\Model\Entity\Proveedor;
use Zend\Db\Sql\Sql;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\TableIdentifier;
use Zend\Db\Sql\Expression;

class ProveedorCuenta extends AbstractTableGateway
{

    private $idProveedorCuenta;
    private $idProveedorOficina;
    private $numeroCuentaBancaria;
    private $idTipoCuenta;
    private $idViaPago;
    
    public $ProveedorOficina;

    public function __construct(Adapter $adapter = null)
    {
        $this->adapter = $adapter;
        $this->table =  new \Zend\Db\Sql\TableIdentifier('ProveedorCuenta', 'Tercero');
    }

    
    public function getIdProveedorCuenta(){
        return $this->idProveedorCuenta;
    }
    public function setIdProveedorCuenta($idProveedorCuenta){
        $this->idProveedorCuenta=$idProveedorCuenta;
    }
    function getIdProveedorOficina() {
        return $this->idProveedorOficina;
    }

    function getNumeroCuentaBancaria() {
        return $this->numeroCuentaBancaria;
    }

    function getIdTipoCuenta() {
        return $this->idTipoCuenta;
    }

    function getIdViaPago() {
        return $this->idViaPago;
    }

    function setIdProveedorOficina($idProveedorOficina) {
        $this->idProveedorOficina = $idProveedorOficina;
    }

    function setNumeroCuentaBancaria($numeroCuentaBancaria) {
        $this->numeroCuentaBancaria = $numeroCuentaBancaria;
    }

    function setIdTipoCuenta($idTipoCuenta) {
        $this->idTipoCuenta = $idTipoCuenta;
    }

    function setIdViaPago($idViaPago) {
        $this->idViaPago = $idViaPago;
    }

    public function guardarProveedorCuenta($idProveedorOficina,$numeroCuentaBancaria,$idTipoCuenta,$idViaPago)
    {
        $datos=array(
                'idProveedorOficina'=> $idProveedorOficina,
                'numeroCuentaBancaria'=> $numeroCuentaBancaria,
                'idTipoCuenta'=> $idTipoCuenta,
                'idViaPago'=> $idViaPago
        );
        $result=$this->insert($datos);
        if($result>0)
            return true;
        return false;
    }

    public function modificarProveedorCuenta($idProveedorCuenta,$idProveedorOficina,$numeroCuentaBancaria,$idTipoCuenta,$idViaPago)
    {
        $datos=array(
                'idProveedorOficina'=> $idProveedorOficina,
                'numeroCuentaBancaria'=> $numeroCuentaBancaria,
                'idTipoCuenta'=> $idTipoCuenta,
                'idViaPago'=> $idViaPago
        );
        $result=$this->update($datos,array('idProveedorCuenta'=>$idProveedorCuenta));
        if($result>0)
            return true;
        return false;
    }
    public function eliminarProveedorCuenta($idProveedorCuenta)
    {
        if ($this->delete(array('idProveedorCuenta'=>$idProveedorCuenta))>0) 
            return true;
        return false;
    }
    
    public function consultarTodoProveedorCuenta()
    {
        $sql = new Sql($this->adapter);
        $select = $sql->select()->
                from(array('pc'=>  $this->table))->
                join(array("po"=> new TableIdentifier("ProveedorOficina","Tercero")),
                                    "po.idProveedorOficina = pc.idProveedorOficina")->
                join(array("p"=> new TableIdentifier("Proveedor","Tercero")),
                                    "po.idProveedor = p.idProveedor")->
                join(array("d"=> new TableIdentifier("DatoBasicoTercero","Tercero")),
                                    "p.idDatoBasicoTercero = d.idDatoBasicoTercero",
                                    array("descripcionTercero"=> new Expression("convert(varchar,d.nit) + ' - ' + d.descripcion")));
        $results = $sql->prepareStatementForSqlObject($select)->execute();
        $resultsSet = new ResultSet();
        return $resultsSet->initialize($results)->toArray();
    }
    public function consultarProveedorCuentaPorIdProveedorCuenta($idProveedorCuenta)
    {
        $result=$this->select(array('idProveedorCuenta'=>$idProveedorCuenta))->current();
        if($result)
        {
            $this->LlenarEntidad($result);
            return true;
        }
        return false;
    }
    public function consultarProveedorCuentaPorIdProveedorOficina($idProveedorOficina)
    {
        $result=$this->select(array('idProveedorOficina'=>$idProveedorOficina))->current();
        if($result)
        {
            $this->LlenarEntidad($result);
            return true;
        }
        return false;
    }
    private function LlenarEntidad($result)
    {
        $this->idViaPago=$result['idViaPago'];
        $this->idTipoCuenta=$result['idTipoCuenta'];
        $this->numeroCuentaBancaria=$result['numeroCuentaBancaria'];
        $this->idProveedorOficina=$result['idProveedorOficina'];
        $this->idProveedorCuenta=$result['idProveedorCuenta'];
    }
    
    private function cargarEmbebidos()
    {
        $this->ProveedorOficina = new ProveedorOficina($this->adapter);
        $this->ProveedorOficina->consultarProveedorOficinaPorIdProveedorOficina($this->idProveedorOficina);
    }
}
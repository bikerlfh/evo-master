<?php
namespace Application\Model\Entity;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\TableIdentifier;
use Zend\Db\Sql\Expression;

class Proveedor extends AbstractTableGateway
{
    private $idProveedor;
    private $idDatoBasicoTercero;
    private $email;
    private $numCuentaBancaria;
    private $idTipoCuenta;
    private $idViaPago;
    private $idUsuarioCreacion;
    
    public function __construct(Adapter $adapter = null)
    {
        $this->adapter = $adapter;
        $this->table =  new \Zend\Db\Sql\TableIdentifier('Proveedor', 'Tercero');
    }

    public function getidUsuarioCreacion(){
        return $this->idUsuarioCreacion;
    }
    public function setidUsuarioCreacion($idUsuarioCreacion){
        $this->idUsuarioCreacion=$idUsuarioCreacion;
    }
    public function getidViaPago(){
        return $this->idViaPago;
    }
    public function setidViaPago($idViaPago){
        $this->idViaPago=$idViaPago;
    }
    public function getidTipoCuenta(){
        return $this->idTipoCuenta;
    }
    public function setidTipoCuenta($idTipoCuenta){
        $this->idTipoCuenta=$idTipoCuenta;
    }
    public function getNumCuentaBancaria(){
        return $this->numCuentaBancaria;
    }
    public function setNumCuentaBancaria($numCuentaBancaria){
        $this->numCuentaBancaria=$numCuentaBancaria;
    }
    public function getEmail(){
        return $this->email;
    }
    public function setEmail($email){
        $this->email=$email;
    }
    public function getIdDatoBasicoTercero(){
        return $this->idDatoBasicoTercero;
    }
    public function setIdDatoBasicoTercero($idDatoBasicoTercero){
        $this->idDatoBasicoTercero=$idDatoBasicoTercero;
    }
    public function getidProveedor(){
        return $this->idProveedor;
    }
    public function setidProveedor($idProveedor){
        $this->idProveedor=$idProveedor;
    }

    public function guardarProveedor($idDatoBasicoTercero,$email,$numCuentaBancaria,$idTipoCuenta,$idViaPago,$idUsuarioCreacion)
    {
        $datos=array(
                'idUsuarioCreacion'=> $idUsuarioCreacion,
                'idViaPago'=> $idViaPago,
                'idTipoCuenta'=> $idTipoCuenta,
                'numCuentaBancaria'=> $numCuentaBancaria,
                'email'=> $email,
                'idDatoBasicoTercero'=> $idDatoBasicoTercero
        );
        $result=$this->insert($datos);
        if($result>0)
            return true;
        return false;
    }

    public function modificarProveedor($idProveedor,$idDatoBasicoTercero,$email,$numCuentaBancaria,$idTipoCuenta,$idViaPago)
    {
        $datos=array(
                'idViaPago'=> $idViaPago,
                'idTipoCuenta'=> $idTipoCuenta,
                'numCuentaBancaria'=> $numCuentaBancaria,
                'email'=> $email,
                'idDatoBasicoTercero'=> $idDatoBasicoTercero
        );
        $result=$this->update($datos,array('idProveedor'=>$idProveedor));
        if($result>0)
            return true;
        return false;
    }
    public function eliminarProveedor($idProveedor)
    {
        if ($this->delete(array('idProveedor'=>$idProveedor))>0)
            return true;
        return false;
    }
    public function consultarTodoProveedor()
    {
        $sql = new Sql($this->adapter);
        $select = $sql->select()->
                from(array('p'=>  $this->table))->
                join(array("d"=> new TableIdentifier("DatoBasicoTercero","Tercero")),
                                    "p.idDatoBasicoTercero = d.idDatoBasicoTercero",
                                    array("descripcionTercero"=> new Expression("convert(varchar,d.nit) + ' - ' + d.descripcion")))->
                join(array("tc"=> new TableIdentifier("TipoCuenta","Compra")),
                                    "p.idTipoCuenta = tc.idTipoCuenta",
                                    array("descripcionTipoCuenta"=> new Expression("tc.codigo + ' - ' + tc.descripcion")))->
                join(array("v"=> new TableIdentifier("ViaPago","Compra")),
                                    "p.idViaPago = v.idViaPago",
                                    array("descripcionViaPago"=> new Expression("v.codigo + ' - ' + v.descripcion")));
        
        $results = $sql->prepareStatementForSqlObject($select)->execute();
        $resultsSet = new ResultSet();
        return $resultsSet->initialize($results)->toArray();
    }
    public function consultarProveedorPoridProveedor($idProveedor)
    {
        $result=$this->select(array('idProveedor'=>$idProveedor))->current();
        if($result)
        {
            $this->LlenarEntidad($result);
            return true;
        }
        return false;
    }
    public function consultarProveedorPoridDatoBasicoTercero($idDatoBasicoTercero)
    {
        $result=$this->select(array('iddatobasicotercero'=>$idDatoBasicoTercero))->current();
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
        $this->idViaPago=$result['idViaPago'];
        $this->idTipoCuenta=$result['idTipoCuenta'];
        $this->numCuentaBancaria=$result['numCuentaBancaria'];
        $this->email=$result['email'];
        $this->idDatoBasicoTercero=$result['idDatoBasicoTercero'];
        $this->idProveedor=$result['idProveedor'];
    }
}
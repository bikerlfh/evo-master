<?php
namespace Application\Model\Entity;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Adapter\Adapter;;
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
    public function getnumCuentaBancaria(){
        return $this->numCuentaBancaria;
    }
    public function setnumCuentaBancaria($numCuentaBancaria){
        $this->numCuentaBancaria=$numCuentaBancaria;
    }
    public function getemail(){
        return $this->email;
    }
    public function setemail($email){
        $this->email=$email;
    }
    public function getidDatoBasicoTercero(){
        return $this->idDatoBasicoTercero;
    }
    public function setidDatoBasicoTercero($idDatoBasicoTercero){
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

    public function modificarProveedor($idProveedor,$idDatoBasicoTercero,$email,$numCuentaBancaria,$idTipoCuenta,$idViaPago,$idUsuarioCreacion)
    {
        $datos=array(
                'idUsuarioCreacion'=> $idUsuarioCreacion,
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

    public function consultarTodoProveedor()
    {
        return $this->select()->toArray();
    }
    public function consultarProveedorPoridProveedor($idProveedor)
    {
        $result=$this->select(array('idproveedor'=>$idProveedor))->current();
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
<?php

namespace Application\Model\Entity;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\TableIdentifier;
use Zend\Db\Sql\Expression;
use Application\Model\Clases;

class DatoBasicoTercero extends AbstractTableGateway
{
    private $idDatoBasicoTercero;
    private $idTipoDocumento;
    private $nit;
    private $descripcion;
    private $primerNombre;
    private $segundoNombre;
    private $primerApellido;
    private $segundoApellido;
    private $direccion;
    private $telefono;
    
    public function __construct(Adapter $adapter = null)
    {
        $this->adapter = $adapter;
        $this->table =  new \Zend\Db\Sql\TableIdentifier('DatoBasicoTercero', 'Tercero');
    }

    public function gettelefono(){
        return $this->telefono;
    }
    public function settelefono($telefono){
        $this->telefono=$telefono;
    }
    public function getdireccion(){
        return $this->direccion;
    }
    public function setdireccion($direccion){
        $this->direccion=$direccion;
    }
    public function getsegundoApellido(){
        return $this->segundoApellido;
    }
    public function setsegundoApellido($segundoApellido){
        $this->segundoApellido=$segundoApellido;
    }
    public function getprimerApellido(){
        return $this->primerApellido;
    }
    public function setprimerApellido($primerApellido){
        $this->primerApellido=$primerApellido;
    }
    public function getsegundoNombre(){
        return $this->segundoNombre;
    }
    public function setsegundoNombre($segundoNombre){
        $this->segundoNombre=$segundoNombre;
    }
    public function getprimerNombre(){
        return $this->primerNombre;
    }
    public function setprimerNombre($primerNombre){
        $this->primerNombre=$primerNombre;
    }
    public function getdescripcion(){
        return $this->descripcion;
    }
    public function setdescripcion($descripcion){
        $this->descripcion=$descripcion;
    }
    public function getnit(){
        return $this->nit;
    }
    public function setnit($nit){
        $this->nit=$nit;
    }
    public function getidTipoDocumento(){
        return $this->idTipoDocumento;
    }
    public function setidTipoDocumento($idTipoDocumento){
        $this->idTipoDocumento=$idTipoDocumento;
    }
    public function getidDatoBasicoTercero(){
        return $this->idDatoBasicoTercero;
    }
    public function setidDatoBasicoTercero($idDatoBasicoTercero){
        $this->idDatoBasicoTercero=$idDatoBasicoTercero;
    }

    public function guardarDatobasicotercero($idTipoDocumento,$nit,$descripcion,$primerNombre,$segundoNombre,$primerApellido,$segundoApellido,$direccion,$telefono)
    {
        $datos=array(
                'telefono'=> $telefono,
                'direccion'=> $direccion,
                'segundoApellido'=> $segundoApellido,
                'primerApellido'=> $primerApellido,
                'segundoNombre'=> $segundoNombre,
                'primerNombre'=> $primerNombre,
                'descripcion'=> $descripcion,
                'nit'=> $nit,
                'idTipoDocumento'=> $idTipoDocumento
        );
        $result=$this->insert($datos);
        if($result>0)
            return true;
        return false;
    }

    public function modificarDatobasicotercero($idDatoBasicoTercero,$idTipoDocumento,$nit,$descripcion,$primerNombre,$segundoNombre,$primerApellido,$segundoApellido,$direccion,$telefono)
    {
        $datos=array(
                'telefono'=> $telefono,
                'direccion'=> $direccion,
                'segundoApellido'=> $segundoApellido,
                'primerApellido'=> $primerApellido,
                'segundoNombre'=> $segundoNombre,
                'primerNombre'=> $primerNombre,
                'descripcion'=> $descripcion,
                'nit'=> $nit,
                'idTipoDocumento'=> $idTipoDocumento
        );
        $result=$this->update($datos,array('idDatoBasicoTercero'=>$idDatoBasicoTercero));
        if($result>0)
            return true;
        return false;
    }

    public function eliminarDatoBasicoTercero($idDatoBasicoTercero)
    {
        if($this->delete(array('idDatoBasicoTercero'=>$idDatoBasicoTercero)))
            return true;
        return false;
    }
    
    public function consultarTodoDatobasicotercero()
    {
        $sql = new Sql($this->adapter);
        $select = $sql->select()->
                from(array('t'=>  $this->table))->
                join(array("td"=> new TableIdentifier("TipoDocumento","Tercero")),
                                    "t.idTipoDocumento = td.idTipoDocumento",
                                    array("descripcionTipoDocumento"=> new Expression("td.codigo + ' - ' + td.descripcion")));
        
        $results = $sql->prepareStatementForSqlObject($select)->execute();
        $resultsSet = new ResultSet();
        return $resultsSet->initialize($results)->toArray();
    }
    public function consultarDatoBasicoTerceroPoridDatoBasicoTercero($idDatoBasicoTercero)
    {
        $result=$this->select(array('idDatoBasicoTercero'=>$idDatoBasicoTercero))->current();
        if($result)
        {
            $this->LlenarEntidad($result);
            return true;
        }
        return false;
    }
    public function consultarDatoBasicoTerceroPornit($nit)
    {
        $result=$this->select(array('nit'=>$nit))->current();
        if($result)
        {
            $this->LlenarEntidad($result);
            return true;
        }
        return false;
    }
    
    public function consultaAvanzadaDatoBasicoTercero($nit, $descripcion)
    {
        $nit = $nit > 0? $nit:null;
        $stored = new Clases\StoredProcedure($this->adapter);
        return $stored->execProcedureReturnDatos("Tercero.ConsultaAvanzadaTercero ?,?",array($nit, $descripcion));
    }
    
     public function generarOptionsSelect($where = null)
    {
        $objs=$this->select($where)->toArray();
        $options=array(null,'');
        for($i=0;$i<count($objs);$i++)
        {
            $options[$objs[$i]['idDatoBasicoTercero']]=$objs[$i]['nit']." - ".$objs[$i]['descripcion'];
        }
        return $options;
    }
    
    private function LlenarEntidad($result)
    {
        $this->telefono=$result['telefono'];
        $this->direccion=$result['direccion'];
        $this->segundoApellido=$result['segundoApellido'];
        $this->primerApellido=$result['primerApellido'];
        $this->segundoNombre=$result['segundoNombre'];
        $this->primerNombre=$result['primerNombre'];
        $this->descripcion=$result['descripcion'];
        $this->nit=$result['nit'];
        $this->idTipoDocumento=$result['idTipoDocumento'];
        $this->idDatoBasicoTercero=$result['idDatoBasicoTercero'];
    }
}
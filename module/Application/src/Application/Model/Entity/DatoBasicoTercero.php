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
    private $nombre;
    private $apellido;
    private $direccion;
    private $telefono;
    
    public function __construct(Adapter $adapter = null)
    {
        $this->adapter = $adapter;
        $this->table =  new \Zend\Db\Sql\TableIdentifier('DatoBasicoTercero', 'Tercero');
    }

    public function getTelefono(){
        return $this->telefono;
    }
    public function setTelefono($telefono){
        $this->telefono=$telefono;
    }
    public function getDireccion(){
        return $this->direccion;
    }
    public function setDireccion($direccion){
        $this->direccion=$direccion;
    }
    public function getApellido(){
        return $this->apellido;
    }
    public function setApellido($apellido){
        $this->apellido=$apellido;
    }
    public function getNombre(){
        return $this->nombre;
    }
    public function setNombre($nombre){
        $this->nombre=$nombre;
    }
    public function getDescripcion(){
        return $this->descripcion;
    }
    public function setDescripcion($descripcion){
        $this->descripcion=$descripcion;
    }
    public function getNit(){
        return $this->nit;
    }
    public function setNit($nit){
        $this->nit=$nit;
    }
    public function getIdTipoDocumento(){
        return $this->idTipoDocumento;
    }
    public function setIdTipoDocumento($idTipoDocumento){
        $this->idTipoDocumento=$idTipoDocumento;
    }
    public function getIdDatoBasicoTercero(){
        return $this->idDatoBasicoTercero;
    }
    public function setIdDatoBasicoTercero($idDatoBasicoTercero){
        $this->idDatoBasicoTercero=$idDatoBasicoTercero;
    }

    public function guardarDatobasicotercero($idTipoDocumento,$nit,$descripcion,$nombre,$apellido,$direccion,$telefono)
    {
        $datos=array(
                'telefono'=> $telefono,
                'direccion'=> $direccion,
                'apellido'=> $apellido,
                'nombre'=> $nombre,
                'descripcion'=> $descripcion,
                'nit'=> $nit,
                'idTipoDocumento'=> $idTipoDocumento
        );
        $result=$this->insert($datos);
        if($result>0)
            return true;
        return false;
    }

    public function modificarDatobasicotercero($idDatoBasicoTercero,$idTipoDocumento,$nit,$descripcion,$nombre,$apellido,$direccion,$telefono)
    {
        $datos=array(
                'telefono'=> $telefono,
                'direccion'=> $direccion,
                'apellido'=> $apellido,
                'nombre'=> $nombre,
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
        $this->apellido=$result['apellido'];
        $this->nombre=$result['nombre'];
        $this->descripcion=$result['descripcion'];
        $this->nit=$result['nit'];
        $this->idTipoDocumento=$result['idTipoDocumento'];
        $this->idDatoBasicoTercero=$result['idDatoBasicoTercero'];
    }
}
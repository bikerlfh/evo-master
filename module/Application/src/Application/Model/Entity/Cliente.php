<?php
namespace Application\Model\Entity;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\TableIdentifier;
use Zend\Db\Sql\Expression;
use Application\Model\Clases;
use Application\Model\Entity;

class Cliente extends AbstractTableGateway
{
    private $idCliente;
    private $idDatoBasicoTercero;
    private $idMunicipio;
    private $email;
    private $direccion;
    private $telefono;
    
    public $DatoBasicoTercero;
    public $Municipio;
    
    public function __construct(Adapter $adapter = null)
    {
        $this->adapter = $adapter;
        $this->table =  new \Zend\Db\Sql\TableIdentifier('Cliente', 'Tercero');
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
    public function getEmail(){
        return $this->email;
    }
    public function setEmail($email){
        $this->email=$email;
    }
    public function getIdMunicipio(){
        return $this->idMunicipio;
    }
    public function setIdMunicipio($idMunicipio){
        $this->idMunicipio=$idMunicipio;
    }
    public function getIdDatoBasicoTercero(){
        return $this->idDatoBasicoTercero;
    }
    public function setIdDatoBasicoTercero($idDatoBasicoTercero){
        $this->idDatoBasicoTercero=$idDatoBasicoTercero;
    }
    public function getIdCliente(){
        return $this->idCliente;
    }
    public function setIdCliente($idCliente){
        $this->idCliente=$idCliente;
    }

    public function guardarCliente($idDatoBasicoTercero,$idMunicipio,$email,$direccion,$telefono)
    {
        $datos=array(
                'telefono'=> $telefono,
                'direccion'=> $direccion,
                'email'=> $email,
                'idMunicipio'=> $idMunicipio,
                'idDatoBasicoTercero'=> $idDatoBasicoTercero,
        );
        $result=$this->insert($datos);
        if($result>0)
            return true;
        return false;
    }

    public function modificarCliente($idCliente,$idDatoBasicoTercero,$idMunicipio,$email,$direccion,$telefono)
    {
        $datos=array(
                'telefono'=> $telefono,
                'direccion'=> $direccion,
                'email'=> $email,
                'idMunicipio'=> $idMunicipio,
                'idDatoBasicoTercero'=> $idDatoBasicoTercero
        );
        $result=$this->update($datos,array('idCliente'=>$idCliente));
        if($result>0)
            return true;
        return false;
    }
    
    public function eliminarCliente($idCliente)
    {
        if ($this->delete(array('idCliente'=>$idCliente))>0)
            return true;
        return false;
    }

    public function consultarTodoCliente()
    {
        $sql = new Sql($this->adapter);
        $select = $sql->select()->
                from(array('c'=>  $this->table))->
                join(array("d"=> new TableIdentifier("DatoBasicoTercero","Tercero")),
                                    "c.idDatoBasicoTercero = d.idDatoBasicoTercero",
                                    array("descripcionTercero"=> new Expression("convert(varchar,d.nit) + ' - ' + d.descripcion")))->
                join(array("m"=> new TableIdentifier("Municipio","Tercero")),
                                    "c.idMunicipio = m.idMunicipio",
                                    array("descripcionMunicipio"=> new Expression("m.codigo + ' - ' + m.descripcion")));
        
        $results = $sql->prepareStatementForSqlObject($select)->execute();
        $resultsSet = new ResultSet();
        return $resultsSet->initialize($results)->toArray();
    }
    public function consultarClientePorIdCliente($idCliente)
    {
        $result=$this->select(array('idCliente'=>$idCliente))->current();
        if($result)
        {
            $this->LlenarEntidad($result);
            return true;
        }
        return false;
    }
    public function consultarClientePorIdDatoBasicoTercero($idDatoBasicoTercero)
    {
        $result=$this->select(array('idDatobasicoTercero'=>$idDatoBasicoTercero))->current();
        if($result)
        {
            $this->LlenarEntidad($result);
            return true;
        }
        return false;
    }
    public function consultarClientePorIdMunicipio($idMunicipio)
    {
        $result=$this->select(array('idMunicipio'=>$idMunicipio))->current();
        if($result)
        {
            $this->LlenarEntidad($result);
            return true;
        }
        return false;
    }
    
     public function consultaAvanzadaCliente($nit, $descripcion)
    {
        $where = array();
        if ($nit>0) {
            array_push($where,'dbt.nit ='.$nit);
        }
        if (strlen($descripcion) >0) {
            $descripcion = strtoupper($descripcion);
            array_push($where,"UPPER(dbt.descripcion) like '%".$descripcion."%' OR UPPER(dbt.nombre) like '%".$descripcion."%' OR UPPER(dbt.apellido) like '%".$descripcion."%'");
        }
        $sql = new Sql($this->adapter);
        $select = $sql->select()->
                        from(array('cliente'=> $this->table))->
                        join(array('dbt'=>new TableIdentifier("DatoBasicoTercero", 'Tercero')),
                                   "dbt.idDatoBasicoTercero = cliente.idDatoBasicoTercero", 
                                   array('descripcionTercero'=> new Expression("CONVERT(VARCHAR,dbt.nit)+' - ' +dbt.descripcion")))->
                        join(array('m'=>new TableIdentifier("Municipio", 'Tercero')),
                                   "cliente.idMunicipio = m.idMunicipio", 
                                   array('descripcionMunicipio'=> new Expression("m.codigo+' - ' +m.descripcion")))->
                        where($where);
        $result = $sql->prepareStatementForSqlObject($select)->execute();
        $resultsSet = new ResultSet();
        return $resultsSet->initialize($result)->toArray();
    }
    
    private function LlenarEntidad($result)
    {
        $this->idCliente=$result['idCliente'];
        $this->idDatoBasicoTercero=$result['idDatoBasicoTercero'];
        $this->idMunicipio=$result['idMunicipio'];
        $this->email=$result['email'];
        $this->direccion=$result['direccion'];
        $this->telefono=$result['telefono'];
        $this->CargarEmbebidos();
    }
    //<===============================================================>
    //<--- Carga los objetos completos relacionados a este objeto ====>
    //<===============================================================>
    private function CargarEmbebidos()
    {
        $this->DatoBasicoTercero =new Entity\DatoBasicoTercero(parent::getAdapter());
        $this->DatoBasicoTercero->consultarDatoBasicoTerceroPoridDatoBasicoTercero($this->idDatoBasicoTercero);
        
        $this->Municipio =new Entity\Municipio(parent::getAdapter());
        $this->Municipio->consultarMunicipioPoridMunicipio($this->idMunicipio);
    }
}
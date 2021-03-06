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

class Proveedor extends AbstractTableGateway
{
    private $idProveedor;
    private $idDatoBasicoTercero;
    private $email;
    private $webSite;
    private $idUsuarioCreacion;
    
    
    public $DatoBasicoTercero;
    
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
    function getWebSite() {
        return $this->webSite;
    }

    function setWebSite($webSite) {
        $this->webSite = $webSite;
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
    
    public function guardarProveedor($idDatoBasicoTercero,$email,$webSite,$idUsuarioCreacion)
    {
        $datos=array(
                'idUsuarioCreacion'=> $idUsuarioCreacion,
                'webSite'=> $webSite,
                'email'=> $email,
                'idDatoBasicoTercero'=> $idDatoBasicoTercero
        );
        $result=$this->insert($datos);
        if($result>0)
            return true;
        return false;
    }

    public function modificarProveedor($idProveedor,$idDatoBasicoTercero,$email,$webSite)
    {
        $datos=array(
                'webSite'=> $webSite,
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
                                    array("descripcionTercero"=> new Expression("convert(varchar,d.nit) + ' - ' + d.descripcion")));
        
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
        $result=$this->select(array('idDatoBasicotercero'=>$idDatoBasicoTercero))->current();
        if($result)
        {
            $this->LlenarEntidad($result);
            return true;
        }
        return false;
    }
    
    public function consultaAvanzadaProveedor($nit, $descripcion)
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
                        from(array('proveedor'=> $this->table))->
                        join(array('dbt'=>new TableIdentifier("DatoBasicoTercero", 'Tercero')),
                                   "dbt.idDatoBasicoTercero = proveedor.idDatoBasicoTercero", 
                                   array('descripcionTercero'=> new Expression("CONVERT(VARCHAR,dbt.nit)+' - ' +dbt.descripcion")))->
                        where($where);
        $result = $sql->prepareStatementForSqlObject($select)->execute();
        $resultsSet = new ResultSet();
        return $resultsSet->initialize($result)->toArray();
        
    }
    public function generarOptionsSelect()
    {
        $objs=$this->consultarTodoProveedor();
        $options=array(null);
        for($i=0;$i<count($objs);$i++)
        {
            $options[$objs[$i]['idProveedor']]=$objs[$i]['descripcionTercero'];
        }
        return $options;
    }
    private function LlenarEntidad($result)
    {
        $this->idUsuarioCreacion=$result['idUsuarioCreacion'];
        $this->webSite=$result['webSite'];        
        $this->email=$result['email'];
        $this->idDatoBasicoTercero=$result['idDatoBasicoTercero'];
        $this->idProveedor=$result['idProveedor'];
        
        $this->CargarEmbebidos();
    }
    //<===============================================================>
    //<--- Carga los objetos completos relacionados a este objeto ====>
    //<===============================================================>
    private function CargarEmbebidos()
    {
        $this->DatoBasicoTercero =new Entity\DatoBasicoTercero(parent::getAdapter());
        $this->DatoBasicoTercero->consultarDatoBasicoTerceroPoridDatoBasicoTercero($this->idDatoBasicoTercero);
       
    }
}
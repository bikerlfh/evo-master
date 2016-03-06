<?php
namespace Application\Model\Entity;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\TableIdentifier;
use Zend\Db\Sql\Expression;


class Municipio extends AbstractTableGateway
{
    private $idMunicipio;
    private $idDepartamento;
    private $codigo;
    private $descripcion;
    
    public function __construct(Adapter $adapter = null)
    {
        $this->adapter = $adapter;
        $this->table =  new \Zend\Db\Sql\TableIdentifier('Municipio', 'Tercero');
    }

    public function getdescripcion(){
        return $this->descripcion;
    }
    public function setdescripcion($descripcion){
        $this->descripcion=$descripcion;
    }
    public function getcodigo(){
        return $this->codigo;
    }
    public function setcodigo($codigo){
        $this->codigo=$codigo;
    }
    public function getidDepartamento(){
        return $this->idDepartamento;
    }
    public function setidDepartamento($idDepartamento){
        $this->idDepartamento=$idDepartamento;
    }
    public function getidMunicipio(){
        return $this->idMunicipio;
    }
    public function setidMunicipio($idMunicipio){
        $this->idMunicipio=$idMunicipio;
    }

    public function guardarMunicipio($idDepartamento,$codigo,$descripcion)
    {
        $datos=array(
                'descripcion'=> $descripcion,
                'codigo'=> $codigo,
                'idDepartamento'=> $idDepartamento
        );
        $result=$this->insert($datos);
        if($result>0)
            return true;
        return false;
    }

    public function modificarMunicipio($idMunicipio,$idDepartamento,$codigo,$descripcion)
    {
        $datos=array(
                'descripcion'=> $descripcion,
                'codigo'=> $codigo,
                'idDepartamento'=> $idDepartamento
        );
        $result=$this->update($datos,array('idMunicipio'=>$idMunicipio));
        if($result>0)
            return true;
        return false;
    }
    public function eliminarMunicipio($idMunicipio)
    {
        if($this->delete(array('idMunicipio'=>$idMunicipio))> 0)
           return true;
        return false;
    }

    public function consultarTodoMunicipio()
    {
        $sql = new Sql($this->adapter);        
        $select = $sql->select()->
                         from(array('m'=> $this->table))->
                         join(array("d"=> new TableIdentifier("Departamento","Tercero")),
                                    "m.idDepartamento = d.idDepartamento",
                                    array("descripcionDepartamento"=> new Expression("d.codigo + ' - ' + d.descripcion")));
        
        $results = $sql->prepareStatementForSqlObject($select)->execute();
        $resultsSet = new ResultSet();
        return $resultsSet->initialize($results)->toArray();   
    }
    public function consultarMunicipioPoridMunicipio($idMunicipio)
    {
        $result=$this->select(array('idMunicipio'=>$idMunicipio))->current();
        if($result)
        {
            $this->LlenarEntidad($result);
            return true;
        }
        return false;
    }
    public function consultarMunicipioPoridDepartamento($idDepartamento)
    {
        $result=$this->select(array('idDepartamento'=>$idDepartamento))->current();
        if($result)
        {
            $this->LlenarEntidad($result);
            return true;
        }
        return false;
    }
    public function consultarMunicipioPorcodigo($codigo)
    {
        $result=$this->select(array('codigo'=>$codigo))->current();
        if($result)
        {
            $this->LlenarEntidad($result);
            return true;
        }
        return false;
    }
    public function generarOptionsSelect($where = null)
    {
        $objs=$this->select($where)->toArray();
        $options=array(null);
        for($i=0;$i<count($objs);$i++)
        {
            $options[$objs[$i]['idMunicipio']]=$objs[$i]['codigo']." - ".$objs[$i]['descripcion'];
        }
        return $options;
    }
    private function LlenarEntidad($result)
    {
        $this->idMunicipio=$result['idMunicipio'];
        $this->idDepartamento=$result['idDepartamento'];
        $this->codigo=$result['codigo'];
        $this->descripcion=$result['descripcion'];
    }
}
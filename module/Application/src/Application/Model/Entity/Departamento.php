<?php
namespace Application\Model\Entity;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Select;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\TableIdentifier;
use Zend\Db\Sql\Expression;

class Departamento extends AbstractTableGateway
{
    private $descripcion;
    private $codigo;
    private $idPais;
    private $idDepartamento;
    public function __construct(Adapter $adapter = null)
    {
        $this->adapter = $adapter;
        $this->table =  new TableIdentifier('Departamento', 'Tercero');
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
    public function getidPais(){
        return $this->idPais;
    }
    public function setidPais($idPais){
        $this->idPais=$idPais;
    }
    public function getidDepartamento(){
        return $this->idDepartamento;
    }
    public function setidDepartamento($idDepartamento){
        $this->idDepartamento=$idDepartamento;
    }

    public function guardarDepartamento($idPais,$codigo,$descripcion)
    {
        $datos=array(
                'descripcion'=> $descripcion,
                'codigo'=> $codigo,
                'idPais'=> $idPais
        );
        $result=$this->insert($datos);
        if($result>0)
            return true;
        return false;
    }

    public function modificarDepartamento($idDepartamento,$idPais,$codigo,$descripcion)
    {
        $datos=array(
                'descripcion'=> $descripcion,
                'codigo'=> $codigo,
                'idPais'=> $idPais
        );
        $result=$this->update($datos,array('idDepartamento'=>$idDepartamento));
        if($result>0)
            return true;
        return false;
    }
    public function eliminarDepartamento($idDepartamento)
    {
        if ($this->delete(array('idDepartamento'=>$idDepartamento))>0) 
            return true;
        return false;
    }
    public function consultarTodoDepartamento()
    {
        try
        {
        //return $this->select()->toArray();
        
        $sql = new Sql($this->adapter,array('d'=>$this->table));

        $select = $sql->select();
        // join($table,ON,arrayCampos('alias'=>'nombreCampo'))
        // en el arreglo campos se pueden usar Expresiones para realizar concatenaciones,count etc.
        /*
        $select = $sql->select()->
                  join(array('p'=> new TableIdentifier("Pais", "Tercero")),
                            'p.idPais=d.idPais',
                            array('codigoPais'=>'codigo','descripcionPais'=>'codigo+'-'+descripcion'));*/
        
        $select = $sql->select()->
                        join(array('p'=> new TableIdentifier("Pais", "Tercero")),
                                   'p.idPais=d.idPais', 
                                    array('descripcionPais' => new Expression("p.codigo+' - '+p.descripcion")));
     

        $results = $sql->prepareStatementForSqlObject($select)->execute();
        $resultsSet = new ResultSet();
        return $resultsSet->initialize($results)->toArray();
        }
        catch (Exception $e)
        {
            var_dump($e->getPrevious());
        }
    }
    public function consultarDepartamentoPoridDepartamento($idDepartamento)
    {
        $result=$this->select(array('idDepartamento'=>$idDepartamento))->current();
        if($result)
        {
            $this->LlenarEntidad($result);           
            return true;
        }
        return false;
    }
    public function consultarDepartamentoPoridPais($idPais)
    {
        $result=$this->select(array('idPais'=>$idPais))->current();
        if($result)
        {
            $this->LlenarEntidad($result);       
            return true;
        }
        return false;
    }
    public function consultarDepartamentoPorcodigo($codigo)
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
        $options=array(null,'');
        for($i=0;$i<count($objs);$i++)
        {
            $options[$objs[$i]['idDepartamento']]=$objs[$i]['codigo']." - ".$objs[$i]['descripcion'];
        }
        return $options;
    }
    
    private function LlenarEntidad($result)
    {
        $this->idDepartamento=$result['idDepartamento'];
        $this->idPais=$result['idPais'];
        $this->codigo=$result['codigo'];
        $this->descripcion=$result['descripcion'];
    }
}
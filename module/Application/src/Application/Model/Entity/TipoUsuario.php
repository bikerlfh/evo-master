<?php
namespace Application\Model\Entity;
use Zend\Db\TableGateway\AbstractTableGateway ;
use Zend\Db\Adapter\Adapter;

class TipoUsuario extends AbstractTableGateway 
{        
    private $idTipoUsuario;
    private $codigo;
    private $descripcion;
    
    public function __construct(Adapter $adapter = null)
    {
        //$this->StoredProcedure=new Entity\StoredProcedure('equipo',$adapter);
        $this->adapter = $adapter;
        $this->table =  new \Zend\Db\Sql\TableIdentifier('TipoUsuario', 'Seguridad');
    }
            
    function getIdTipoUsuario() { return $this->idTipoUsuario;}

    function getCodigo() { return $this->codigo; }

    function getDescripcion() { return $this->descripcion; }

    function setIdTipoUsuario($idTipoUsuario) { $this->idTipoUsuario = $idTipoUsuario; }

    function setCodigo($codigo) { $this->codigo = $codigo; }

    function setDescripcion($descripcion) { $this->descripcion = $descripcion; }
    
    public function guardarTipoUsuario($codigo,$descripcion)
    {
        $datos=array(
                'codigo'=> $codigo,
                'descripcion'=> $descripcion);
        $result=$this->insert($datos);
        if ($result > 0) {
            return true;
        }
        return false;
    }
    
    public function modificarTipoUsuario($idTipoUsuario,$codigo,$descripcion)
    {
        $datos=array('codigo'=> $codigo,'descripcion'=> $descripcion);
        $result=$this->update($datos,array('idTipoUsuario'=>$idTipoUsuario));
        if ($result > 0) {
            return true;
        }
        return false;
    }
    public function eliminarTipoUsuario($idTipoUsuario)
    {
        $result=$this->delete(array('idTipoUsuario'=>$idTipoUsuario));
        if ($result > 0) {
            return true;
        }
        return false;
    }
    
    public function consultarTodoTipoUsuario()
    {
        return $this->select()->toArray();
    }
    public function consultarTipoUsuarioPorIdTipoUsuario($idTipoUsuario)
    {
        $result=$this->select(array('idTipoUsuario'=>$idTipoUsuario))->current();
        if($result)
        {
            $this->idTipoUsuario=$result['idTipoUsuario'];
            $this->codigo=$result['codigo'];
            $this->descripcion=$result['descripcion'];
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
            $options[$objs[$i]['idTipoUsuario']]=$objs[$i]['codigo']." - ".$objs[$i]['descripcion'];
        }
        return $options;
    }
    
}
     

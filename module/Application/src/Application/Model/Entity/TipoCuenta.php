<?php
namespace Application\Model\Entity;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Adapter\Adapter;

class TipoCuenta extends AbstractTableGateway
{
    private $idTipoCuenta;
    private $codigo;
    private $descripcion;
    
    public function __construct(Adapter $adapter = null)
    {
        $this->adapter = $adapter;
        $this->table =  new \Zend\Db\Sql\TableIdentifier('TipoCuenta', 'Compra');
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
    public function getidTipoCuenta(){
        return $this->idTipoCuenta;
    }
    public function setidTipoCuenta($idTipoCuenta){
        $this->idTipoCuenta=$idTipoCuenta;
    }

    public function guardarTipocuenta($codigo,$descripcion)
    {
        $datos=array(
                'descripcion'=> $descripcion,
                'codigo'=> $codigo
        );
        $result=$this->insert($datos);
        if($result>0)
            return true;
        return false;
    }

    public function modificarTipocuenta($idTipoCuenta,$codigo,$descripcion)
    {
        $datos=array(
                'descripcion'=> $descripcion,
                'codigo'=> $codigo
        );
        $result=$this->update($datos,array('idTipoCuenta'=>$idTipoCuenta));
        if($result>0)
            return true;
        return false;
    }
    public function eliminarTipoCuenta($idTipoCuenta)
    {
        if ($this->delete(array('idTipoCuenta'=>$idTipoCuenta)) > 0)
            return true;
        return false;
    }
    public function consultarTodoTipocuenta()
    {
        return $this->select()->toArray();
    }
    public function consultarTipoCuentaPorIdTipoCuenta($idTipoCuenta)
    {
        $result=$this->select(array('idtipocuenta'=>$idTipoCuenta))->current();
        if($result)
        {
            $this->idTipoCuenta=$result['idTipoCuenta'];
            $this->codigo=$result['codigo'];
            $this->descripcion=$result['descripcion'];
            return true;
        }
        return false;
    }
    
    public function consultarTipocuentaPorcodigo($codigo)
    {
        $result=$this->select(array('codigo'=>$codigo))->current();
        if($result)
        {
            $this->idTipoCuenta=$result['idTipoCuenta'];
            $this->codigo=$result['codigo'];
            $this->descripcion=$result['descripcion'];
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
            $options[$objs[$i]['idTipoCuenta']]=$objs[$i]['codigo']." - ".$objs[$i]['descripcion'];
        }
        return $options;
    }
}
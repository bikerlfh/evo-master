<?php
namespace Application\Model\Entity;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Adapter\Adapter;;
class TipoDocumento extends AbstractTableGateway
{
    private $idTipoDocumento;
    private $codigo;
    private $descripcion;
    
    public function __construct(Adapter $adapter = null)
    {
        $this->adapter = $adapter;
        $this->table =  new \Zend\Db\Sql\TableIdentifier('TipoDocumento', 'Tercero');
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
    public function getidTipoDocumento(){
        return $this->idTipoDocumento;
    }
    public function setidTipoDocumento($idTipoDocumento){
        $this->idTipoDocumento=$idTipoDocumento;
    }

    public function guardarTipodocumento($codigo,$descripcion)
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

    public function modificarTipodocumento($idTipoDocumento,$codigo,$descripcion)
    {
        $datos=array(
                'descripcion'=> $descripcion,
                'codigo'=> $codigo
        );
        $result=$this->update($datos,array('idTipoDocumento'=>$idTipoDocumento));
        if($result>0)
            return true;
        return false;
    }

    public function consultarTodoTipodocumento()
    {
        return $this->select()->toArray();
    }
    public function consultarTipodocumentoPoridTipoDocumento($idTipoDocumento)
    {
        $result=$this->select(array('idtipodocumento'=>$idTipoDocumento))->current();
        if($result)
        {
            $this->idTipoDocumento=$result['idtipodocumento'];
            $this->codigo=$result['codigo'];
            $this->descripcion=$result['descripcion'];
            return true;
        }
        return false;
    }
    public function getTipodocumentoPorcodigo($codigo)
    {
        $result=$this->select(array('codigo'=>$codigo))->current();
        if($result)
        {
            $this->idTipoDocumento=$result['idtipodocumento'];
            $this->codigo=$result['codigo'];
            $this->descripcion=$result['descripcion'];
            return true;
        }
        return false;
    }
}
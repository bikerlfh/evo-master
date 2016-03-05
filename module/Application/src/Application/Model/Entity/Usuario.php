<?php
namespace Application\Model\Entity;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\TableIdentifier;
use Zend\Db\Sql\Expression;


class Usuario extends AbstractTableGateway
{
    private $idUsuario;
    private $idTipoUsuario;
    Private $idDatoBasicoTercero;
    private $clave;
    private $email;
    // Objetos Embebidos
    public $TipoUsuario;
    public $DatoBasicoTercero;

    public function __construct(Adapter $adapter = null)
    {
        $this->adapter = $adapter;
        $this->table =  new \Zend\Db\Sql\TableIdentifier('Usuario', 'Seguridad');
    }

    public function getclave(){
        return $this->clave;
    }
    public function setclave($clave){
        $this->clave=$clave;
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
    public function getidTipoUsuario(){
        return $this->idTipoUsuario;
    }
    public function setidTipoUsuario($idTipoUsuario){
        $this->idTipoUsuario=$idTipoUsuario;
    }
    public function getidUsuario(){
        return $this->idUsuario;
    }
    public function setidUsuario($idUsuario){
        $this->idUsuario=$idUsuario;
    }

    public function guardarUsuario($clave,$email,$idDatoBasicoTercero,$idTipoUsuario)
    {
        $datos=array(
                'clave'=> md5($clave),
                'email'=> $email,
                'idDatoBasicoTercero'=> $idDatoBasicoTercero,
                'idTipoUsuario'=> $idTipoUsuario,
        );
        $result=$this->insert($datos);
        if($result>0)
            return true;
        return false;
    }

    public function modificarUsuario($idUsuario,$email,$idDatoBasicoTercero,$idTipoUsuario,$clave = null)
    {
        $datos=array(
                'email'=> $email,
                'idDatoBasicoTercero'=> $idDatoBasicoTercero,
                'idTipoUsuario'=> $idTipoUsuario,
        );
        if ($clave!=null) {
            $datos["clave"] = md5($clave);
        }
        
        $result=$this->update($datos,array('idUsuario'=>$idUsuario));
        if($result>0)
            return true;
        return false;
    }
    public function eliminarUsuario($idUsuario)
    {
        if($this->delete(array("idUsuario"=> $idUsuario)) > 0)
            return true;
        return false;
    }

    public function consultarTodoUsuario()
    {
         $sql = new Sql($this->adapter);        
        $select = $sql->select()->
                         from(array('u'=> $this->table))->
                         join(array("tu"=> new TableIdentifier("TipoUsuario","Seguridad")),
                                    "u.idTipoUsuario = tu.idTipoUsuario",
                                    array("descripcionTipoUsuario"=> new Expression("tu.codigo + ' - ' + tu.descripcion")))->
                         join(array("dbt"=> new TableIdentifier("DatoBasicoTercero","Tercero")),
                                 "dbt.idDatoBasicoTercero = u.idDatoBasicoTercero",
                                 array("descripcionTercero"=> new Expression("Convert(varchar(20),dbt.nit) + ' - ' + dbt.descripcion")));
        
        $results = $sql->prepareStatementForSqlObject($select)->execute();
        $resultsSet = new ResultSet();
        return $resultsSet->initialize($results)->toArray();   
    }
    
    public function consultarUsuarioPoridUsuario($idUsuario)
    {
        $result=$this->select(array('idusuario'=>$idUsuario))->current();
        if($result)
        {
            $this->clave=$result['clave'];
            $this->email=$result['email'];
            $this->idDatoBasicoTercero=$result['idDatoBasicoTercero'];
            $this->idTipoUsuario=$result['idTipoUsuario'];
            $this->idUsuario=$result['idUsuario'];
            return true;
        }
        return false;
    }
    
    public function logIn($email,$pass)
    {
        $result=$this->select(array('email'=>$email,'clave'=>md5($pass)))->current();
        if($result)
        {
            $this->LlenarEntidad($result);
            $this->cargarEmbebidos();
            return true;
        }
        return false;
    }
    private function cargarEmbebidos()
    {
        $this->TipoUsuario = new TipoUsuario(parent::getAdapter());
        $this->TipoUsuario->consultarTipoUsuarioPorIdTipoUsuario($this->idTipoUsuario);
        
        $this->DatoBasicoTercero = new DatoBasicoTercero(parent::getAdapter());
        $this->DatoBasicoTercero->consultarDatoBasicoTerceroPoridDatoBasicoTercero($this->idDatoBasicoTercero);
    }
    private function LlenarEntidad($result)
    {
        $this->clave=$result['clave'];
        $this->email=$result['email'];
        $this->idDatoBasicoTercero=$result['idDatoBasicoTercero'];
        $this->idTipoUsuario=$result['idTipoUsuario'];
        $this->idUsuario=$result['idUsuario'];
    }
}
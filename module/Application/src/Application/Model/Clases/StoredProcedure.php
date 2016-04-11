<?php
/**************************************************************
 * Fecha Creación:      01/01/2013 
 * Descripción:         Clase para manejar procedimientos almacenados.
 * Creado por :         Luis Fernando Henriquez Arciniegas
 * Fecha Modificacion:  09/09/2014
 * Motivo Descripción:  Optimización Metodos..
 **************************************************************/
namespace Application\Model\Clases;

//use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\ParameterContainer as ParameterContainer; 

class StoredProcedure 
{
    private $adapter;
    public function __construct(Adapter $adapter = null)
    {    	
        $this->adapter=$adapter;
        //return parent::__construct($table, $adapter, $databaseSchema,$selectResultPrototype);		
    }
    //Metodo que ejecuta un procedimiento almacenado que tenga o no parametros
    //Modo de implementacion $StoredProcedure->execProcedureReturnDatos('insertUsuario ?,?',array('Pepito','Perez'));
    //                       $StoredProcedure->execProcedureReturnDatos('consultarTodosUsuario',null);   
    public function execProcedureReturnDatos($nameProcedure,$parameters=array())
    {
        $stmt = $this->adapter->createStatement()->setSql('exec '.$nameProcedure);
        if (count($parameters)>0) 
        {
             $stmt->setParameterContainer(new ParameterContainer($parameters)); 
        }       
        return $stmt->execute();
    }
    public function ejecutarSelect($select)
    {
        $stmt = $this->adapter->createStatement()->setSql($select);   
        return $stmt->execute();
    }
    //Metodo que devuelve las filas affectadas
    public function execProcedureReturnAffectedRows($nameProcedure,$parameters=array())
    {
        $result=$this->execProcedureParametersReturnDatos($nameProcedure,$parameters);
        if($result!=null) {
            return $result->getAffectedRows();
        }
    }
}
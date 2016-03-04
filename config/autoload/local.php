<?php
/**
 * Local Configuration Override
 *
 * This configuration override file is for overriding environment-specific and
 * security-sensitive configuration information. Copy this file without the
 * .dist extension at the end and populate values as needed.
 *
 * @NOTE: This file is ignored from Git by default with the .gitignore included
 * in ZendSkeletonApplication. This is a good practice, as it prevents sensitive
 * credentials from accidentally being committed into version control.
 */
return array(
    'service_manager'=>array(
        'factories'=>array(
            'Zend\Db\Adapter'=>'Zend\Db\Adapter\AdapterServiceFactory',
        ),
    ),
   'db'=> array(
    'driver'    => 'pdo',
    'dsn'       => 'sqlsrv:database=evo;Server=localhost',
    'username'  => 'sa',
    'password'  => '123'
    ),
    //<== clases css Formularios ==>
    'cssClass' => array(
        'text'=>'form-control',
        'select'=>'form-control',
        'date'=>'form-control',
        'radio'=>'',
        'btnGuardar'=>'btn btn-primary',
        'btnModificar'=>'btn btn-success',
        'btnEliminar'=>'btn btn-danger',
        'btnCancelar'=>'btn btn-default',
        'btnSeleccionar'=>'btn btn-success'
    ),
    'MsgCrud'=> array(
        'okSave'=> array('title'=>'Operación Exitosa','message'=>'Se ha guardado con Exito','function'=>'showMessageSuccess'),
        'errorSave'=>array('title'=>'Operación sin resultado','message'=>'No se logro guardar','function'=>'showMessageError'),
        'okUpdate'=>array('title'=>'Operación Exitosa','message'=>'se ha Modificación con Exito','function'=>'showMessageSuccess'),
        'errorUpdate'=>array('title'=>'Operación sin resultado','message'=>'No se logro Modificar','function'=>'showMessageError'),
        'okDelete'=>array('title'=>'Operación Exitosa','message'=>'Se ha eliminado exitosamente','function'=>'showMessageSuccess'),
        'errorDelete'=>array('title'=>'Operación sin resultado','message'=>'No se logro Eliminar','function'=>'showMessageError'),
        'updateDisable'=>array('title'=>'Districell','message'=>'Este módulo no esta habilitado para modificar','function'=>'showMessaginfo'),
        
    ),
    );

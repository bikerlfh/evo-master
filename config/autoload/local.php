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
    /*
    'db'=> array(
    'driver'    => 'pdo',
    'dsn'       => 'sqlsrv:database=DB_9EB44A_evo;Server=SQL5024.Smarterasp.net',
    'username'  => 'DB_9EB44A_evo_admin',
    'password'  => 'laurame123'
    ),*/
    // <== Formatos de fechas, etc ==>
    'formats'=>array(
        'datetime'=>'d-m-Y H:m:i', //Y-m-d H:m:i
        'date'=>'d-m-Y',// Y-m-d
    ),
    //<== clases css Formularios ==>
    'cssClass' => array(
        'text'=>'form-control',
        'select'=>'form-control',
        'date'=>'form-control',
        'spinner'=>'form-control ui-spinner-input',
        'radio'=>'',
        'btnGuardar'=>'btn btn-primary',
        'btnModificar'=>'btn btn-success',
        'btnEliminar'=>'btn btn-danger',
        'btnCancelar'=>'btn btn-default',
        'btnSeleccionar'=>'btn btn-success',
        'btnBuscar'=>'btn btn-support2'
    ),
    'MsgCrud'=> array(
        'okSave'=> array('title'=>'Operación Exitosa','message'=>'Se ha guardado con Exito','function'=>'showMessageSuccess'),
        'errorSave'=>array('title'=>'Operación sin resultado','message'=>'No se logro guardar','function'=>'showMessageError'),
        'okUpdate'=>array('title'=>'Operación Exitosa','message'=>'se ha Modificación con Exito','function'=>'showMessageSuccess'),
        'errorUpdate'=>array('title'=>'Operación sin resultado','message'=>'No se logro Modificar','function'=>'showMessageError'),
        'okDelete'=>array('title'=>'Operación Exitosa','message'=>'Se ha eliminado exitosamente','function'=>'showMessageSuccess'),
        'errorDelete'=>array('title'=>'Operación sin resultado','message'=>'No se logro Eliminar','function'=>'showMessageError'),
        'updateDisable'=>array('title'=>'Información','message'=>'Este módulo no esta habilitado para modificar','function'=>'showMessaginfo'),
        'errorDesconocido'=>array('title'=>'Erro desconocido','message'=>'Ha ocurrido un error desconocido','function'=>'showMessageWarning'),
        'okAutorizacion'=> array('title'=>'Operación Exitosa','message'=>'Se ha autorizado el pedido con Exito','function'=>'showMessageSuccess'),
        'errorAutorizacion'=>array('title'=>'Operación sin resultado','message'=>'No se logro autorizar el pedido','function'=>'showMessageError'),
    ),
    'MsgCliente'=> array(
        'okRegistroUsuario'=>array('title'=>'Registro Usuario','message'=>'Se registrado Exitosamente..','function'=>'showMessageSuccess'),
        ''=>array('title'=>'','message'=>'','function'=>''),
        ''=>array('title'=>'','message'=>'','function'=>''),
        ''=>array('title'=>'','message'=>'','function'=>''),
        ''=>array('title'=>'','message'=>'','function'=>''),
    ),
    
    'session' => array(
        'config' => array(
            'class' => 'Zend\Session\Config\SessionConfig',
            'options' => array(
                'remember_me_seconds' => 3600,
                'name' => 'evo-master',
            ),
        ),
        'storage' => 'Zend\Session\Storage\SessionArrayStorage',
        'validators' => array(
            'Zend\Session\Validator\RemoteAddr',
            'Zend\Session\Validator\HttpUserAgent',
        ),
    ),
);

<?php
/*
* @autor Luis Fernando Henriquez
* @copyrigth 2016
*/

namespace Application\Form;
use Zend\Form\Form;
use Zend\Form\Element;
use Admin\Form\FormDatoBasicoTercero;
use Admin\Form\FormUsuario;
use Application\Model\Entity\Pais;

class FormRegistro extends Form
{
    private $cssClass;
    private $basePath;
    private $adapter;
    private $formDatoBasicoTercero;
    private $formUsuario;
   
    
    public function __construct($serviceLocator,$basePath = null)
    {
        parent::__construct("frmregistro");
        $this->basePath = $basePath;
        $this->adapter=$serviceLocator->get('Zend\Db\Adapter');
        $this->setAttributes(array(
            'action' => $this->basePath.'/application/index/register',
            'method' => 'post',
            'class'=>'form-horizontal',
            'role'=>'form'
        ));
        $this->formDatoBasicoTercero = new FormDatoBasicoTercero($serviceLocator,$basePath);
        $this->formUsuario = new FormUsuario($serviceLocator,$basePath);
        $this->cssClass = $serviceLocator->get('Config');
        $this->cssClass = $this->cssClass['cssClass'];
        $this->generarCampos();    
    }
    private function generarCampos()
    {
        /************* select idPais ***********/
        $pais= new Pais($this->adapter);
        $select = new Element\Select('idPais');
        $select->setValueOptions($pais->generarOptionsSelect());
        $select->setAttributes(array('id' => 'idPais',
                                     'required' => "required"));
        $this->add($select);
        /************* select idPais ***********/
        
        $this->add($this->formDatoBasicoTercero->get('idTipoDocumento')->setAttribute('class', 'sbSelector'));       
        $this->add($this->formDatoBasicoTercero->get('nit')->setAttribute('class', 'form-control input-lg'));        
        $this->add($this->formDatoBasicoTercero->get('nombre')->setAttribute('class', 'form-control input-lg'));
        $this->add($this->formDatoBasicoTercero->get('apellido')->setAttribute('class', 'form-control input-lg'));
        $this->add($this->formDatoBasicoTercero->get('direccion')->setAttribute('class', 'form-control input-lg'));
        $this->add($this->formDatoBasicoTercero->get('telefono')->setAttribute('class', 'form-control input-lg'));
        
        $this->add($this->formUsuario->get('email')->setAttribute('class', 'form-control input-lg'));
        $this->add(array(
            'name' => 'clave1',                       
            'attributes' => array(
                'id'=>'clave1', 
                'type' => 'password',
                'placeholder'=>'Clave',
                'required'=>true,
                'maxlength'=>'32',
                'class' => 'form-control input-lg'
            ),
        ));
        $this->add(array(
            'name' => 'clave2',                       
            'attributes' => array(
                'id'=>'clave2', 
                'type' => 'password',
                'placeholder'=>'Clave',
                'required'=>true,
                'maxlength'=>'32',
                'class' => 'form-control input-lg'
            ),
        ));
        
        $this->add(array(
                'name'=>'btnGuardar',			
                'attributes'=>array(
                        'id'=>'btnGuardar',
                        'type'=>'submit',
                        'value'=>'CREAR MI CUENTA',
                        'title'=>'CREAR MI CUENTA',
                        'class'=>'btn btn-custom-2 md-margin'
                )
        ));
       
        $this->add(array(
                'name'=>'btnCancelar',			
                'attributes'=>array(
                        'id'=>'btnCancelar',
                        'type'=>'button',
                        'value'=>'Cancelar',
                        'title'=>'Cancelar',
                        'onClick'=>"limpiarformulario(".$this->getAttribute("name").");",
                        'style'=>'margin:2px',
                        'class'=>$this->cssClass['btnCancelar']
                )
        )); 
    }
}
?>

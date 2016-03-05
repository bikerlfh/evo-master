<?php
/*
* @autor Luis Fernando Henriquez
* @copyrigth 2016
*/

namespace Admin\Form;
use Zend\Form\Form;
use Zend\Form\Element;
use Application\Model\Entity\TipoUsuario;
use Application\Model\Entity\DatoBasicoTercero;

class FormUsuario extends Form
{
    
    private $cssClass;
    private $basePath;
    public function __construct($serviceLocator,$basePath = null)
    {
        parent::__construct("frmusuario");
        $this->adapter=$serviceLocator->get('Zend\Db\Adapter');
        $this->basePath = $basePath;
        $this->setAttributes(array(
            'action' => $this->basePath.'/admin/usuario/index',
            'method' => 'post',
            'class'=>'form-horizontal',
            'role'=>'form'
        ));
        $this->cssClass = $serviceLocator->get('Config');
        $this->cssClass = $this->cssClass['cssClass'];
        $this->generarCampos();    
    }
    private function generarCampos()
    {
        $this->add(array(
            'name' => 'idUsuario',                       
            'attributes' => array(
                'id'=>'idUsuario', 
                'type' => 'hidden',
            ),
        ));
        
        /************* select tipoUsuario ***********/
        $tipoUsuario= new TipoUsuario($this->adapter);
        $select = new Element\Select('idTipoUsuario');
        $select->setValueOptions($tipoUsuario->generarOptionsSelect());
        $select->setAttributes(array('id' => 'idTipoUsuario',
                                     'class' => $this->cssClass['select'],
                                     'required' => true));
        $this->add($select);
        /************* select tipoUsuario ***********/ 
        
        /************* select datobasicoTercero ***********/
        $datoBasicoTercero= new DatoBasicoTercero($this->adapter);
        $select1 = new Element\Select('idDatoBasicoTercero');
        $select1->setValueOptions($datoBasicoTercero->generarOptionsSelect());
        $select1->setAttributes(array('id' => 'idDatoBasicoTercero',
                                     'class' => $this->cssClass['select'],
                                     'required' => true));
        $this->add($select1);
        /************* select datobasicoTercero ***********/ 
        
        
        $this->add(array(
            'name' => 'email',                       
            'attributes' => array(
                'id'=>'email', 
                'type' => 'text',
                'placeholder'=>'E-mail',
                'required'=>true,
                'maxlength'=>'150',
                'class' => $this->cssClass['text']
            ),
        ));
        $this->add(array(
            'name' => 'clave',                       
            'attributes' => array(
                'id'=>'clave', 
                'type' => 'password',
                'placeholder'=>'Clave',
                'required'=>true,
                'maxlength'=>'32',
                'class' => $this->cssClass['text']
            ),
        ));
        
        $this->add(array(
                'name'=>'btnGuardar',			
                'attributes'=>array(
                        'id'=>'btnGuardar',
                        'type'=>'submit',
                        'value'=>'Guardar',
                        'title'=>'Guardar',
                        'class'=>$this->cssClass['btnGuardar']
                )
        ));
        $this->add(array(
                'name'=>'btnModificar',			
                'attributes'=>array(
                        'id'=>'btnModificar',
                        'type'=>'submit',
                        'value'=>'Modificar',
                        'title'=>'Modificar',
                        'style'=>'margin:2px',
                        'class'=>$this->cssClass['btnModificar']
                )
        ));
        $this->add(array(
                'name'=>'btnEliminar',			
                'attributes'=>array(
                        'id'=>'btnEliminar',
                        'type'=>'button',
                        'value'=>'Eliminar',
                        'title'=>'Eliminar',
                        'onClick'=>"$(location).attr('href','".$this->basePath."/admin/usuario/eliminar?id='+$('#idUsuario').val());",
                        'style'=>'margin:2px',
                        'class'=>$this->cssClass['btnEliminar']
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

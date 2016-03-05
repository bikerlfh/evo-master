<?php
/*
* @autor Luis Fernando Henriquez
* @copyrigth 2016
*/

namespace Admin\Form;
use Zend\Form\Form;
use Zend\Form\Element;
use Application\Model\Entity\TipoDocumento;

class FormDatoBasicoTercero extends Form
{
    private $adapter;
    private $cssClass;
    private $basePath;
    public function __construct($serviceLocator,$basePath = null)
    {
        parent::__construct("frmdatobasicotercero");
        $this->adapter=$serviceLocator->get('Zend\Db\Adapter');
        $this->basePath = $basePath;
        $this->setAttributes(array(
            'action' => $this->basePath.'/admin/datobasicotercero/index',
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
            'name' => 'idDatoBasicoTercero',                       
            'attributes' => array(
                'id'=>'idDatoBasicoTercero', 
                'type' => 'hidden',
            ),
        ));
        /************* select idTipoDocumento ***********/
        $tipoDocumento= new TipoDocumento($this->adapter);
        $select = new Element\Select('idTipoDocumento');
        $select->setValueOptions($tipoDocumento->generarOptionsSelect());
        $select->setAttributes(array('id' => 'idTipoDocumento',
                                     'class' => $this->cssClass['select'],
                                     'required' => "required"));
        $this->add($select);
        /************* select idPais ***********/
       
        
        $this->add(array(
            'name' => 'nit',                       
            'attributes' => array(
                'id'=>'nit', 
                'type' => 'text',
                'placeholder'=>'Nif',
                'required'=>'required',
                'maxlength'=>'10',
                'class' => $this->cssClass['text']
            ),
        ));
        $this->add(array(
            'name' => 'descripcion',                       
            'attributes' => array(
                'id'=>'descripcion', 
                'type' => 'text',
                'placeholder'=>'Descripción',
                'required'=>'required',
                'maxlength'=>'150',
                'class' => $this->cssClass['text']
            ),
        ));
        $this->add(array(
            'name' => 'primerNombre',                       
            'attributes' => array(
                'id'=>'primerNombre', 
                'type' => 'text',
                'placeholder'=>'Primer Nombre',
                'required'=>'required',
                'maxlength'=>'30',
                'class' => $this->cssClass['text']
            ),
        ));
        $this->add(array(
            'name' => 'segundoNombre',                       
            'attributes' => array(
                'id'=>'segundoNombre', 
                'type' => 'text',
                'placeholder'=>'Segundo Nombre',
                'required'=>'required',
                'maxlength'=>'30',
                'class' => $this->cssClass['text']
            ),
        ));
        $this->add(array(
            'name' => 'primerApellido',                       
            'attributes' => array(
                'id'=>'primerApellido', 
                'type' => 'text',
                'placeholder'=>'Primer Apellido',
                'required'=>'required',
                'maxlength'=>'30',
                'class' => $this->cssClass['text']
            ),
        ));
        $this->add(array(
            'name' => 'segundoApellido',                       
            'attributes' => array(
                'id'=>'segundoApellido', 
                'type' => 'text',
                'placeholder'=>'Segundo Apellido',
                'required'=>'required',
                'maxlength'=>'30',
                'class' => $this->cssClass['text']
            ),
        ));
        $this->add(array(
            'name' => 'direccion',                       
            'attributes' => array(
                'id'=>'direccion', 
                'type' => 'text',
                'placeholder'=>'Dirección',
                'required'=>'required',
                'maxlength'=>'50',
                'class' => $this->cssClass['text']
            ),
        ));
        $this->add(array(
            'name' => 'telefono',                       
            'attributes' => array(
                'id'=>'telefono', 
                'type' => 'text',
                'placeholder'=>'Teléfono',
                'required'=>'required',
                'maxlength'=>'50',
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
                        'onClick'=>"$(location).attr('href','".$this->basePath."/admin/datobasicotercero/eliminar?id='+$('#idDatoBasicoTercero').val());",
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

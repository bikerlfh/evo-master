<?php
/*
* @autor Luis Fernando Henriquez
* @copyrigth 2016
*/

namespace Admin\Form;
use Zend\Form\Form;

class FormTipoCuenta extends Form
{
    
    private $cssClass;
    private $basePath;
    public function __construct($serviceLocator,$basePath = null)
    {
        parent::__construct("frmviapago");
        $this->basePath = $basePath;
        $this->setAttributes(array(
            'action' => $this->basePath.'/admin/tipocuenta/index',
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
            'name' => 'idTipoCuenta',                       
            'attributes' => array(
                'id'=>'idTipoCuenta', 
                'type' => 'hidden',
            ),
        ));

        $this->add(array(
            'name' => 'codigo',                       
            'attributes' => array(
                'id'=>'codigo', 
                'type' => 'text',
                'placeholder'=>'Código',
                'required'=>'required',
                'maxlength'=>'5',
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
                'maxlength'=>'20',
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
                        'onClick'=>"$(location).attr('href','".$this->basePath."/admin/tipocuenta/eliminar?id='+$('#idTipoCuenta').val());",
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

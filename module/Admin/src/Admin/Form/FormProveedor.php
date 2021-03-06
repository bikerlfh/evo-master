<?php
/*
* @autor Luis Fernando Henriquez
* @copyrigth 2016
*/

namespace Admin\Form;
use Zend\Form\Form;

class FormProveedor extends Form
{
    private $adapter;
    private $cssClass;
    private $basePath;
    
    //Formulario
    private $FormDatoBasicoTercero;
    
    public function __construct($serviceLocator,$basePath = null)
    {
        parent::__construct("frmproveedor");
        $this->adapter=$serviceLocator->get('Zend\Db\Adapter');
        $this->basePath = $basePath;
        
        $this->FormDatoBasicoTercero = new FormDatoBasicoTercero($serviceLocator, $basePath);
        
        $this->setAttributes(array(
            'action' => $this->basePath.'/admin/proveedor/index',
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
            'name' => 'idProveedor',                       
            'attributes' => array(
                'id'=>'idProveedor', 
                'type' => 'hidden',
            ),
        ));
        
        $this->add(array(
            'name'=> 'nombreProveedor',
            'attributes' => array(
                'id'=>'nombreProveedor', 
                'type' => 'text',
                'placeholder'=>'Proveedor',
                'required'=>'required',
                'onkeypress'=>'return false',
                'class' => $this->cssClass['text']
            ),
        ));
        
        //campo Formulario
        $this->add($this->FormDatoBasicoTercero->get("idDatoBasicoTercero"));
        $this->add($this->FormDatoBasicoTercero->get("nombreTercero"));
         
        /***************Campos tercero **************************/
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
            'name' => 'webSite',                       
            'attributes' => array(
                'id'=>'webSite', 
                'type' => 'text',
                'placeholder'=>'Web Site',
                'maxlength'=>'150',
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
                        'onClick'=>"$(location).attr('href','".$this->basePath."/admin/proveedor/eliminar?id='+$('#idProveedor').val());",
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

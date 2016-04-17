<?php
/*
* @autor Luis Fernando Henriquez
* @copyrigth 2016
*/

namespace Admin\Form;
use Zend\Form\Form;
use Zend\Form\Element;
class FormReview extends Form
{
    private $adapter;
    private $cssClass;
    private $basePath;
    
    private $FormDatoBasicoTercero;
    private $FormProducto;
    
    public function __construct($serviceLocator,$basePath = null)
    {
        parent::__construct("frmreview");
        $this->adapter=$serviceLocator->get('Zend\Db\Adapter');
        $this->basePath = $basePath;
        
        $this->FormDatoBasicoTercero = new FormDatoBasicoTercero($serviceLocator, $basePath);
        $this->FormProducto = new FormProducto($serviceLocator, $basePath);
        
        $this->setAttributes(array(
            'action' => $this->basePath.'/admin/review/index',
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
            'name' => 'idReview',                       
            'attributes' => array(
                'id'=>'idReview', 
                'type' => 'hidden',
            ),
        ));
        $this->add(array(
            'name' => 'idPedidoVentaPosicion',                       
            'attributes' => array(
                'id'=>'idPedidoVentaPosicion', 
                'type' => 'hidden',
            ),
        ));
        
        $this->add($this->FormDatoBasicoTercero->get("nombreTercero"));
        $this->add($this->FormProducto->get("nombreProducto"));
        
        $this->add(array(
            'name' => 'puntuacion',                       
            'attributes' => array(
                'id'=>'puntuacion', 
                'type' => 'number',
                'placeholder'=>'Puntuación',
                'required'=>'required',
                'maxlength'=>'1',
                'onkeyup'=>'if($(this).val()<1) $(this).val(1); else if($(this).val() > 5) $(this).val(5);',
                'class' => $this->cssClass['text']
            ),
        ));
        $textMensaje=new Element\Textarea('mensaje');
        $textMensaje->setAttributes(array(
                'id'=>'mensaje',
                'placeholder'=>'mensaje',
                'required'=>'required',
                'maxlength'=>'150',
                'rows'=>'5',
                'style'=>'width:100%; height: 100%'
        ));
        $this->add($textMensaje);
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
                        'onClick'=>"$(location).attr('href','".$this->basePath."/admin/review/eliminar?id='+$('#idReview').val());",
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

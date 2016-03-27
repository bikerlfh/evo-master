<?php
/*
* @autor Luis Fernando Henriquez
* @copyrigth 2016
*/

namespace Admin\Form;
use Zend\Form\Form;
use Zend\Form\Element;
use Application\Model\Entity\Promocion;

class FormPromocion extends Form
{
    private $adapter;
    private $cssClass;
    private $basePath;
    public function __construct($serviceLocator,$basePath = null)
    {
        parent::__construct("frmpromocion");
        $this->adapter=$serviceLocator->get('Zend\Db\Adapter');
        $this->basePath = $basePath;
        $this->setAttributes(array(
            'action' => $this->basePath.'/admin/promocion/index',
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
            'name' => 'idPromocion',                       
            'attributes' => array(
                'id'=>'idPromocion', 
                'type' => 'hidden',
            ),
        ));
        
        $this->add(array(
            'name' => 'idSaldoInventario',                       
            'attributes' => array(
                'id'=>'idSaldoInventario', 
                'type' => 'hidden',
            ),
        ));
        
        $this->add(array(
            'name' => 'valorAnterior',                       
            'attributes' => array(
                'id'=>'valorAnterior', 
                'type' => 'text',
                'placeholder'=>'Código',
                'readonly'=>true,
                'class' => $this->cssClass['text']
            ),
        ));
        $this->add(array(
            'name' => 'valorPromocion',                       
            'attributes' => array(
                'id'=>'valorPromocion', 
                'type' => 'text',
                'placeholder'=>'Valor Promoción',
                'required'=>'required',
                'maxlength'=>'12',
                'class' => $this->cssClass['spinner']
            ),
        ));
        $this->add(array(
            'name' => 'fechaDesde',                       
            'attributes' => array(
                'id'=>'fechaDesde', 
                'type' => 'date',
                'placeholder'=>'fecha Desde',
                'required'=>'required',
                'class' => $this->cssClass['text']
            ),
        ));
        $this->add(array(
            'name' => 'fechaHasta',                       
            'attributes' => array(
                'id'=>'fechaHasta', 
                'type' => 'date',
                'placeholder'=>'fecha Hasta',
                'required'=>'required',
                'class' => $this->cssClass['text']
            ),
        ));
         /************* select estado ***********/
        $select2 = new Element\Select('estado');
        $select2->setValueOptions(array(1=>'Activo',0=>'Inactivo'));
        $select2->setAttributes(array('id' => 'estado',
                                     'class' => $this->cssClass['select']));
        $this->add($select2);
        /************* select estado ***********/ 
        
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
                        'onClick'=>"$(location).attr('href','".$this->basePath."/admin/promocion/eliminar?id='+$('#idPromocion').val());",
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

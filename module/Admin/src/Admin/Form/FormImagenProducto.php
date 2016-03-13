<?php
/*
* @autor Luis Fernando Henriquez
* @copyrigth 2016
*/

namespace Admin\Form;
use Zend\Form\Form;
use Zend\Form\Element;
use Application\Model\Entity\Producto;
use Zend\InputFilter;

class FormImagenProducto extends Form
{
    private $adapter;
    private $cssClass;
    private $basePath;
    public function __construct($serviceLocator,$basePath = null)
    {
        parent::__construct("frmimagenproducto");
        $this->adapter=$serviceLocator->get('Zend\Db\Adapter');
        $this->basePath = $basePath;
        $this->setAttributes(array(
            'action' => $this->basePath.'/admin/imagenproducto/index',
            'method' => 'post',
            'class'=>'form-horizontal',
            'role'=>'form'
        ));
        $this->cssClass = $serviceLocator->get('Config');
        $this->cssClass = $this->cssClass['cssClass'];
        $this->generarCampos();    
        $this->addInputFilter();
    }
    private function generarCampos()
    {
        $this->add(array(
            'name' => 'idImagenProducto',                       
            'attributes' => array(
                'id'=>'idImagenProducto', 
                'type' => 'hidden',
            ),
        ));
        $this->add(array(
            'name' => 'url',                       
            'attributes' => array(
                'id'=>'url', 
                'type' => 'hidden',
                'disabled'=>true,
                'class' => $this->cssClass['text']
            ),
        ));
        
        $file = new Element\File('image-file');
        $file->setLabel('Imagen')
             ->setAttribute('id', 'image-file')
             ->setAttributes(array('multiple' => true,
                                   'accept'=>".gif,.jpg,.jpeg,.png"));
        $this->add($file);
        
        /************* select idProducto ***********/
        $producto = new Producto($this->adapter);
        $select = new Element\Select('idProducto');
        $select->setValueOptions($producto->generarOptionsSelect());
        $select->setAttributes(array('id' => 'idProducto',
                                     'class' => $this->cssClass['select'],
                                     'required' => "required"));
        $this->add($select);
        /************* select idProducto ***********/
        
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
                        'onClick'=>"$(location).attr('href','".$this->basePath."/admin/imagenproducto/eliminar?id='+$('#idImagenProducto').val());",
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
    
    public function addInputFilter()
    {
        $inputFilter = new InputFilter\InputFilter();
        // File Input
        $fileInput = new InputFilter\FileInput('image-file');
        $fileInput->setRequired(true);
        $fileInput->getFilterChain()->attachByName(
            'filerenameupload',
            array(
                'target'    => './public/imguploads/',
                //'randomize' => true,
                'overwrite'       => true,
                'use_upload_name' => true,
            )
        );
        $inputFilter->add($fileInput);
        $this->setInputFilter($inputFilter);
    }
}
?>

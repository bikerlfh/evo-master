<?php
/*
* @autor Luis Fernando Henriquez
* @copyrigth 2016
*/

namespace Admin\Form;
use Zend\Form\Form;
use Zend\Form\Element;
use Application\Model\Entity\Marca;
use Application\Model\Entity\Categoria;

class FormProducto extends Form
{
    private $adapter;
    private $cssClass;
    private $basePath;
    public function __construct($serviceLocator,$basePath = null)
    {
        parent::__construct("frmproducto");
        $this->adapter=$serviceLocator->get('Zend\Db\Adapter');
        $this->basePath = $basePath;
        $this->setAttributes(array(
            'action' => $this->basePath.'/admin/producto/index',
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
            'name' => 'idProducto',                       
            'attributes' => array(
                'id'=>'idProducto', 
                'type' => 'hidden',
            ),
        ));
        /************* select Marca ***********/
        $marca = new Marca($this->adapter);
        $select = new Element\Select('idMarca');
        $select->setValueOptions($marca->generarOptionsSelect());
        $select->setAttributes(array('id' => 'idMarca',
                                     'class' => $this->cssClass['select'],
                                     'required' => true));
        $this->add($select);
        /************* select Marca ***********/ 
        
        /************* select Categoria ***********/
        $categoria = new Categoria($this->adapter);
        $select2 = new Element\Select('idCategoria');
        $select2->setValueOptions($categoria->generarOptionsSelect());
        $select2->setAttributes(array('id' => 'idCategoria',
                                     'class' => $this->cssClass['select'],
                                     'required' => true));
        $this->add($select2);
        /************* select Categoria ***********/ 
        
        $this->add(array(
            'name' => 'nombreProducto',                       
            'attributes' => array(
                'id'=>'nombreProducto', 
                'type' => 'text',
                'placeholder'=>'Producto',
                'required'=>'required',
                'onkeypress'=>'return false',
                'class' => $this->cssClass['text']
            ),
        ));
        
        $this->add(array(
            'name' => 'codigo',                       
            'attributes' => array(
                'id'=>'codigo', 
                'type' => 'text',
                'placeholder'=>'Código',
                'required'=>true,
                'maxlength'=>'10',
                'class' => $this->cssClass['text']
            ),
        ));
        $this->add(array(
            'name' => 'nombre',                       
            'attributes' => array(
                'id'=>'nombre', 
                'type' => 'text',
                'placeholder'=>'Nombre',
                'maxlength'=>'50',
                'class' => $this->cssClass['text']
            ),
        ));
        $this->add(array(
            'name' => 'referencia',                       
            'attributes' => array(
                'id'=>'referencia', 
                'type' => 'text',
                'placeholder'=>'Referencia',
                'maxlength'=>'50',
                'class' => $this->cssClass['text']
            ),
        ));
        
        $textDescripcion=new Element\Textarea('descripcion');
        $textDescripcion->setAttributes(array(
                'id'=>'descripcion',
                'placeholder'=>'Descripción',
                'required'=>'required',
                'maxlength'=>'100000',
                'rows'=>'5',
                'style'=>'width:100%; height: 100%'
        ));
        $this->add($textDescripcion);
        
        $textEspecificacion=new Element\Textarea('especificacion');
        $textEspecificacion->setAttributes(array(
                'id'=>'especificacion',
                'placeholder'=>'Especificación  ',
                'maxlength'=>'100000',
                'rows'=>'5',
                'style'=>'width:100%; height: 100%'
        ));
        $this->add($textEspecificacion);
        
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
                        'onClick'=>"$(location).attr('href','".$this->basePath."/admin/producto/eliminar?id='+$('#idProducto').val());",
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

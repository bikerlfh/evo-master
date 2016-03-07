<?php
/*
* @autor Luis Fernando Henriquez
* @copyrigth 2016
*/

namespace Admin\Form;
use Zend\Form\Form;
use Zend\Form\Element;
use Application\Model\Entity\Municipio;
use Application\Model\Entity\Proveedor;

class FormProveedorOficina extends Form
{
    private $adapter;
    private $cssClass;
    private $basePath;
    public function __construct($serviceLocator,$basePath = null)
    {
        parent::__construct("frmproveedoroficina");
        $this->adapter=$serviceLocator->get('Zend\Db\Adapter');
        $this->basePath = $basePath;
        $this->setAttributes(array(
            'action' => $this->basePath.'/admin/proveedoroficina/index',
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
            'name' => 'idProveedorOficina',                       
            'attributes' => array(
                'id'=>'idProveedorOficina', 
                'type' => 'hidden',
            ),
        ));
        $this->add(array(
            'name' => 'idProveedor',                       
            'attributes' => array(
                'id'=>'idProveedor', 
                'type' => 'hidden',
            ),
        ));
        /************* select Municipio ***********/
        $municipio= new Municipio($this->adapter);
        $select = new Element\Select('idMunicipio');
        $select->setValueOptions($municipio->generarOptionsSelect());
        $select->setAttributes(array('id' => 'idMunicipio',
                                     'class' => $this->cssClass['select'],
                                     'required' => true));
        $this->add($select);
        /************* select Municipio ***********/ 
        
        /************* select Proveedor ***********/
        $proveedor = new Proveedor($this->adapter);
        $select1 = new Element\Select('idProveedor');
        $select1->setValueOptions($proveedor->generarOptionsSelect());
        $select1->setAttributes(array('id' => 'idProveedor',
                                     'class' => $this->cssClass['select'],
                                     'required' => true));
        $this->add($select1);
        /************* select Proveedor ***********/ 
        
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
            'name' => 'direccion',                       
            'attributes' => array(
                'id'=>'direccion', 
                'type' => 'text',
                'placeholder'=>'Dirección',
                'maxlength'=>'150',
                'class' => $this->cssClass['text']
            ),
        ));
        $this->add(array(
            'name' => 'telefono',                       
            'attributes' => array(
                'id'=>'telefono', 
                'type' => 'text',
                'placeholder'=>'Telefono',
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
                        'onClick'=>"$(location).attr('href','".$this->basePath."/admin/proveedoroficina/eliminar?id='+$('#idProveedorOficina').val());",
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

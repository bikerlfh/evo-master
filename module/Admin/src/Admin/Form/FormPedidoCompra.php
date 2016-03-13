<?php
/*
* @autor Luis Fernando Henriquez
* @copyrigth 2016
*/

namespace Admin\Form;
use Zend\Form\Form;
use Zend\Form\Element;
use Application\Model\Entity\EstadoPedido;
use Zend\InputFilter;

class FormPedidoCompra extends Form
{
    private $adapter;
    private $cssClass;
    private $basePath;
    private $FormProveedor;
    private $FormBase;
    public function __construct($serviceLocator,$basePath = null)
    {
        parent::__construct("frmpedidocompra");
        $this->adapter=$serviceLocator->get('Zend\Db\Adapter');
        $this->basePath = $basePath;
        $this->FormProveedor = new FormProveedor($serviceLocator,$basePath);
        $this->FormBase = new FormBase($serviceLocator,$basePath);
        $this->setAttributes(array(
            'action' => $this->basePath.'/admin/pedidocompra/index',
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
            'name' => 'idPedidoCompra',                       
            'attributes' => array(
                'id'=>'idPedidoCompra', 
                'type' => 'hidden',
            ),
        ));
        
        /************ CAMPOS DEL PROVEEDOR *****************/
        $this->add($this->FormProveedor->get('idProveedor'));
        $this->add($this->FormProveedor->get('nombreProveedor'));
        $this->add($this->FormBase->get('btnBuscarProveedor'));
        /************ CAMPOS DEL PROVEEDOR *****************/
        
        $file = new Element\File('file-documentoPago');
        $file->setLabel('Imagen')
             ->setAttribute('id', 'file-documentoPago')
             ->setAttributes(array('multiple' => false,
                                   'accept'=>".gif,.jpg,.jpeg,.png"));
        $this->add($file);
        
        /************* EstadoPedido ***********/
        $EstadoPedido = new EstadoPedido($this->adapter);
        $select = new Element\Select('idEstadoPedido');
        $select->setValueOptions($EstadoPedido->generarOptionsSelect());
        $select->setAttributes(array('id' => 'idEstadoPedido',
                                     'class' => $this->cssClass['select'],
                                     'required' => true));
        $this->add($select);
        /************* EstadoPedido ***********/ 
        
        
         $this->add(array(
            'name' => 'numeroPedido',                       
            'attributes' => array(
                'id'=>'numeroPedido', 
                'type' => 'text',
                'readonly'=>true,
                'class' => $this->cssClass['text']
            ),
        ));
        
        $this->add($this->FormBase->get('btnGuardar'));
        $this->add($this->FormBase->get('btnModificar'));
        
        $this->add(array(
                'name'=>'btnEliminar',			
                'attributes'=>array(
                        'id'=>'btnEliminar',
                        'type'=>'button',
                        'value'=>'Eliminar',
                        'title'=>'Eliminar',    
                        'onClick'=>"$(location).attr('href','".$this->basePath."/admin/pedidocompra/eliminar?id='+$('#idPedidoCompra').val());",
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
        $fileInput = new InputFilter\FileInput('file-documentoPago');
        $fileInput->setRequired(true);
        $fileInput->getFilterChain()->attachByName(
            'filerenameupload',
            array(
                'target'    => './public/imguploads/compra',
                //'randomize' => true,
                'overwrite'       => false,
                'use_upload_name' => true,  
            )
        );
        $inputFilter->add($fileInput);
        $this->setInputFilter($inputFilter);
    }
}
?>

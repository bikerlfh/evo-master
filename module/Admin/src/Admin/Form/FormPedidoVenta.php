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

class FormPedidoVenta extends Form
{
    private $adapter;
    private $cssClass;
    private $basePath;
    private $FormCliente;
    private $FormBase;
    public function __construct($serviceLocator,$basePath = null)
    {
        parent::__construct("frmpedidoventa");
        $this->adapter=$serviceLocator->get('Zend\Db\Adapter');
        $this->basePath = $basePath;
        $this->FormCliente = new FormCliente($serviceLocator,$basePath);
        $this->FormBase = new FormBase($serviceLocator,$basePath);
        $this->setAttributes(array(
            'action' => $this->basePath.'/admin/pedidoventa/index',
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
            'name' => 'idPedidoVenta',                       
            'attributes' => array(
                'id'=>'idPedidoVenta', 
                'type' => 'hidden',
            ),
        ));
        
        /************ CAMPOS DEL CLIENTE *****************/
        $this->add($this->FormCliente->get('idCliente'));
        $this->add($this->FormCliente->get('nombreCliente'));
        $this->add($this->FormBase->get('btnBuscarCliente'));
        /************ CAMPOS DEL PROVEEDOR *****************/
        
        $file = new Element\File('file-documentoPago');
        $file->setLabel('Imagen')
             ->setAttribute('id', 'file-documentoPago')
             ->setAttributes(array('multiple' => false,
                                   'accept'=>".gif,.jpg,.jpeg,.png"));
        $this->add($file);
        
        /************* EstadoPedido ***********/
        $EstadoPedido = new EstadoPedido($this->adapter);
        $select = new Element\Select('idEstadoPedidoVenta');
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
                        'onClick'=>"$(location).attr('href','".$this->basePath."/admin/pedidoventa/eliminar?id='+$('#idPedidoVenta').val());",
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

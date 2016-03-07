<?php
/*
* @autor Luis Fernando Henriquez
* @copyrigth 2016
*/

namespace Admin\Form;
use Zend\Form\Form;
use Zend\Form\Element;
use Application\Model\Entity\TipoCuenta;
use Application\Model\Entity\ViaPago;
use Application\Model\Entity\DatoBasicoTercero;
use Application\Model\Entity\ProveedorOficina;

class FormProveedorCuenta extends Form
{
    private $adapter;
    private $cssClass;
    private $basePath;
    public function __construct($serviceLocator,$basePath = null)
    {
        parent::__construct("frmproveedorcuenta");
        $this->adapter=$serviceLocator->get('Zend\Db\Adapter');
        $this->basePath = $basePath;
        $this->setAttributes(array(
            'action' => $this->basePath.'/admin/proveedorcuenta/index',
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
            'name' => 'idProveedorCuenta',                       
            'attributes' => array(
                'id'=>'idProveedorCuenta', 
                'type' => 'hidden',
            ),
        ));
        /************* select TipoCuenta ***********/
        $tipoCuenta= new TipoCuenta($this->adapter);
        $select = new Element\Select('idTipoCuenta');
        $select->setValueOptions($tipoCuenta->generarOptionsSelect());
        $select->setAttributes(array('id' => 'idTipoCuenta',
                                     'class' => $this->cssClass['select'],
                                     'required' => true));
        $this->add($select);
        /************* select TipoCuenta ***********/ 
        
        /************* select ViaPago ***********/
        $viaPago= new ViaPago($this->adapter);
        $select2 = new Element\Select('idViaPago');
        $select2->setValueOptions($viaPago->generarOptionsSelect());
        $select2->setAttributes(array('id' => 'idViaPago',
                                     'class' => $this->cssClass['select'],
                                     'required' => true));
        $this->add($select2);
        /************* select TipoCuenta ***********/ 
        
        /************* select datobasicoTercero ***********/
        $ProveedorOficina= new ProveedorOficina($this->adapter);
        $select1 = new Element\Select('idProveedorOficina');
        $select1->setValueOptions($ProveedorOficina->generarOptionsSelect());
        $select1->setAttributes(array('id' => 'idProveedorOficina',
                                     'class' => $this->cssClass['select'],
                                     'required' => true));
        $this->add($select1);
        /************* select datobasicoTercero ***********/ 
         $this->add(array(
            'name' => 'numeroCuentaBancaria',                       
            'attributes' => array(
                'id'=>'numeroCuentaBancaria', 
                'type' => 'text',
                'placeholder'=>'Número Cuenta Bancaria',
                'required'=>true,
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
                        'onClick'=>"$(location).attr('href','".$this->basePath."/admin/proveedorcuenta/eliminar?id='+$('#idProveedorCuenta').val());",
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

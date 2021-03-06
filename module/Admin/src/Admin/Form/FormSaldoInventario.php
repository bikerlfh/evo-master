<?php
/*
 * @autor Luis Fernando Henriquez
 * @copyrigth 2016
 */

namespace Admin\Form;
use Zend\Form\Form;
use Zend\Form\Element;
use Application\Model\Entity\Proveedor;

class FormSaldoInventario extends Form {

    private $adapter;
    private $cssClass;
    private $basePath;

    /* Formularios */
    private $FormProducto;
    private $FormProveedor;
    private $form;

    public function __construct($serviceLocator, $basePath = null) {
        parent::__construct("frmsaldoinventario");
        $this->adapter = $serviceLocator->get('Zend\Db\Adapter');
        $this->basePath = $basePath;
        $this->FormProducto = new FormProducto($serviceLocator, $basePath);
        $this->FormProveedor = new FormProveedor($serviceLocator, $basePath);

        $this->setAttributes(array(
            'action' => $this->basePath . '/admin/saldoinventario/index',
            'method' => 'post',
            'class' => 'form-horizontal',
            'role' => 'form'
        ));
        $this->cssClass = $serviceLocator->get('Config');
        $this->cssClass = $this->cssClass['cssClass'];
        $this->generarCampos();
    }

    private function generarCampos() {
        $this->add(array(
            'name' => 'idSaldoInventario',
            'attributes' => array(
                'id' => 'idSaldoInventario',
                'type' => 'hidden',
            ),
        ));
        /*************  Producto ********** */
        $this->add($this->FormProducto->get("idProducto"));
        $this->add($this->FormProducto->get("nombreProducto"));
        /************* Producto  ********** */

        /*********** Proveedor ************* */

        $this->add($this->FormProveedor->get("idProveedor"));
        $this->add($this->FormProveedor->get("nombreProveedor"));

        /** ********* Proveedor ************* */

        $this->add(array(
            'name' => 'cantidad',
            'attributes' => array(
                'id' => 'cantidad',
                'type' => 'text',
                'placeholder' => 'Cantidad',
                'required' => true,
                'maxlength' => '11',
                'required' => true,
                'onKeyPress' => "return validarTecla(event,'num')",
                'class' => $this->cssClass['text']
            ),
        ));
        $this->add(array(
            'name' => 'costoTotal',
            'attributes' => array(
                'id' => 'costoTotal',
                'type' => 'text',
                'placeholder' => 'Costo Total',
                'maxlength' => '11',
                'required' => true,
                'onKeyPress' => "return validarTecla(event,'num')",
                'class' => $this->cssClass['text']
            ),
        ));
        $this->add(array(
            'name' => 'valorVenta',
            'attributes' => array(
                'id' => 'valorVenta',
                'type' => 'text',
                'placeholder' => 'Valor Venta',
                'maxlength' => '11',
                'required' => true,
                'onKeyPress' => "return validarTecla(event,'num')",
                'class' => $this->cssClass['text']
            ),
        ));
        $this->add(array(
            'name' => 'url',
            'attributes' => array(
                'id' => 'urlProuctoProveedor',
                'type' => 'text',
                'placeholder' => 'Url producto - proveedor',
                'maxlength' => '150',
                'class' => $this->cssClass['text']
            ),
        ));
        /************* select estado ***********/
        $select2 = new Element\Select('estado');
        $select2->setValueOptions(array(''=>'',1=>'Activo',0=>'Inactivo'));
        $select2->setAttributes(array('id' => 'estado',
                                     'class' => $this->cssClass['select'],
                                     'required'=>true));
        $this->add($select2);
        /************* select estado ***********/ 
        $this->add(array(
            'name' => 'btnGuardar',
            'attributes' => array(
                'id' => 'btnGuardar',
                'type' => 'submit',
                'value' => 'Guardar',
                'title' => 'Guardar',
                'class' => $this->cssClass['btnGuardar']
            )
        ));
        $this->add(array(
            'name' => 'btnModificar',
            'attributes' => array(
                'id' => 'btnModificar',
                'type' => 'submit',
                'value' => 'Modificar',
                'title' => 'Modificar',
                'style' => 'margin:2px',
                'class' => $this->cssClass['btnModificar']
            )
        ));
        $this->add(array(
            'name' => 'btnEliminar',
            'attributes' => array(
                'id' => 'btnEliminar',
                'type' => 'button',
                'value' => 'Eliminar',
                'title' => 'Eliminar',
                'onClick' => "$(location).attr('href','" . $this->basePath . "/admin/saldoinventario/eliminar?id='+$('#idSaldoInventario').val());",
                'style' => 'margin:2px',
                'class' => $this->cssClass['btnEliminar']
            )
        ));
        $this->add(array(
            'name' => 'btnCancelar',
            'attributes' => array(
                'id' => 'btnCancelar',
                'type' => 'button',
                'value' => 'Cancelar',
                'title' => 'Cancelar',
                'onClick' => "limpiarformulario(" . $this->getAttribute("name") . ");",
                'style' => 'margin:2px',
                'class' => $this->cssClass['btnCancelar']
            )
        ));
    }
}
?>

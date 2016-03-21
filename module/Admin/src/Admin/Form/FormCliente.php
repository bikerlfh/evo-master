<?php

/*
 * @autor Ezequiel David
 * @copyrigth 2016
 */

namespace Admin\Form;

use Zend\Form\Form;
use Zend\Form\Element;
use Application\Model\Entity\Municipio;

class FormCliente extends Form {

    private $adapter;
    private $cssClass;
    private $basePath;
    //Formulario
    private $FormDatoBasicoTercero;

    public function __construct($serviceLocator, $basePath = null) {
        parent::__construct("frmcliente");
        $this->adapter = $serviceLocator->get('Zend\Db\Adapter');
        $this->basePath = $basePath;

        $this->FormDatoBasicoTercero = new FormDatoBasicoTercero($serviceLocator, $basePath);

        $this->setAttributes(array(
            'action' => $this->basePath . '/admin/cliente/index',
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
            'name' => 'idCliente',
            'attributes' => array(
                'id' => 'idCliente',
                'type' => 'hidden',
            ),
        ));

        $this->add(array(
            'name' => 'nombreCliente',
            'attributes' => array(
                'id' => 'nombreCliente',
                'type' => 'text',
                'placeholder' => 'Cliente',
                'required' => 'required',
                'onkeypress' => 'return false',
                'class' => $this->cssClass['text']
            ),
        ));

        /*         * ****** campos Tercero***************** */
        //campo Modal
        $this->add($this->FormDatoBasicoTercero->get("nit"));
        $this->add($this->FormDatoBasicoTercero->get("descripcion"));

        //campo Formulario
        $this->add($this->FormDatoBasicoTercero->get("idDatoBasicoTercero"));
        $this->add($this->FormDatoBasicoTercero->get("nombreTercero"));

        $municipio = new Municipio($this->adapter);
        $select = new Element\Select('idMunicipio');
        $select->setValueOptions($municipio->generarOptionsSelect());
        $select->setAttributes(array('id' => 'idMunicipio',
                                    'class' => $this->cssClass['select'],
                                    'required' => true));
        $this->add($select);

        $this->add(array(
            'name' => 'email',
            'attributes' => array(
                'id' => 'email',
                'type' => 'text',
                'placeholder' => 'E-mail',
                'required' => true,
                'maxlength' => '150',
                'class' => $this->cssClass['text']
            ),
        ));
        $this->add(array(
            'name' => 'direccion',
            'attributes' => array(
                'id' => 'direccion',
                'type' => 'text',
                'placeholder' => 'Dirección',
                'maxlength' => '150',
                'class' => $this->cssClass['text']
            ),
        ));

        $this->add(array(
            'name' => 'telefono',
            'attributes' => array(
                'id' => 'telefono',
                'type' => 'text',
                'placeholder' => 'Teléfono',
                'maxlength' => '50',
                'class' => $this->cssClass['text']
            ),
        ));

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
                'onClick' => "$(location).attr('href','" . $this->basePath . "/admin/cliente/eliminar?id='+$('#idCliente').val());",
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

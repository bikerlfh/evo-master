<?php

namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Admin\Form\FormBase;
use Admin\Form\FormSaldoInventario;
use Application\Model\Entity\SaldoInventario;
use Zend\Session\Container;

class SaldoInventarioController extends AbstractActionController {

    private $SaldoInventario;
    private $form;
    private $user_session;

    public function __construct() {
        $this->user_session = new Container();
    }
    public function indexAction() {
        $this->validarSession();
        // se asigna el layout admin
        $this->layout('layout/admin');
        // se obtiene el adapter
        $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
        // Parametro pasado por get, con el cual se sabe si se seleccionó objeto para modificar
        $id = $this->params()->fromQuery('id', null);

        $this->SaldoInventario = new SaldoInventario($this->dbAdapter);
        $this->form = new FormSaldoInventario($this->getServiceLocator(), $this->getRequest()->getBaseUrl());
        /*         * *********************************************************************************** */
        // Se agregan los botones de buscar producto y proveedor al formualrio saldo inventario
        /*         * *********************************************************************************** */
        $formBase = new FormBase($this->getServiceLocator(), $this->getRequest()->getBaseUrl());
        $this->form->add($formBase->get("btnBuscarProducto"));
        $this->form->add($formBase->get("btnBuscarProveedor"));
        /*         * *********************************************************************************** */
        $this->configurarBotonesFormulario(false);
        // Si se ha enviado parámetros por post, se evalua si se va a modificar o a guardar
        if (count($this->request->getPost()) > 0) {
            $datos = $this->request->getPost();
            // Si se envia el id de la saldoinventario se modifica este.
            if ($datos["idSaldoInventario"] != null) {
                $returnCrud = $this->consultarMessage("errorUpdate");
                if ($this->SaldoInventario->modificarSaldoInventario($datos['idSaldoInventario'], $datos['idProducto'], $datos['idProveedor'], $datos['cantidad'], $datos['valorCompra'], $datos['valorVenta'], $this->user_session->idUsuario, date('d-m-Y H:i:s')))
                    $returnCrud = $this->consultarMessage("okUpdate");
            }
            else {
                $returnCrud = $this->consultarMessage("errorSave");
                // se guarda la nueva saldoinventario
                if ($this->SaldoInventario->guardarSaldoInventario($datos['idProducto'], $datos['idProveedor'], $datos['cantidad'], $datos['valorCompra'], $datos['valorVenta'], $this->user_session->idUsuario, date('d-m-Y H:i:s')))
                    $returnCrud = $this->consultarMessage("okSave");
            }
            return new ViewModel(array('form' => $this->form, 'msg' => $returnCrud, 'registros' => $this->SaldoInventario->consultarTodoSaldoInventario()));
        }
        // si existe el parametro $id  se consulta la saldoinventario y se carga el formulario.
        else if (isset($id)) {
            $this->SaldoInventario->consultarSaldoInventarioPorIdSaldoInventario($this->params()->fromQuery('id'));
            $this->form->get("idSaldoInventario")->setValue($this->SaldoInventario->getIdSaldoInventario());
            //Campos Producto
            $this->form->get("idProducto")->setValue($this->SaldoInventario->getIdProducto());
            $descripcionProducto = $this->SaldoInventario->Producto->getCodigo() . ' - ' . $this->SaldoInventario->Producto->getNombre();
            $this->form->get("nombreProducto")->setValue($descripcionProducto);
            //Campo Proveedor
            $this->form->get("idProveedor")->setValue($this->SaldoInventario->getIdProveedor());
            $descripcionProveedor = $this->SaldoInventario->Proveedor->DatoBasicoTercero->getnit() . ' - ' . $this->SaldoInventario->Proveedor->DatoBasicoTercero->getdescripcion();
            $this->form->get("nombreProveedor")->setValue($descripcionProveedor);

            $this->form->get("cantidad")->setValue($this->SaldoInventario->getCantidad());
            $this->form->get("valorCompra")->setValue($this->SaldoInventario->getValorCompra());
            $this->form->get("valorVenta")->setValue($this->SaldoInventario->getValorVenta());

            $this->configurarBotonesFormulario(true);
        }
        return new ViewModel(array('form' => $this->form, 'registros' => $this->SaldoInventario->consultarTodoSaldoInventario()));
    }

    public function buscarAction() {
        $this->validarSession();
        // se obtiene el adapter
        $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');

        $this->form = new FormSaldoInventario($this->getServiceLocator(), $this->getRequest()->getBaseUrl());
        
        /*         * *********************************************************************************** */
        // Se agregan los botones de buscar producto y proveedor al formualrio saldo inventario
        /*         * *********************************************************************************** */
        $formBase = new FormBase($this->getServiceLocator(), $this->getRequest()->getBaseUrl());
        $this->form->add($formBase->get("btnBuscarProducto2"));
        $this->form->add($formBase->get("btnBuscarProveedor2"));
        /*         * *********************************************************************************** */
        
        /** Campos para saber en donde se deben devolver los valores de la busqueda * */
        $campoId = $this->params()->fromQuery('campoId', null) == null ? 'idSaldoInventario' : $this->params()->fromQuery('campoId', null);
        $campoIdProducto = $this->params()->fromQuery('campoIdProducto', null) == null ? 'campoIdProducto' : $this->params()->fromQuery('campoIdProducto', null);
        $campoNombre = $this->params()->fromQuery('campoNombre', null) == null ? 'nombreProducto' : $this->params()->fromQuery('campoNombre', null);
        $campoValorVenta = $this->params()->fromQuery('campoValorVenta', null) == null ? 'campoValorVenta' : $this->params()->fromQuery('campoValorVenta', null);
        $campoCantidad = $this->params()->fromQuery('campoCantidad', null) == null ? 'campoCantidad' : $this->params()->fromQuery('campoCantidad', null);
        
        //****Campos modal *****//
        $botonClose = $this->params()->fromQuery('botonClose',null) == null ? 'btnClosePop' :$this->params()->fromQuery('botonClose',null);
        $contenedorDialog = $this->params()->fromQuery('contenedorDialog',null) == null ? 'modal-dialog-display' :$this->params()->fromQuery('contenedorDialog',null);
        $modal = $this->params()->fromQuery('modal',null) == null ? 'textModal' :$this->params()->fromQuery('modal',null);

        $registros = array();
        if (count($this->request->getPost()) > 0) {
            $this->SaldoInventario = new SaldoInventario($this->dbAdapter);
            $datos = $this->request->getPost();

            $this->form->get("idProducto")->setValue($datos["idProducto"]);
            $this->form->get("nombreProducto")->setValue($datos['nombreProducto']);
            $this->form->get("idProveedor")->setValue($datos["idProveedor"]);
            $this->form->get("nombreProveedor")->setValue($datos['nombreProveedor']);

            $registros = $this->SaldoInventario->consultaAvanzadaSaldoInventario($datos['idProducto'], $datos['idProveedor']);
        }

        // consultamos  y los devolvemos a la vista    
        $view = new ViewModel(array('form' => $this->form, 
                                    'campoId' => $campoId, 
                                    'campoNombre' => $campoNombre,
                                    'campoValorVenta'=> $campoValorVenta, 
                                    'campoCantidad'=> $campoCantidad, 
                                    'campoIdProducto'=> $campoIdProducto,
                                    'botonClose'=> $botonClose,
                                    'contenedorDialog'=> $contenedorDialog,
                                    'modal'=> $modal,
                                    'registros' => $registros));
        $view->setTerminal(true);
        return $view;
    }

    public function eliminarAction() {
        //$this->validarSession();
        $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
        $this->SaldoInventario = new SaldoInventario($this->dbAdapter);
        $id = $this->params()->fromQuery('id', null);
        if ($id != null) {
            $this->SaldoInventario->eliminarSaldoInventario($id);
            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . '/admin/saldoinventario');
        }
    }

    private function consultarMessage($nameMensaje) {
        $serviceLocator = $this->getServiceLocator()->get('Config');
        $mensaje = $serviceLocator['MsgCrud'];
        $mensaje = $mensaje[$nameMensaje];
        return $mensaje['function'] . "('" . $mensaje['title'] . "','" . $mensaje['message'] . "');";
    }

    private function configurarBotonesFormulario($modificarBool) {
        if ($modificarBool == true) {
            $this->form->get("btnGuardar")->setAttribute("type", "hidden");
            $this->form->get("btnModificar")->setAttribute("type", "submit");
            $this->form->get("btnEliminar")->setAttribute("type", "button");
        } else {
            $this->form->get("btnGuardar")->setAttribute("type", "submit");
            $this->form->get("btnModificar")->setAttribute("type", "hidden");
            $this->form->get("btnEliminar")->setAttribute("type", "hidden");
        }
    }

    private function validarSession() {
        //<== Si no existe la session se redirge al login ==>
        if (!isset($_SESSION['user'])) {
            return $this->redirect()->toUrl(str_replace("/public", "", $this->getRequest()->getBaseUrl()) . '/admin/login');
        }
        $this->user_session = $_SESSION['user'];
    }

}

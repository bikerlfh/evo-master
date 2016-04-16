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
        $this->user_session = new Container('user');
    }
    public function indexAction() {
        $this->validarSession();
        // se asigna el layout admin
        $this->layout('layout/admin');
        // se obtiene el adapter
        $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
        // Parametro pasado por get, con el cual se sabe si se seleccionó objeto para modificar
        $id = $this->params()->fromQuery('idSaldoInventario', null);

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
                if ($this->SaldoInventario->modificarSaldoInventario($datos['idSaldoInventario'], $datos['idProducto'], $datos['idProveedor'], $datos['cantidad'], $datos['costoTotal'], $datos['valorVenta'],$datos['url'],$datos['estado'], $this->user_session->idUsuario))
                    $returnCrud = $this->consultarMessage("okUpdate");
            }
            else 
            {
                // Se valida que no exista un saldo inventario con el mismo producto y proveedor
                if ($this->SaldoInventario->consultarSaldoInventarioPorIdProductoIdProveedor($datos['idProducto'], $datos['idProveedor'])) {
                    return new ViewModel(array('form' => $this->form, 'validacion' => 'Ya existe un saldo invenario con el producto y el proveedor seleccionados.'));
                }
                $returnCrud = $this->consultarMessage("errorSave");
                // se guarda la nueva saldoinventario
                if ($this->SaldoInventario->guardarSaldoInventario($datos['idProducto'], $datos['idProveedor'], $datos['cantidad'], $datos['costoTotal'], $datos['valorVenta'],$datos['url'],$datos['estado'], $this->user_session->idUsuario))
                    $returnCrud = $this->consultarMessage("okSave");
            }
            return new ViewModel(array('form' => $this->form, 'msg' => $returnCrud));
        }
        // si existe el parametro $id  se consulta la saldoinventario y se carga el formulario.
        else if (isset($id)) {
            $this->SaldoInventario->consultarSaldoInventarioPorIdSaldoInventario($id);
            $this->form->get("idSaldoInventario")->setValue($this->SaldoInventario->getIdSaldoInventario());
            $this->form->get("idProducto")->setValue($this->SaldoInventario->getIdProducto());
            $descripcionProducto = $this->SaldoInventario->Producto->getCodigo() . ' - ' . $this->SaldoInventario->Producto->getNombre();
            $this->form->get("nombreProducto")->setValue($descripcionProducto);
            $this->form->get("idProveedor")->setValue($this->SaldoInventario->getIdProveedor());
            $descripcionProveedor = $this->SaldoInventario->Proveedor->DatoBasicoTercero->getnit() . ' - ' . $this->SaldoInventario->Proveedor->DatoBasicoTercero->getdescripcion();
            $this->form->get("nombreProveedor")->setValue($descripcionProveedor);
            $this->form->get("cantidad")->setValue($this->SaldoInventario->getCantidad());
            $this->form->get("costoTotal")->setValue($this->SaldoInventario->getCostoTotal());
            $this->form->get("valorVenta")->setValue($this->SaldoInventario->getValorVenta());
            $this->form->get("url")->setValue($this->SaldoInventario->getUrl());
            $this->form->get("estado")->setValue($this->SaldoInventario->getEstado());
            $this->configurarBotonesFormulario(true);
        }
        $this->form->get('estado')->setValue(1);
        return new ViewModel(array('form' => $this->form));
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
        $this->form->add($formBase->get("btnBuscarProducto"));
        $this->form->add($formBase->get("btnBuscarProveedor"));
        /*         * *********************************************************************************** */
        
        /** Campos para saber en donde se deben devolver los valores de la busqueda * */
        $campoId = $this->params()->fromQuery('campoId', null) == null ? 'idSaldoInventario' : $this->params()->fromQuery('campoId', null);
        $campoIdProducto = $this->params()->fromQuery('campoIdProducto', null) == null ? 'campoIdProducto' : $this->params()->fromQuery('campoIdProducto', null);
        $campoNombre = $this->params()->fromQuery('campoNombre', null) == null ? 'nombreProducto' : $this->params()->fromQuery('campoNombre', null);
        $campoValorVenta = $this->params()->fromQuery('campoValorVenta', null) == null ? 'campoValorVenta' : $this->params()->fromQuery('campoValorVenta', null);
        $campoCantidad = $this->params()->fromQuery('campoCantidad', null) == null ? 'campoCantidad' : $this->params()->fromQuery('campoCantidad', null);
        // Parametro que se utiliza para determinar si se va a redirigir a alguna vista en particular el id del saldo inventario seleccionado
        // Si el origen es saldoinventario/index, al dar click en la fila, esta debe redirigir al formualrio de saldo inventario
        $origen = $this->params()->fromQuery('origen', null);
         //**** OJO: la Uri se debe enviar a la busqueda *****//
        $Uri = $this->getRequest()->getRequestUri();

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
                                    'Uri'=> $Uri,
                                    'origen'=>$origen,
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

    private function validarSession()
    {
        $dif = date("H:i:s", strtotime("00:00:00") + strtotime(date('H:i:s')) - strtotime($this->user_session->timeLastActivity) );
        $dif = explode(':',$dif)[0]*3600+explode(':',$dif)[1]*60+explode(':',$dif)[2];
        // Si el no existe la variable de sesión o  tiempo de inactividad sobrepasa al parametrizado  se cierra la sesión
        if (!$this->user_session->offsetExists('idUsuario') || 
            $dif > $this->user_session->getManager()->getConfig()->getRememberMeSeconds()) 
        {
            $manager = $this->user_session->getManager();
            $manager->getStorage()->clear(); //delete all session values unless it is immutable
            unset($_SESSION['user']);
            return $this->redirect()->toRoute("login_admin");
        }
        $this->user_session->timeLastActivity = date('H:i:s');
    }

}

<?php

namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Admin\Form\FormBase;
use Admin\Form\FormProveedor;
use Application\Model\Entity\Proveedor;
use Zend\Session\Container;
use Admin\Form\FormDatoBasicoTercero;
use Application\Model\Clases\FuncionesBase;

class ProveedorController extends AbstractActionController {

    private $Proveedor;
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
        $id = $this->params()->fromQuery('idProveedor', null);

        $this->Proveedor = new Proveedor($this->dbAdapter);
        $this->form = new FormProveedor($this->getServiceLocator(), $this->getRequest()->getBaseUrl());
        /*         * *********************************************************************************** */
        // Se agregan el boton de buscar Tercero al formualrio saldo inventario
        /*         * *********************************************************************************** */
        $formBase = new FormBase($this->getServiceLocator(), $this->getRequest()->getBaseUrl());
        $this->form->add($formBase->get("btnBuscarTercero"));
        /*         * *********************************************************************************** */
        $this->configurarBotonesFormulario(false);
        // Si se ha enviado parámetros por post, se evalua si se va a modificar o a guardar
        if (count($this->request->getPost()) > 0) {
            try {
                $datos = $this->request->getPost();
                // Si se envia el id de la proveedor se modifica este.
                if ($datos["idProveedor"] != null) {
                    $returnCrud = $this->consultarMessage("errorUpdate");
                    if ($this->Proveedor->modificarProveedor($datos['idProveedor'], $datos['idDatoBasicoTercero'], $datos['email'], $datos['webSite']))
                        $returnCrud = $this->consultarMessage("okUpdate");
                }
                else {
                    $returnCrud = $this->consultarMessage("errorSave");
                    // se guarda la nueva proveedor
                    if ($this->Proveedor->guardarProveedor($datos['idDatoBasicoTercero'], $datos['email'], $datos['webSite'], $this->user_session->idUsuario))
                        $returnCrud = $this->consultarMessage("okSave");
                }
            } catch (\Exception $e) {
                $returnCrud = $this->consultarMessage($e->getMessage(), true);
            }
            return new ViewModel(array('form' => $this->form, 'msg' => $returnCrud));
        }
        // si existe el parametro $id  se consulta la proveedor y se carga el formulario.
        else if (isset($id)) {
            $this->Proveedor->consultarProveedorPorIdProveedor($this->params()->fromQuery('idProveedor'));
            $this->form->get("idProveedor")->setValue($this->Proveedor->getIdProveedor());

            //campos Tercero
            $this->form->get("idDatoBasicoTercero")->setValue($this->Proveedor->getIdDatoBasicoTercero());
            $descripcionTercero = $this->Proveedor->DatoBasicoTercero->getnit() . ' - ' . $this->Proveedor->DatoBasicoTercero->getdescripcion();
            $this->form->get("nombreTercero")->setValue($descripcionTercero);

            $this->form->get("email")->setValue($this->Proveedor->getEmail());
            $this->form->get("webSite")->setValue($this->Proveedor->getWebSite());
            $this->configurarBotonesFormulario(true);
        }
        return new ViewModel(array('form' => $this->form));
    }

    public function buscarAction() {
        $this->validarSession();
        // se obtiene el adapter
        $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');

        $this->form = new FormDatoBasicoTercero($this->getServiceLocator(), $this->getRequest()->getBaseUrl());
        /** Campos para saber en donde se deben devolver los valores de la busqueda * */
        $campoId = $this->params()->fromQuery('campoId', null) == null ? 'idProveedor' : $this->params()->fromQuery('campoId', null);
        $campoNombre = $this->params()->fromQuery('campoNombre', null) == null ? 'nombreProveedor' : $this->params()->fromQuery('campoNombre', null);

        /*         * ************************************************************************** */
        // Parametro que se utiliza para determinar si se va a redirigir a alguna vista en particular el id del saldo inventario seleccionado
        // Si el origen es saldoinventario/index, al dar click en la fila, esta debe redirigir al formualrio de saldo inventario
        $origen = $this->params()->fromQuery('origen', null);
        //**** OJO: la Uri se debe enviar a la busqueda *****//
        $Uri = $this->getRequest()->getRequestUri();

        $registros = array();
        if (count($this->request->getPost()) > 0) {
            $this->Proveedor = new Proveedor($this->dbAdapter);
            $datos = $this->request->getPost();

            $this->form->get("nit")->setValue($datos["nit"]);
            $this->form->get("descripcion")->setValue($datos["descripcion"]);

            $registros = $this->Proveedor->consultaAvanzadaProveedor($datos["nit"], $datos["descripcion"]);
        }

        // consultamos todos los Proveedores y los devolvemos a la vista    
        $view = new ViewModel(array(
            'form' => $this->form,
            'campoId' => $campoId,
            'campoNombre' => $campoNombre,
            'Uri' => $Uri,
            'origen' => $origen,
            'registros' => $registros));
        $view->setTerminal(true);
        return $view;
    }

    public function eliminarAction() {
        //$this->validarSession();
        $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
        $this->Proveedor = new Proveedor($this->dbAdapter);
        $id = $this->params()->fromQuery('id', null);
        if ($id != null) {
            $this->Proveedor->eliminarProveedor($id);
            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . '/admin/proveedor');
        }
    }

    private function consultarMessage($nameMensaje, $propio = false) {

        return FuncionesBase::consultarMessage($this->getServiceLocator()->get('Config'), $nameMensaje, $propio);
    }

    private function configurarBotonesFormulario($modificarBool) {

        FuncionesBase::configurarBotonesFormulario($this->form, $modificarBool);
    }

    private function validarSession() {
        $dif = date("H:i:s", strtotime("00:00:00") + strtotime(date('H:i:s')) - strtotime($this->user_session->timeLastActivity));
        $dif = explode(':', $dif)[0] * 3600 + explode(':', $dif)[1] * 60 + explode(':', $dif)[2];
        // Si el no existe la variable de sesión o  tiempo de inactividad sobrepasa al parametrizado  se cierra la sesión
        if (!$this->user_session->offsetExists('idUsuario') ||
                $dif > $this->user_session->getManager()->getConfig()->getRememberMeSeconds()) {
            $manager = $this->user_session->getManager();
            $manager->getStorage()->clear(); //delete all session values unless it is immutable
            unset($_SESSION['user']);
            return $this->redirect()->toRoute("login_admin");
        }
        $this->user_session->timeLastActivity = date('H:i:s');
    }

}

<?php

namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Admin\Form\FormPromocion;
use Application\Model\Entity\Promocion;
use Zend\Session\Container;
use Application\Model\Clases\FuncionesBase;

class PromocionController extends AbstractActionController {

    private $Promocion;
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
        $id = $this->params()->fromQuery('idPromocion', null);

        $this->Promocion = new Promocion($this->dbAdapter);
        $this->form = new FormPromocion($this->getServiceLocator(), $this->getRequest()->getBaseUrl());
        $this->form->get('btnBuscarSaldoInventario')->setAttribute('onClick', "showBusquedaOnModal(this,'" . $this->getRequest()->getBaseUrl() . "/admin/saldoinventario/buscar?campoValorVenta=valorAnterior','')");
        $this->configurarBotonesFormulario(false);
        // Si se ha enviado parámetros por post, se evalua si se va a modificar o a guardar
        if (count($this->request->getPost()) > 0) {
            try {
                $datos = $this->request->getPost();
                $fechaDesde = new \DateTime($datos['fechaDesde']);
                $fechaDesde = $fechaDesde->format('Y-m-d');
                $fechaHasta = new \DateTime($datos['fechaHasta']);
                $fechaHasta = $fechaHasta->format('Y-m-d');
                // Si se envia el id de la promocion se modifica este.
                if ($datos["idPromocion"] != null) {
                    $returnCrud = $this->consultarMessage("errorUpdate");
                    if ($this->Promocion->modificarPromocion($datos['idPromocion'], $datos['idSaldoInventario'], $datos['valorAnterior'], $datos['valorPromocion'], $fechaDesde, $fechaHasta, $datos['estado']))
                        $returnCrud = $this->consultarMessage("okUpdate");
                }
                else {
                    $returnCrud = $this->consultarMessage("errorSave");
                    // se guarda la nueva promocion
                    if ($this->Promocion->guardarPromocion($datos['idSaldoInventario'], $datos['valorAnterior'], $datos['valorPromocion'], $fechaDesde, $fechaHasta, $datos['estado'], $this->user_session->idUsuario))
                        $returnCrud = $this->consultarMessage("okSave");
                }
            } catch (\Exception $e) {
                $returnCrud = $this->consultarMessage($e->getMessage(), true);
            }
            return new ViewModel(array('form' => $this->form, 'msg' => $returnCrud));
        }
        // si existe el parametro $id  se consulta la promocion y se carga el formulario.
        else if (isset($id)) {
            $this->Promocion->consultarPromocionPorIdPromocion($id);
            $this->form->get("idPromocion")->setValue($this->Promocion->getIdPromocion());
            $this->form->get("nombreProducto")->setValue($this->Promocion->nombreProducto);
            $this->form->get("idSaldoInventario")->setValue($this->Promocion->getIdSaldoInventario());
            $this->form->get("valorAnterior")->setValue($this->Promocion->getValorAnterior());
            $this->form->get("valorPromocion")->setValue($this->Promocion->getValorPromocion());

            $fechaDesde = new \DateTime($this->Promocion->getFechaDesde());
            $fechaDesde = $fechaDesde->format('Y-m-d');

            $fechaHasta = new \DateTime($this->Promocion->getFechaHasta());
            $fechaHasta = $fechaHasta->format('Y-m-d');

            $this->form->get("fechaDesde")->setValue($fechaDesde);
            $this->form->get("fechaHasta")->setValue($fechaHasta);
            $this->form->get("estado")->setValue($this->Promocion->getEstado());
            $this->configurarBotonesFormulario(true);
        }
        return new ViewModel(array('form' => $this->form));
    }

    public function buscarAction() {
        $this->validarSession();
        // se obtiene el adapter
        $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
        $this->Promocion = new Promocion($this->dbAdapter);
        $this->form = new FormPromocion($this->getServiceLocator(), $this->getRequest()->getBaseUrl());
        //$this->form->get('estado')->setAttributes(array('id'=>'estadoBusqueda','name'=>'estadoBusqueda'));
        //**** OJO: la Uri se debe enviar a la busqueda *****//
        $Uri = $this->getRequest()->getRequestUri();
        $origen = $this->params()->fromQuery('origen', null);

        $registros = array();
        if (count($this->request->getPost()) > 0) {
            $datos = $this->request->getPost();
            $this->form->get("idProducto")->setValue($datos['idProducto']);
            $this->form->get("nombreProducto")->setValue($datos['nombreProducto']);
            $this->form->get("idProveedor")->setValue($datos['idProveedor']);
            $this->form->get("nombreProveedor")->setValue($datos['nombreProveedor']);
            //$this->form->get("estado")->setValue($datos['estadoBusqueda']);
            $registros = $this->Promocion->consultaAvanzadaPromocion($datos['idProducto'], $datos['idProveedor'], null);
        }

        // consultamos todas las promocions y los devolvemos a la vista    
        $view = new ViewModel(array('form' => $this->form,
            'Uri' => $Uri,
            'origen' => $origen,
            'registros' => $registros));
        $view->setTerminal(true);
        return $view;
    }

    public function eliminarAction() {
        //$this->validarSession();
        $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
        $this->Promocion = new Promocion($this->dbAdapter);
        $id = $this->params()->fromQuery('id', null);
        if ($id != null) {
            $this->Promocion->eliminarPromocion($id);
            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . '/admin/promocion');
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

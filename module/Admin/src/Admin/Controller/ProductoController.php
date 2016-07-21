<?php

namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Admin\Form\FormBase;
use Admin\Form\FormProducto;
use Application\Model\Entity\Producto;
use Application\Model\Clases\FuncionesBase;
use Zend\Session\Container;

class ProductoController extends AbstractActionController {

    private $Producto;
    private $form;
    private $user_session;
    
    private $formats;
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
        $id = $this->params()->fromQuery('idProducto', null);

        $this->Producto = new Producto($this->dbAdapter);
        $this->form = new FormProducto($this->getServiceLocator(), $this->getRequest()->getBaseUrl());
        /*         * *********************************************************************************** */
        // Se agregan el botones de buscar  marca y categoria al formualrio saldo inventario
        /*         * *********************************************************************************** */
        $formBase = new FormBase($this->getServiceLocator(), $this->getRequest()->getBaseUrl());
        $this->form->add($formBase->get("btnBuscarMarca"));
        $this->form->add($formBase->get("btnBuscarCategoria"));
        /*         * *********************************************************************************** */
        $this->configurarBotonesFormulario(false);
        // Si se ha enviado parámetros por post, se evalua si se va a modificar o a guardar
        if (count($this->request->getPost()) > 0) {
            $this->formats = $this->getServiceLocator()->get('Config')['formats'];
            try {
                $datos = $this->request->getPost();
                // Si se envia el id de la producto se modifica este.
                if ($datos["idProducto"] != null) {
                    $returnCrud = $this->consultarMessage("errorUpdate");
                    if ($this->Producto->modificarProducto($datos['idProducto'], $datos['idMarca'], $datos['idCategoria'], $datos['codigo'], $datos['nombre'], $datos['referencia'], $datos['descripcion'], $datos['especificacion']))
                        $returnCrud = $this->consultarMessage("okUpdate");
                }
                else {
                    $returnCrud = $this->consultarMessage("errorSave");
                    // se guarda la nueva producto
                    if ($this->Producto->guardarProducto($datos['idMarca'], $datos['idCategoria'], $datos['codigo'], $datos['nombre'], $datos['referencia'], $datos['descripcion'], $datos['especificacion'], $this->user_session->idUsuario, date($this->formats['datetime'])))
                        $returnCrud = $this->consultarMessage("okSave");
                }
            } catch (\Exception $e) {
                $returnCrud = $this->consultarMessage($e->getMessage(), true);
            }
            return new ViewModel(array('form' => $this->form, 'msg' => $returnCrud));
        }
        // si existe el parametro $id  se consulta la producto y se carga el formulario.
        else if (isset($id)) {
            $this->Producto->consultarProductoPorIdProducto($id);
            $this->form->get("idProducto")->setValue($this->Producto->getIdProducto());
            $this->form->get("idMarca")->setValue($this->Producto->getIdMarca());
            $this->form->get("idCategoria")->setValue($this->Producto->getIdCategoria());
            $this->form->get("codigo")->setValue($this->Producto->getCodigo());
            $this->form->get("nombre")->setValue($this->Producto->getNombre());
            $this->form->get("referencia")->setValue($this->Producto->getReferencia());
            $this->form->get("descripcion")->setValue($this->Producto->getDescripcion());
            $this->form->get("especificacion")->setValue($this->Producto->getEspecificacion());
            $this->configurarBotonesFormulario(true);
        }
        return new ViewModel(array('form' => $this->form));
    }

    public function buscarAction() {
        $this->validarSession();
        // se obtiene el adapter
        $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
        $this->form = new FormProducto($this->getServiceLocator(), $this->getRequest()->getBaseUrl());
        /** Campos para saber en donde se deben devolver los valores de la busqueda * */
        $campoId = $this->params()->fromQuery('campoId', null) == null ? 'idProducto' : $this->params()->fromQuery('campoId', null);
        $campoNombre = $this->params()->fromQuery('campoNombre', null) == null ? 'nombreProducto' : $this->params()->fromQuery('campoNombre', null);
        /*         * ************************************************************************** */
        // Parametro que se utiliza para determinar si se va a redirigir a alguna vista en particular el id del saldo inventario seleccionado
        // Si el origen es saldoinventario/index, al dar click en la fila, esta debe redirigir al formualrio de saldo inventario
        $origen = $this->params()->fromQuery('origen', null);
        //**** OJO: la Uri se debe enviar a la busqueda *****//
        $Uri = $this->getRequest()->getRequestUri();

        $registros = array();
        if (count($this->request->getPost()) > 0) {
            $this->Producto = new Producto($this->dbAdapter);
            $datos = $this->request->getPost();
            $this->form->get("idMarca")->setValue($datos['idMarca']);
            $this->form->get("idCategoria")->setValue($datos['idCategoria']);
            $this->form->get("referencia")->setValue($datos['referencia']);
            $this->form->get("codigo")->setValue($datos['codigo']);
            $this->form->get("nombre")->setValue($datos['nombre']);
            $registros = $this->Producto->consultaAvanzadaProducto($datos['idMarca'], $datos['idCategoria'], $datos['referencia'], $datos['codigo'], $datos['nombre']);
        }

        // consultamos todos los productos y los devolvemos a la vista    
        $view = new ViewModel(array('form' => $this->form,
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
        $this->Producto = new Producto($this->dbAdapter);
        $id = $this->params()->fromQuery('id', null);
        if ($id != null) {
            $this->Producto->eliminarProducto($id);
            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . '/admin/producto');
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

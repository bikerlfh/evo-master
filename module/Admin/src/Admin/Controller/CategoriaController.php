<?php

namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Admin\Form\FormCategoria;
use Application\Model\Entity\Categoria;
use Zend\Session\Container;
use Application\Model\Clases\FuncionesBase;

class CategoriaController extends AbstractActionController {

    private $Categoria;
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
        $id = $this->params()->fromQuery('idCategoria', null);

        $this->Categoria = new Categoria($this->dbAdapter);
        $this->form = new FormCategoria($this->getServiceLocator(), $this->getRequest()->getBaseUrl());
        $this->configurarBotonesFormulario(false);
        // Si se ha enviado parámetros por post, se evalua si se va a modificar o a guardar
        if (count($this->request->getPost()) > 0) {
            $datos = $this->request->getPost();

            try {

                // Si se envia el id de la categoria se modifica este.
                if ($datos["idCategoria"] != null) {
                    $returnCrud = $this->consultarMessage("errorUpdate");
                    if ($this->Categoria->modificarCategoria($datos['idCategoria'], $datos['idCategoriaCentral'], $datos['codigo'], $datos['descripcion']))
                        $returnCrud = $this->consultarMessage("okUpdate");
                }
                else {
                    $returnCrud = $this->consultarMessage("errorSave");
                    // se guarda la nueva categoria
                    if ($this->Categoria->guardarCategoria($datos['idCategoriaCentral'], $datos['codigo'], $datos['descripcion']))
                        $returnCrud = $this->consultarMessage("okSave");
                }
            } catch (\Exception $e){
                $returnCrud = $this->consultarMessage($e->getMessage(),true);
            }
            return new ViewModel(array('form' => $this->form, 'msg' => $returnCrud));
        }
        // si existe el parametro $id  se consulta la categoria y se carga el formulario.
        else if (isset($id)) {
            $this->Categoria->consultarCategoriaPorIdCategoria($this->params()->fromQuery('idCategoria'));
            $this->form->get("idCategoria")->setValue($this->Categoria->getIdCategoria());
            $this->form->get("idCategoriaCentral")->setValue($this->Categoria->getIdCategoriaCentral());
            $this->form->get("codigo")->setValue($this->Categoria->getCodigo());
            $this->form->get("descripcion")->setValue($this->Categoria->getDescripcion());
            $this->configurarBotonesFormulario(true);
        }
        return new ViewModel(array('form' => $this->form));
    }

    public function buscarAction() {
        $this->validarSession();
        // se obtiene el adapter
        $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
        $this->Categoria = new Categoria($this->dbAdapter);

        // Parametro que se utiliza para determinar si se va a redirigir a alguna vista en particular el id del saldo inventario seleccionado
        // Si el origen es saldoinventario/index, al dar click en la fila, esta debe redirigir al formualrio de saldo inventario
        $origen = $this->params()->fromQuery('origen', null);
        //**** OJO: la Uri se debe enviar a la busqueda *****//
        $Uri = $this->getRequest()->getRequestUri();

        // consultamos todas las categorias y los devolvemos a la vista    
        $view = new ViewModel(array('Uri' => $Uri,
            'origen' => $origen,
            'registros' => $this->Categoria->consultarTodoCategoria()));
        $view->setTerminal(true);
        return $view;
    }

    public function eliminarAction() {
        //$this->validarSession();
        $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
        $this->Categoria = new Categoria($this->dbAdapter);
        $id = $this->params()->fromQuery('id', null);
        if ($id != null) {
            $this->Categoria->eliminarCategoria($id);
            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . '/admin/categoria');
        }
    }

    //Si se envia el parámetro propio true este tomara como mensaje nameMensaje
    private function consultarMessage($nameMensaje,$propio = false) {
        
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

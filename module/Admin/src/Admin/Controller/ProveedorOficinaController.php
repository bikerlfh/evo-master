<?php
namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Admin\Form\FormBase;
use Admin\Form\FormProveedorOficina;
use Application\Model\Entity\ProveedorOficina;
use Zend\Session\Container;


class ProveedorOficinaController extends AbstractActionController
{
    private $ProveedorOficina;
    private $form;
    
    private $user_session;
    public function __construct() {
        $this->user_session = new Container();
    }
    
    public function indexAction()
    {
        $this->validarSession();
        // se asigna el layout admin
        $this->layout('layout/admin'); 
        // se obtiene el adapter
        $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
        // Parametro pasado por get, con el cual se sabe si se seleccionó objeto para modificar
        $id=$this->params()->fromQuery('id',null);
        
        $this->ProveedorOficina = new ProveedorOficina($this->dbAdapter);
        $this->form = new FormProveedorOficina($this->getServiceLocator(),$this->getRequest()->getBaseUrl());
        /**************************************************************************************/
        // Se agregan el botones de buscar  proveedor y municipio al formualrio saldo inventario
        /**************************************************************************************/
        $formBase = new FormBase($this->getServiceLocator(),$this->getRequest()->getBaseUrl());
        $this->form->add($formBase->get("btnBuscarProveedor"));
        $this->form->add($formBase->get("btnBuscarMunicipio"));
        /**************************************************************************************/
        $this->configurarBotonesFormulario(false);
        // Si se ha enviado parámetros por post, se evalua si se va a modificar o a guardar
        if(count($this->request->getPost())>0)
        {
            $datos=$this->request->getPost();
            // Si se envia el id de la proveedoroficina se modifica este.
            if ($datos["idProveedorOficina"] != null) 
            {
                $returnCrud=$this->consultarMessage("errorUpdate");
                if($this->ProveedorOficina->modificarProveedorOficina($datos['idProveedorOficina'],$datos['idProveedor'],$datos['idMunicipio'],$datos['email'],$datos['webSite'],$datos['direccion'],$datos['telefono']))
                    $returnCrud=$this->consultarMessage("okUpdate");
            }
            else
            {
                $returnCrud=$this->consultarMessage("errorSave");
                // se guarda la nueva proveedoroficina
                if($this->ProveedorOficina->guardarProveedorOficina($datos['idProveedor'],$datos['idMunicipio'],$datos['email'],$datos['webSite'],$datos['direccion'],$datos['telefono']))
                    $returnCrud=$this->consultarMessage("okSave");
            }
                return new ViewModel(array('form'=>$this->form,'msg'=>$returnCrud,'registros'=>$this->ProveedorOficina->consultarTodoProveedorOficina()));
        }
        // si existe el parametro $id  se consulta la proveedoroficina y se carga el formulario.
        else if(isset($id))
        {
            $this->ProveedorOficina->consultarProveedorOficinaPorIdProveedorOficina($this->params()->fromQuery('id'));
            $this->form->get("idProveedorOficina")->setValue($this->ProveedorOficina->getIdProveedorOficina());
            $this->form->get("idProveedor")->setValue($this->ProveedorOficina->getIdProveedor());
            $this->form->get("idMunicipio")->setValue($this->ProveedorOficina->getIdMunicipio());
            $this->form->get("email")->setValue($this->ProveedorOficina->getEmail());
            $this->form->get("webSite")->setValue($this->ProveedorOficina->getWebSite());
            $this->form->get("direccion")->setValue($this->ProveedorOficina->getDireccion());
            $this->form->get("telefono")->setValue($this->ProveedorOficina->getTelefono());
            $this->configurarBotonesFormulario(true);
        }
        return new ViewModel(array('form'=>$this->form,'registros'=>$this->ProveedorOficina->consultarTodoProveedorOficina()));
    }
    public function buscarAction()
    {
        $this->validarSession();
        // se obtiene el adapter
        $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
        $this->ProveedorOficina = new ProveedorOficina($this->dbAdapter);
        // consultamos todos los Proveedores y los devolvemos a la vista    
        $view = new ViewModel(array('registros'=>$this->ProveedorOficina->consultarTodoProveedorOficina()));
        $view->setTerminal(true);
        return $view;
    }
    public function eliminarAction()
    {
        //$this->validarSession();
        $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
        $this->ProveedorOficina = new ProveedorOficina($this->dbAdapter);
        $id=$this->params()->fromQuery('id',null);
        if($id != null)
        {
            $this->ProveedorOficina->eliminarProveedorOficina($id);
            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl().'/admin/proveedoroficina');
        }
    }
    private function consultarMessage($nameMensaje)
    {
        $serviceLocator=$this->getServiceLocator()->get('Config');
        $mensaje=$serviceLocator['MsgCrud'];
        $mensaje= $mensaje[$nameMensaje];
        return $mensaje['function']."('".$mensaje['title']."','".$mensaje['message']."');";
    }
    private function configurarBotonesFormulario($modificarBool)
    {
        if ($modificarBool == true)
        {
            $this->form->get("btnGuardar")->setAttribute("type", "hidden");
            $this->form->get("btnModificar")->setAttribute("type", "submit");
            $this->form->get("btnEliminar")->setAttribute("type", "button");
          
        }
        else
        {
            $this->form->get("btnGuardar")->setAttribute("type", "submit");
            $this->form->get("btnModificar")->setAttribute("type", "hidden");
            $this->form->get("btnEliminar")->setAttribute("type", "hidden");
        }
    }
    
    private function validarSession()
    {
        //<== Si no existe la session se redirge al login ==>
        if (!isset($_SESSION['user'])) {
            return $this->redirect()->toUrl(str_replace("/public","", $this->getRequest()->getBaseUrl()).'/admin/login');
        }
        $this->user_session = $_SESSION['user'];
    }
}
<?php
namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Admin\Form\FormBase;
use Admin\Form\FormProveedor;
use Application\Model\Entity\Proveedor;
use Zend\Session\Container;


class ProveedorController extends AbstractActionController
{
    private $Proveedor;
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
        
        $this->Proveedor = new Proveedor($this->dbAdapter);
        $this->form = new FormProveedor($this->getServiceLocator(),$this->getRequest()->getBaseUrl());
        /**************************************************************************************/
        // Se agregan el boton de buscar Tercero al formualrio saldo inventario
        /**************************************************************************************/
        $formBase = new FormBase($this->getServiceLocator(),$this->getRequest()->getBaseUrl());
        $this->form->add($formBase->get("btnBuscarTercero"));
        /**************************************************************************************/
        $this->configurarBotonesFormulario(false);
        // Si se ha enviado parámetros por post, se evalua si se va a modificar o a guardar
        if(count($this->request->getPost())>0)
        {
            $datos=$this->request->getPost();
            // Si se envia el id de la proveedor se modifica este.
            if ($datos["idProveedor"] != null) 
            {
                $returnCrud=$this->consultarMessage("errorUpdate");
                if($this->Proveedor->modificarProveedor($datos['idProveedor'],$datos['idDatoBasicoTercero'],$datos['email'],$datos['numCuentaBancaria'],$datos['idTipoCuenta'],$datos['idViaPago']))
                    $returnCrud=$this->consultarMessage("okUpdate");
            }
            else
            {
                $returnCrud=$this->consultarMessage("errorSave");
                // se guarda la nueva proveedor
                if($this->Proveedor->guardarProveedor($datos['idDatoBasicoTercero'],$datos['email'],$datos['numCuentaBancaria'],$datos['idTipoCuenta'],$datos['idViaPago'], $this->user_session->idUsuario))
                    $returnCrud=$this->consultarMessage("okSave");
            }
                return new ViewModel(array('form'=>$this->form,'msg'=>$returnCrud,'registros'=>$this->Proveedor->consultarTodoProveedor()));
        }
        // si existe el parametro $id  se consulta la proveedor y se carga el formulario.
        else if(isset($id))
        {
            $this->Proveedor->consultarProveedorPorIdProveedor($this->params()->fromQuery('id'));
            $this->form->get("idProveedor")->setValue($this->Proveedor->getIdProveedor());
            $this->form->get("idDatoBasicoTercero")->setValue($this->Proveedor->getIdDatoBasicoTercero());
            $this->form->get("idViaPago")->setValue($this->Proveedor->getIdViaPago());
            $this->form->get("idTipoCuenta")->setValue($this->Proveedor->getIdTipoCuenta());
            $this->form->get("email")->setValue($this->Proveedor->getEmail());
            $this->form->get("numCuentaBancaria")->setValue($this->Proveedor->getNumCuentaBancaria());
            $this->configurarBotonesFormulario(true);
        }
        return new ViewModel(array('form'=>$this->form,'registros'=>$this->Proveedor->consultarTodoProveedor()));
    }
    
    public function buscarAction()
    {
        $this->validarSession();
        // se obtiene el adapter
        $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
        $this->Proveedor = new Proveedor($this->dbAdapter);
        // consultamos todos los Proveedores y los devolvemos a la vista    
        $view = new ViewModel(array('registros'=>$this->Proveedor->consultarTodoProveedor()));
        $view->setTerminal(true);
        return $view;
    }
    
    public function eliminarAction()
    {
        //$this->validarSession();
        $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
        $this->Proveedor = new Proveedor($this->dbAdapter);
        $id=$this->params()->fromQuery('id',null);
        if($id != null)
        {
            $this->Proveedor->eliminarProveedor($id);
            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl().'/admin/proveedor');
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
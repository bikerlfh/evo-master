<?php
namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Admin\Form\FormBase;
use Admin\Form\FormProveedorCuenta;
use Application\Model\Entity\ProveedorCuenta;
use Zend\Session\Container;


class ProveedorCuentaController extends AbstractActionController
{
    private $ProveedorCuenta;
    private $form;
    
    private $user_session;
    public function __construct() {
        $this->user_session = new Container('user');
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
        
        $this->ProveedorCuenta = new ProveedorCuenta($this->dbAdapter);
        $this->form = new FormProveedorCuenta($this->getServiceLocator(),$this->getRequest()->getBaseUrl());
        /**************************************************************************************/
        // Se agregan el boton de buscar  proveedor oficina al formualrio saldo inventario
        /**************************************************************************************/
        $formBase = new FormBase($this->getServiceLocator(),$this->getRequest()->getBaseUrl());
        $this->form->add($formBase->get("btnBuscarProveedorOficina"));
        /**************************************************************************************/
        $this->configurarBotonesFormulario(false);
        // Si se ha enviado parámetros por post, se evalua si se va a modificar o a guardar
        if(count($this->request->getPost())>0)
        {
            $datos=$this->request->getPost();
            // Si se envia el id de la proveedorcuenta se modifica este.
            if ($datos["idProveedorCuenta"] != null) 
            {
                $returnCrud=$this->consultarMessage("errorUpdate");
                if($this->ProveedorCuenta->modificarProveedorCuenta($datos['idProveedorCuenta'],$datos['idProveedorOficina'],$datos['numeroCuentaBancaria'],$datos['idTipoCuenta'],$datos['idViaPago']))
                    $returnCrud=$this->consultarMessage("okUpdate");
            }
            else
            {
                $returnCrud=$this->consultarMessage("errorSave");
                // se guarda la nueva proveedorcuenta
                if($this->ProveedorCuenta->guardarProveedorCuenta($datos['idProveedorOficina'],$datos['numeroCuentaBancaria'],$datos['idTipoCuenta'],$datos['idViaPago']))
                    $returnCrud=$this->consultarMessage("okSave");
            }
                return new ViewModel(array('form'=>$this->form,'msg'=>$returnCrud,'registros'=>$this->ProveedorCuenta->consultarTodoProveedorCuenta()));
        }
        // si existe el parametro $id  se consulta la proveedorcuenta y se carga el formulario.
        else if(isset($id))
        {
            $this->ProveedorCuenta->consultarProveedorCuentaPorIdProveedorCuenta($this->params()->fromQuery('id'));
            $this->form->get("idProveedorCuenta")->setValue($this->ProveedorCuenta->getIdProveedorCuenta());
            $this->form->get("idProveedorOficina")->setValue($this->ProveedorCuenta->getIdProveedorOficina());
            $this->form->get("numeroCuentaBancaria")->setValue($this->ProveedorCuenta->getNumeroCuentaBancaria());
            $this->form->get("idTipoCuenta")->setValue($this->ProveedorCuenta->getIdTipoCuenta());
            $this->form->get("idViaPago")->setValue($this->ProveedorCuenta->getIdViaPago());
            $this->configurarBotonesFormulario(true);
        }
        return new ViewModel(array('form'=>$this->form,'registros'=>$this->ProveedorCuenta->consultarTodoProveedorCuenta()));
    }
    
    public function eliminarAction()
    {
        //$this->validarSession();
        $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
        $this->ProveedorCuenta = new ProveedorCuenta($this->dbAdapter);
        $id=$this->params()->fromQuery('id',null);
        if($id != null)
        {
            $this->ProveedorCuenta->eliminarProveedorCuenta($id);
            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl().'/admin/proveedorcuenta');
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
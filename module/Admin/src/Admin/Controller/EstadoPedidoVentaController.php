<?php
namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Admin\Form\FormEstadoPedidoVenta;
use Application\Model\Entity\EstadoPedidoVenta;
use Zend\Session\Container;

class EstadoPedidoVentaController extends AbstractActionController
{
    private $EstadoPedidoVenta;
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
        $id=$this->params()->fromQuery('idEstadoPedidoVenta',null);
        
        $this->EstadoPedidoVenta = new EstadoPedidoVenta($this->dbAdapter);
        $this->form = new FormEstadoPedidoVenta($this->getServiceLocator(),$this->getRequest()->getBaseUrl());
        $this->configurarBotonesFormulario(false);
        // Si se ha enviado parámetros por post, se evalua si se va a modificar o a guardar
        if(count($this->request->getPost())>0)
        {
            $datos=$this->request->getPost();
            // Si se envia el id de la categoria se modifica este.
            if ($datos["idEstadoPedidoVenta"] != null) 
            {
                $returnCrud=$this->consultarMessage("errorUpdate");
                if($this->EstadoPedidoVenta->modificarEstadoPedidoVenta($datos['idEstadoPedidoVenta'],$datos['codigo'],$datos['descripcion']))
                    $returnCrud=$this->consultarMessage("okUpdate");
            }
            else
            {
                $returnCrud=$this->consultarMessage("errorSave");
                // se guarda la nueva categoria
                if($this->EstadoPedidoVenta->guardarEstadoPedidoVenta($datos['codigo'],$datos['descripcion']))
                    $returnCrud=$this->consultarMessage("okSave");
            }
            return new ViewModel(array('form'=>$this->form,'msg'=>$returnCrud));
        }
        // si existe el parametro $id  se consulta el estado pedido y se carga el formulario.
        else if(isset($id))
        {
            $this->EstadoPedidoVenta->consultarEstadoPedidoVentaPorIdEstadoPedidoVenta($this->params()->fromQuery('idEstadoPedidoVenta'));
            $this->form->get("idEstadoPedidoVenta")->setValue($this->EstadoPedidoVenta->getIdEstadoPedidoVenta());
            $this->form->get("codigo")->setValue($this->EstadoPedidoVenta->getCodigo());
            $this->form->get("descripcion")->setValue($this->EstadoPedidoVenta->getDescripcion());
            $this->configurarBotonesFormulario(true);
        }
        return new ViewModel(array('form'=>$this->form));
    }
    
    public function buscarAction()
    {
        $this->validarSession();
        // se obtiene el adapter
        $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
        $this->EstadoPedidoVenta = new EstadoPedidoVenta($this->dbAdapter);
        
       // Parametro que se utiliza para determinar si se va a redirigir a alguna vista en particular el id del saldo inventario seleccionado
        // Si el origen es saldoinventario/index, al dar click en la fila, esta debe redirigir al formualrio de saldo inventario
        $origen = $this->params()->fromQuery('origen', null);
        //**** OJO: la Uri se debe enviar a la busqueda *****//
        $Uri = $this->getRequest()->getRequestUri();
        
        // consultamos todos los municipio y los devolvemos a la vista    
        $view = new ViewModel(array('Uri'=> $Uri,
                                    'origen'=> $origen,
                                    'registros'=>$this->EstadoPedidoVenta->consultarTodoEstadoPedidoVenta()));
        $view->setTerminal(true);
        return $view;
    }
    
    public function eliminarAction()
    {
        $this->validarSession();
        $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
        $this->EstadoPedidoVenta = new EstadoPedidoVenta($this->dbAdapter);
        $id=$this->params()->fromQuery('id',null);
        if($id != null)
        {
            $this->EstadoPedidoVenta->eliminarEstadoPedidoVenta($id);
            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl().'/admin/estadopedidoventa');
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
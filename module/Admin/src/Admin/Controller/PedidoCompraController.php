<?php
namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Admin\Form\FormBase;
use Admin\Form\FormPedidoCompra;
use Application\Model\Entity\PedidoCompra;
use Zend\Session\Container;


class PedidoCompraController extends AbstractActionController
{
    private $PedidoCompra;
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
        
        $this->PedidoCompra = new PedidoCompra($this->dbAdapter);
        $this->form = new FormPedidoCompra($this->getServiceLocator(),$this->getRequest()->getBaseUrl());
        /**************************************************************************************/
        // Se agregan el botones de buscar  marca y categoria al formualrio saldo inventario
        /**************************************************************************************/
        $formBase = new FormBase($this->getServiceLocator(),$this->getRequest()->getBaseUrl());
        $this->form->add($formBase->get("btnBuscarProveedor"));
        $this->form->add($formBase->get("btnBuscarProducto"));
        /**************************************************************************************/
        $this->configurarBotonesFormulario(false);
        // Si se ha enviado parámetros por post, se evalua si se va a modificar o a guardar
        if(count($this->request->getPost())>0)
        {
            $datos=$this->request->getPost();
            // Si se envia el id de la pedidocompra se modifica este.
            if ($datos["idPedidoCompra"] != null) 
            {
                $returnCrud=$this->consultarMessage("errorUpdate");
                if($this->PedidoCompra->modificarPedidoCompra($datos['idPedidoCompra'],$datos['idMarca'],$datos['idCategoria'],$datos['codigo'],$datos['nombre'],$datos['referencia'],$datos['descripcion'],$datos['especificacion']))
                    $returnCrud=$this->consultarMessage("okUpdate");
            }
            else
            {
                $returnCrud=$this->consultarMessage("errorSave");
                // se guarda la nueva pedidocompra
                if($this->PedidoCompra->guardarPedidoCompra($datos['idMarca'],$datos['idCategoria'],$datos['codigo'],$datos['nombre'],$datos['referencia'],$datos['descripcion'],$datos['especificacion'], $this->user_session->idUsuario,  date('d-m-Y H:i:s')))
                    $returnCrud=$this->consultarMessage("okSave");
            }
                return new ViewModel(array('form'=>$this->form,'msg'=>$returnCrud,'registros'=>$this->PedidoCompra->consultarTodoPedidoCompra()));
        }
        // si existe el parametro $id  se consulta la pedidocompra y se carga el formulario.
        else if(isset($id))
        {
            $this->PedidoCompra->consultarPedidoCompraPorIdPedidoCompra($this->params()->fromQuery('id'));
            $this->form->get("idPedidoCompra")->setValue($this->PedidoCompra->getIdPedidoCompra());
            $this->form->get("numeroPedido")->setValue($this->PedidoCompra->getIdMarca());
            $this->form->get("idCategoria")->setValue($this->PedidoCompra->getIdCategoria());
            $this->form->get("codigo")->setValue($this->PedidoCompra->getCodigo());
            $this->form->get("nombre")->setValue($this->PedidoCompra->getNombre());
            $this->form->get("referencia")->setValue($this->PedidoCompra->getReferencia());
            $this->form->get("descripcion")->setValue($this->PedidoCompra->getDescripcion());
            $this->form->get("especificacion")->setValue($this->PedidoCompra->getEspecificacion());
            $this->configurarBotonesFormulario(true);
        }
        return new ViewModel(array('form'=>$this->form,'registros'=>$this->PedidoCompra->consultarTodoPedidoCompra()));
    }
    public function buscarAction()
    {
        $this->validarSession();
        // se obtiene el adapter
        $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
        $this->PedidoCompra = new PedidoCompra($this->dbAdapter);
        // consultamos todos los pedidocompras y los devolvemos a la vista    
        $view = new ViewModel(array('registros'=>$this->PedidoCompra->consultarTodoPedidoCompra()));
        $view->setTerminal(true);
        return $view;
    }
    public function eliminarAction()
    {
        //$this->validarSession();
        $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
        $this->PedidoCompra = new PedidoCompra($this->dbAdapter);
        $id=$this->params()->fromQuery('id',null);
        if($id != null)
        {
            $this->PedidoCompra->eliminarPedidoCompra($id);
            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl().'/admin/pedidocompra');
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
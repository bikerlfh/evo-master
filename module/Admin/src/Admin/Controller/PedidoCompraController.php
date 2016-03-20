<?php
namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Admin\Form\FormPedidoCompra;
use Application\Model\Entity\PedidoCompra;
use Application\Model\Entity\PedidoCompraPosicion;
use Zend\Session\Container;


class PedidoCompraController extends AbstractActionController
{
    private $PedidoCompra;
    //private $PedidoCompraPosicion;
    private $form;
    
    private $user_session;
    public function __construct() {
        $this->user_session = new Container();
    }
    
    public function solicitudAction()
    {
        //$param = $this->getEvent()->getRouteMatch()->getParams();
        /*
        if ($this->getSessContainer()->idUsuario>0){
            return $this->forward()->dispatch('Test\Controller\Auth', array('action'=>'loginpage'));
         }*/
        $this->validarSession();
        // se asigna el layout admin
        $this->layout('layout/admin'); 
        // se obtiene el adapter
        $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
        // Parametro pasado por get, con el cual se sabe si se seleccionó objeto para modificar
        $id=$this->params()->fromQuery('idPedidoCompra',null);
        
        $this->PedidoCompra = new PedidoCompra($this->dbAdapter);
        $this->form = new FormPedidoCompra($this->getServiceLocator(),$this->getRequest()->getBaseUrl());
        $this->configurarBotonesFormulario(false);
        // Si se ha enviado parámetros por post, se evalua si se va a modificar o a guardar
        if(count($this->request->getPost())>0)
        {
            $urlDocumentoPago = null;
            // Guardamos la imagen, si esta viene.
            $request = $this->getRequest();
            if ($request->isPost()) 
            {
                // Make certain to merge the files info!
                $data = array_merge_recursive(
                    $request->getPost()->toArray(),
                    $request->getFiles()->toArray()
                );

                $this->form->setData($data);
                if ($this->form->isValid()) 
                {
                    $data = $this->form->getData();
                    $urlDocumentoPago = $data['file-documentoPago']['tmp_name'];
                }
            }
            $datos=$this->request->getPost();
            // Este ciclo muestra todas las claves del array asociativo
            foreach($datos as $key => $value)
            {
                // Se evalua si la clave es un idProducto
                if (strpos($key, 'idProducto') !== FALSE)
                {
                    $indice =  split('idProducto',$key)[1];
                    $PedidoCompraPosicion = new PedidoCompraPosicion($this->dbAdapter);
                    $PedidoCompraPosicion->setIdProducto($datos[$key]);
                    $PedidoCompraPosicion->setCantidad($datos['cantidad'.$indice]);
                    $PedidoCompraPosicion->setValorCompra($datos['valorCompra'.$indice]);
                    $PedidoCompraPosicion->setIdUsuarioCreacion($this->user_session->idUsuario);                        
                    array_push($this->PedidoCompra->PedidoCompraPosicion, $PedidoCompraPosicion);
                }
            }
            $returnCrud=$this->consultarMessage("errorSave");
            // Se guarda el nuevo pedido compra con sus posiciones.
            $resultado = $this->PedidoCompra->guardarPedidoCompra($datos['idEstadoPedido'],$datos['idProveedor'],$urlDocumentoPago, $this->user_session->idUsuario);
            if($resultado == 'true'){
                $returnCrud=$this->consultarMessage("okSave");
            }
            
            return new ViewModel(array('form'=>$this->form,'msg'=>$returnCrud));
        }
        return new ViewModel(array('form'=>$this->form));
    }
    public function buscarAction()
    {
        $this->validarSession();
        /*
        $idPedidoCompra=$this->params()->fromQuery('idPedidoCompra',null);
        if ($idPedidoCompra != null) {
            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl().'/admin/pedidocompra/index?id='+$idPedidoCompra);
            return $this->redirect()->toRoute('buscarPedidoCompra',array('idPedidoCompra'=>  $idPedidoCompra));
        }*/
        // se obtiene el adapter
        $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
        
        $this->form = new FormPedidoCompra($this->getServiceLocator(),$this->getRequest()->getBaseUrl());
        $this->form->setAttribute('name' , 'frmBuscarPedidoCompra');
        /** Campos para saber en donde se deben devolver los valores de la busqueda **/
        $campoId=$this->params()->fromQuery('campoId',null) == null? 'idPedidoCompra':$this->params()->fromQuery('campoId',null);
        $campoNombre=$this->params()->fromQuery('campoNombre',null)== null?'numeroPedido':$this->params()->fromQuery('campoNombre',null);
        
        $registros = array();
        if(count($this->request->getPost()) > 0)
        {
            $datos = $this->request->getPost();
            $this->PedidoCompra = new PedidoCompra($this->dbAdapter);
            $this->form->get("numeroPedido")->setValue($datos["numeroPedido"]);
            //$this->form->get("idProveedor")->setValue($datos["idProveedor"]);
            //$this->form->get("nombreProveedor")->setValue($datos["nombreProveedor"]);
            $this->form->get("idEstadoPedidoBusqueda")->setValue($datos["idEstadoPedido"]);            
            $registros = $this->PedidoCompra->consultaAvanzadaPedidoCompra($datos["numeroPedido"],null,$datos["idEstadoPedido"]);
        }
        // consultamos todos los Proveedores y los devolvemos a la vista    
        $view = new ViewModel(array('form'=>$this->form,'campoId'=>$campoId,'campoNombre'=>$campoNombre,'registros'=>$registros ));
        $view->setTerminal(true);
        return $view;
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
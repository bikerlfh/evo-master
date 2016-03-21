<?php
namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Admin\Form\FormPedidoVenta;
use Application\Model\Entity\PedidoVenta;
use Application\Model\Entity\PedidoVentaPosicion;
use Application\Model\Entity\EstadoPedido;

use Zend\Session\Container;


class PedidoVentaController extends AbstractActionController
{
    private $PedidoVenta;
    private $form;
    
    private $user_session;
    
    public function __construct() {
        $this->user_session = new Container();
    }
    
    public function solicitudAction()
    {
      
        $this->validarSession();
        // se asigna el layout admin
        $this->layout('layout/admin'); 
        // se obtiene el adapter
        $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
        $this->PedidoVenta = new PedidoVenta($this->dbAdapter);
        $this->form = new FormPedidoVenta($this->getServiceLocator(),$this->getRequest()->getBaseUrl());
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
                if (strpos($key, 'idSaldoInventario') !== FALSE)
                {
                    $indice = split('idSaldoInventario',$key)[1];
                    $PedidoVentaPosicion = new PedidoVentaPosicion($this->dbAdapter);
                    $PedidoVentaPosicion->setIdProducto($datos['idProducto'.$indice]);
                    $PedidoVentaPosicion->setCantidad($datos['cantidad'.$indice]);
                    $PedidoVentaPosicion->setValorCompra($datos['valorVenta'.$indice]);
                    $PedidoVentaPosicion->setIdUsuarioCreacion($this->user_session->idUsuario);                        
                    array_push($this->PedidoVenta->PedidoVentaPosicion, $PedidoVentaPosicion);
                }
            }
            $returnCrud=$this->consultarMessage("errorSave");
            /********************* Se consulta el estado Solicitado*****************************/
            $estadoPedido = new EstadoPedido($this->dbAdapter);
            $estadoPedido->consultarEstadoPedidoPorCodigo("01");
            /**********************************************************************************/
            // Se guarda el nuevo pedido compra con sus posiciones.
            $resultado = $this->PedidoVenta->guardarPedidoVenta($estadoPedido->getidEstadoPedido(),$datos['idCliente'],$urlDocumentoPago, $this->user_session->idUsuario);
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
        $idPedidoVenta=$this->params()->fromQuery('idPedidoVenta',null);
        if ($idPedidoVenta != null) {
            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl().'/admin/pedidocompra/index?id='+$idPedidoVenta);
            return $this->redirect()->toRoute('buscarPedidoVenta',array('idPedidoVenta'=>  $idPedidoVenta));
        }*/
        // se obtiene el adapter
        $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
        
        $this->form = new FormPedidoVenta($this->getServiceLocator(),$this->getRequest()->getBaseUrl());
        $this->form->setAttribute('name' , 'frmBuscarPedidoVenta');
        /** Campos para saber en donde se deben devolver los valores de la busqueda **/
        $campoId=$this->params()->fromQuery('campoId',null) == null? 'idPedidoVenta':$this->params()->fromQuery('campoId',null);
        $campoNombre=$this->params()->fromQuery('campoNombre',null)== null?'numeroPedido':$this->params()->fromQuery('campoNombre',null);
        
        $registros = array();
        if(count($this->request->getPost()) > 0)
        {
            $datos = $this->request->getPost();
            $this->PedidoVenta = new PedidoVenta($this->dbAdapter);
            $this->form->get("numeroPedido")->setValue($datos["numeroPedido"]);
            //$this->form->get("idProveedor")->setValue($datos["idProveedor"]);
            //$this->form->get("nombreProveedor")->setValue($datos["nombreProveedor"]);
            $this->form->get("idEstadoPedidoBusqueda")->setValue($datos["idEstadoPedido"]);            
            $registros = $this->PedidoVenta->consultaAvanzadaPedidoVenta($datos["numeroPedido"],null,$datos["idEstadoPedido"]);
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
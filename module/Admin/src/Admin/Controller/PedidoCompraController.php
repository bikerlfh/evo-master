<?php
namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Admin\Form\FormPedidoCompra;
use Application\Model\Entity\PedidoCompra;
use Application\Model\Entity\PedidoCompraPosicion;
use Application\Model\Entity\EstadoPedido;

use Zend\Session\Container;


class PedidoCompraController extends AbstractActionController
{
    private $PedidoCompra;
    private $EstadoPedido;
    //private $PedidoCompraPosicion;
    private $form;
    
    private $user_session;
    
    public function __construct() {
        $this->user_session = new Container('user');
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
        $this->PedidoCompra = new PedidoCompra($this->dbAdapter);
        $this->form = new FormPedidoCompra($this->getServiceLocator(),$this->getRequest()->getBaseUrl());
        $this->configurarBotonesFormulario(false);
        // Si se ha enviado parámetros por post, se evalua si se va a modificar o a guardar
        if(count($this->request->getPost())>0)
        {
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
            /********************* Se consulta el estado Solicitado*****************************/
            $estadoPedido = new EstadoPedido($this->dbAdapter);
            $estadoPedido->consultarEstadoPedidoPorCodigo("01");
            /**********************************************************************************/
            // Se guarda el nuevo pedido compra con sus posiciones.
            $resultado = $this->PedidoCompra->guardarPedidoCompra($estadoPedido->getidEstadoPedido(),$datos['idProveedor'],null, $this->user_session->idUsuario);
            if($resultado == 'true'){
                $returnCrud=$this->consultarMessage("okSave");
            }
            return new ViewModel(array('form'=>$this->form,'msg'=>$returnCrud,'numeroPedido'=>$this->PedidoCompra->getNumeroPedido()));
        }
        return new ViewModel(array('form'=>$this->form));
    }
    public function autorizarAction()
    {
        $this->validarSession();
        // se asigna el layout admin
        $this->layout('layout/admin');
        $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
        $this->form = new FormPedidoCompra($this->getServiceLocator(),$this->getRequest()->getBaseUrl());
        
        $id=$this->params()->fromQuery('idPedidoCompra',null);
        $request = $this->getRequest();
        if ($request->isPost()) 
        {
            $this->form->remove('idEstadoPedido');
            $urlDocumentoPago = null;
            $returnCrud = $this->consultarMessage("errorAutorizacion");
            // Make certain to merge the files info!
            $data = array_merge_recursive(
                $request->getPost()->toArray(),
                $request->getFiles()->toArray()
            );
            /***************** SE CAMBIA EL NOMBRE DE LA IMAGEN***************/
            $data['image-file']['name'] = "pedido_compra_".$data['numeroPedido'].'.'.explode ('.',$data['image-file']['name'])[1];
            /******************************************************************/
            $this->form->setData($data);
            if ($this->form->isValid()) 
            {
                $data = $this->form->getData();
                $urlDocumentoPago = $data['image-file']['tmp_name'];
                $this->PedidoCompra = new PedidoCompra($this->dbAdapter);
                $result = $this->PedidoCompra->autorizarPedidoCompra($data['idPedidoCompra'],$urlDocumentoPago,$this->user_session->idUsuario);
                if ($result == 'true'){
                    $returnCrud = $this->consultarMessage("okAutorizacion");
                }
            }
            return new ViewModel(array('form'=>$this->form,'msg'=>$returnCrud));
        }
        else if (isset($id)) 
        {
            $this->PedidoCompra = new PedidoCompra($this->dbAdapter);
            if($this->PedidoCompra->consultarPedidoCompraPorIdPedidoCompra($id))
            {
                $this->EstadoPedido = new EstadoPedido($this->dbAdapter);
                $this->EstadoPedido->consultarEstadoPedidoPorIdEstadoPedido($this->PedidoCompra->getIdEstadoPedido());
                // Se valida que el pedido este con estado SOLICITADO
                if ($this->EstadoPedido->getDescripcion() != 'SOLICITADO'){
                    unset($this->PedidoCompra);
                    return new ViewModel(array('form'=>$this->form,'validacion'=>'El pedido seleccionado esta con estado '.$this->EstadoPedido->getDescripcion()));
                }
                $this->form->get("idPedidoCompra")->setValue($this->PedidoCompra->getIdPedidoCompra());
                $this->form->get("numeroPedido")->setValue($this->PedidoCompra->getNumeroPedido());
                $this->form->get("nombreProveedor")->setValue($this->PedidoCompra->getNombreProveedor());
                return new ViewModel(array('form'=>$this->form,'PedidoCompraPosicion'=>$this->PedidoCompra->PedidoCompraPosicion));
            }
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
        // Se cambia de nombre al campo, para que este no tenga conflicto con el campo del formulario que abrió la busqueda.
        $this->form->get('nombreProveedor')->setAttributes(array('id'=>'nombreProveedorBusqueda','name'=>'nombreProveedorBusqueda'));
        $this->form->get('numeroPedido')->setAttributes(array('id'=>'numeroPedidoBusqueda','name'=>'numeroPedidoBusqueda','readonly'=>false));
        /** Campos para saber en donde se deben devolver los valores de la busqueda **/
        $campoId=$this->params()->fromQuery('campoId',null) == null? 'idPedidoCompra':$this->params()->fromQuery('campoId',null);
        $campoNombre=$this->params()->fromQuery('campoNombre',null)== null?'numeroPedido':$this->params()->fromQuery('campoNombre',null);
        $vista=$this->params()->fromQuery('vista',null)== null?'autorizar':$this->params()->fromQuery('vista',null);
        
         //**** OJO: la Uri se debe enviar a la busqueda *****//
        $Uri = $this->getRequest()->getRequestUri();
        
        $registros = array();
        if(count($this->request->getPost()) > 0)
        {
            $datos = $this->request->getPost();
            $this->PedidoCompra = new PedidoCompra($this->dbAdapter);
            $this->form->get("numeroPedido")->setValue($datos["numeroPedido"]);
            $this->form->get("idProveedor")->setValue($datos["idProveedor"]);
            $this->form->get("nombreProveedor")->setValue($datos["nombreProveedor"]);
            $this->form->get("idEstadoPedido")->setValue($datos["idEstadoPedido"]);            
            $registros = $this->PedidoCompra->consultaAvanzadaPedidoCompra($datos["numeroPedido"],$datos["idProveedor"],$datos["idEstadoPedido"]);
        }
        // consultamos todos los Proveedores y los devolvemos a la vista    
        $view = new ViewModel(array(
                                    'form'=>$this->form,
                                    'vista'=>$vista,
                                    'campoId'=>$campoId,
                                    'campoNombre'=>$campoNombre,
                                    'Uri'=> $Uri,
                                    'registros'=>$registros ));
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
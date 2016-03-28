<?php
namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Admin\Form\FormPedidoVenta;
use Admin\Form\FormBase;
use Application\Model\Entity\PedidoVenta;
use Application\Model\Entity\PedidoVentaPosicion;
use Application\Model\Entity\EstadoPedidoVenta;
use Application\Model\Entity\SaldoInventario;

use Zend\Session\Container;


class PedidoVentaController extends AbstractActionController
{
    private $PedidoVenta;
    private $EstadoPedido;
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
            $datos=$this->request->getPost();
            // Este ciclo muestra todas las claves del array asociativo
            $this->PedidoVenta->PedidoVentaPosicion = array();
            $validador = false;
            foreach($datos as $key => $value)
            {
                // Se evalua si la clave es un idProducto
                if (strpos($key, 'idSaldoInventario') !== FALSE)
                {
                    $indice = split('idSaldoInventario',$key)[1];
                    
                    //Se obtienen valores
                    $idSaldoInventario = $datos['idSaldoInventario'. $indice];
                    $cantidad = $datos['cantidad'.$indice];
                    //Se consulta el saldo inventario para rectificar tarifas.
                    $SaldoInventario = new SaldoInventario($this->dbAdapter);
                    $SaldoInventario->consultarSaldoInventarioPorIdSaldoInventario($idSaldoInventario);
//                    
//                    //Se valida la cantidad de productos ingresados
//                    if($cantidad > $SaldoInventario->getCantidad())
//                    {
//                        $validador = true;
//                        break;
//                    }
                    $PedidoVentaPosicion = new PedidoVentaPosicion($this->dbAdapter);
                    $PedidoVentaPosicion->setIdProducto($SaldoInventario->getIdProducto());
                    $PedidoVentaPosicion->setCantidad($cantidad);
                    $PedidoVentaPosicion->setValorVenta($SaldoInventario->getValorVenta());
                    $PedidoVentaPosicion->setIdSaldoInventario($SaldoInventario->getIdSaldoInventario());
                    $PedidoVentaPosicion->setIdUsuarioCreacion($this->user_session->idUsuario);
                    array_push($this->PedidoVenta->PedidoVentaPosicion, $PedidoVentaPosicion);
                }
            }
            $returnCrud=$this->consultarMessage("errorSave");
            
            if(!$validador)
            {
                /********************* Se consulta el estado Solicitado*****************************/
                $estadoPedido = new EstadoPedidoVenta($this->dbAdapter);
                $estadoPedido->consultarEstadoPedidoVentaPorcodigo("01");
                
                //en caso de que no exista el estado del pedido se envia al frm de estado pedido.
                if($estadoPedido->getIdEstadoPedidoVenta() == null)
                {
                    $this->redirect()->toUrl($this->getRequest()->getBaseUrl()."/admin/estadopedidoventa");
                }
                
                /**********************************************************************************/
                // Se guarda el nuevo pedido compra con sus posiciones.
                $resultado = $this->PedidoVenta->guardarPedidoVenta($estadoPedido->getIdEstadoPedidoVenta(),$datos['idCliente'],$urlDocumentoPago, $this->user_session->idUsuario);
                if($resultado == 'true'){
                    $returnCrud=$this->consultarMessage("okSave");
                }
            }
            return new ViewModel(array('form'=>$this->form,'msg'=>$returnCrud,'numeroPedido'=>$this->PedidoVenta->getIdPedidoVenta()));
        }
        return new ViewModel(array('form'=>$this->form));
    }
    
     public function autorizarAction()
    {
        $this->validarSession();
        // se asigna el layout admin
        $this->layout('layout/admin');
        $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
        $this->form = new FormPedidoVenta($this->getServiceLocator(),$this->getRequest()->getBaseUrl());
        
        $id=$this->params()->fromQuery('idPedidoVenta',null);
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
            $data['image-file']['name'] = "pedido_Venta_".$data['numeroPedido'].'.'.explode ('.',$data['image-file']['name'])[1];
            /******************************************************************/
            $this->form->setData($data);
            if ($this->form->isValid()) 
            {
                $data = $this->form->getData();
                $urlDocumentoPago = $data['image-file']['tmp_name'];
                $this->PedidoVenta = new PedidoVenta($this->dbAdapter);
                $result = $this->PedidoVenta->autorizarPedidoVenta($data['idPedidoVenta'],$urlDocumentoPago,$this->user_session->idUsuario);
                if ($result == 'true'){
                    $returnCrud = $this->consultarMessage("okAutorizacion");
                }
            }
            return new ViewModel(array('form'=>$this->form,'msg'=>$returnCrud));
        }
        else if (isset($id)) 
        {
            $this->PedidoVenta = new PedidoVenta($this->dbAdapter);
            if($this->PedidoVenta->consultarPedidoVentaPorIdPedidoVenta($id))
            {
                $this->EstadoPedido = new EstadoPedidoVenta($this->dbAdapter);
                $this->EstadoPedido->consultarEstadoPedidoVentaPorIdEstadoPedidoVenta($this->PedidoVenta->getIdEstadoPedidoVenta());
                // Se valida que el pedido este con estado SOLICITADO
                if ($this->EstadoPedido->getDescripcion() != 'SOLICITADO'){
                    unset($this->PedidoVenta);
                    return new ViewModel(array('form'=>$this->form,'validacion'=>'El pedido seleccionado esta con estado '.$this->EstadoPedido->getDescripcion()));
                }
                $this->form->get("idPedidoVenta")->setValue($this->PedidoVenta->getIdPedidoVenta());
                $this->form->get("numeroPedido")->setValue($this->PedidoVenta->getNumeroPedidoVenta());
                $this->form->get("nombreCliente")->setValue($this->PedidoVenta->getNombreCliente());
                return new ViewModel(array('form'=>$this->form,'PedidoVentaPosicion'=>$this->PedidoVenta->PedidoVentaPosicion));
            }
        }
        return new ViewModel(array('form'=>$this->form));
    }
    
    public function buscarAction()
    {
        $this->validarSession();
       
        // se obtiene el adapter
        $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
        
        $this->form = new FormPedidoVenta($this->getServiceLocator(),$this->getRequest()->getBaseUrl());
        $this->form->setAttribute('name' , 'frmBuscarPedidoVenta');
        
        // Se cambia de nombre al campo, para que este no tenga conflicto con el campo del formulario que abrió la busqueda.
        $this->form->get('nombreCliente')->setAttributes(array('id'=>'nombreClienteBusqueda','name'=>'nombreClienteBusqueda'));
        $this->form->get('numeroPedido')->setAttributes(array('id'=>'numeroPedidoBusqueda','name'=>'numeroPedidoBusqueda','readonly'=>false));
        $vista=$this->params()->fromQuery('vista',null)== null?'autorizar':$this->params()->fromQuery('vista',null);
        
        /** Campos para saber en donde se deben devolver los valores de la busqueda **/
        $campoId=$this->params()->fromQuery('campoId',null) == null? 'idPedidoVenta':$this->params()->fromQuery('campoId',null);
        $campoNombre=$this->params()->fromQuery('campoNombre',null)== null?'numeroPedido':$this->params()->fromQuery('campoNombre',null);
        
          //****Campos modal *****//
        $botonClose = $this->params()->fromQuery('botonClose',null) == null ? 'btnClosePop' :$this->params()->fromQuery('botonClose',null);
        $contenedorDialog = $this->params()->fromQuery('contenedorDialog',null) == null ? 'modal-dialog-display' :$this->params()->fromQuery('contenedorDialog',null);
        $modal = $this->params()->fromQuery('modal',null) == null ? 'textModal' :$this->params()->fromQuery('modal',null);
        
        $registros = array();
        if(count($this->request->getPost()) > 0)
        {
            $datos = $this->request->getPost();
            $this->PedidoVenta = new PedidoVenta($this->dbAdapter);
            $this->form->get("numeroPedido")->setValue($datos["numeroPedido"]);
            $this->form->get("idCliente")->setValue($datos["idCliente"]);
            $this->form->get("nombreCliente")->setValue($datos["nombreCliente"]);
            $this->form->get("idEstadoPedidoVenta")->setValue($datos["idEstadoPedido"]);            
            $registros = $this->PedidoVenta->consultaAvanzadaPedidoVenta($datos["numeroPedido"],$datos['idCliente'],$datos["idEstadoPedido"]);
        }
        
        $view = new ViewModel(array('form'=>$this->form,
                                    'campoId'=>$campoId,
                                    'vista'=>$vista,
                                    'campoNombre'=>$campoNombre,
                                    'botonClose'=> $botonClose,
                                    'contenedorDialog'=> $contenedorDialog,
                                    'modal'=> $modal,
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
        //<== Si no existe la session se redirge al login ==>
        if (!isset($_SESSION['user'])) {
            return $this->redirect()->toUrl(str_replace("/public","", $this->getRequest()->getBaseUrl()).'/admin/login');
        }
        $this->user_session = $_SESSION['user'];
    }
}
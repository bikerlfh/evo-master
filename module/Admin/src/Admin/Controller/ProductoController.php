<?php
namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Admin\Form\FormBase;
use Admin\Form\FormProducto;
use Application\Model\Entity\Producto;
use Zend\Session\Container;


class ProductoController extends AbstractActionController
{
    private $Producto;
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
        
        $this->Producto = new Producto($this->dbAdapter);
        $this->form = new FormProducto($this->getServiceLocator(),$this->getRequest()->getBaseUrl());
        /**************************************************************************************/
        // Se agregan el botones de buscar  marca y categoria al formualrio saldo inventario
        /**************************************************************************************/
        $formBase = new FormBase($this->getServiceLocator(),$this->getRequest()->getBaseUrl());
        $this->form->add($formBase->get("btnBuscarMarca"));
        $this->form->add($formBase->get("btnBuscarCategoria"));
        /**************************************************************************************/
        $this->configurarBotonesFormulario(false);
        // Si se ha enviado parámetros por post, se evalua si se va a modificar o a guardar
        if(count($this->request->getPost())>0)
        {
            $datos=$this->request->getPost();
            // Si se envia el id de la producto se modifica este.
            if ($datos["idProducto"] != null) 
            {
                $returnCrud=$this->consultarMessage("errorUpdate");
                if($this->Producto->modificarProducto($datos['idProducto'],$datos['idMarca'],$datos['idCategoria'],$datos['codigo'],$datos['nombre'],$datos['referencia'],$datos['descripcion'],$datos['especificacion']))
                    $returnCrud=$this->consultarMessage("okUpdate");
            }
            else
            {
                $returnCrud=$this->consultarMessage("errorSave");
                // se guarda la nueva producto
                if($this->Producto->guardarProducto($datos['idMarca'],$datos['idCategoria'],$datos['codigo'],$datos['nombre'],$datos['referencia'],$datos['descripcion'],$datos['especificacion'], $this->user_session->idUsuario,  date('d-m-Y H:i:s')))
                    $returnCrud=$this->consultarMessage("okSave");
            }
                return new ViewModel(array('form'=>$this->form,'msg'=>$returnCrud,'registros'=>$this->Producto->consultarTodoProducto()));
        }
        // si existe el parametro $id  se consulta la producto y se carga el formulario.
        else if(isset($id))
        {
            $this->Producto->consultarProductoPorIdProducto($this->params()->fromQuery('id'));
            $this->form->get("idProducto")->setValue($this->Producto->getIdProducto());
            $this->form->get("idMarca")->setValue($this->Producto->getIdMarca());
            $this->form->get("idCategoria")->setValue($this->Producto->getIdCategoria());
            $this->form->get("codigo")->setValue($this->Producto->getCodigo());
            $this->form->get("nombre")->setValue($this->Producto->getNombre());
            $this->form->get("referencia")->setValue($this->Producto->getReferencia());
            $this->form->get("descripcion")->setValue($this->Producto->getDescripcion());
            $this->form->get("especificacion")->setValue($this->Producto->getEspecificacion());
            $this->configurarBotonesFormulario(true);
        }
        return new ViewModel(array('form'=>$this->form,'registros'=>$this->Producto->consultarTodoProducto()));
    }
    public function buscarAction()
    {
        $this->validarSession();
        // se obtiene el adapter
        $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
        $this->form = new FormProducto($this->getServiceLocator(),$this->getRequest()->getBaseUrl());
        /** Campos para saber en donde se deben devolver los valores de la busqueda **/
        $campoId=$this->params()->fromQuery('campoId',null) == null? 'idProducto':$this->params()->fromQuery('campoId',null);
        $campoNombre=$this->params()->fromQuery('campoNombre',null)== null?'nombreProducto':$this->params()->fromQuery('campoNombre',null);
        /*****************************************************************************/
        
        //****Campos modal *****//
        $botonClose = $this->params()->fromQuery('botonClose',null) == null ? 'btnClosePop' :$this->params()->fromQuery('botonClose',null);
        $contenedorDialog = $this->params()->fromQuery('contenedorDialog',null) == null ? 'modal-dialog-display' :$this->params()->fromQuery('contenedorDialog',null);
        $modal = $this->params()->fromQuery('modal',null) == null ? 'textModal' :$this->params()->fromQuery('modal',null);
        
        $registros = array();
        if(count($this->request->getPost())>0)
        {
            $this->Producto = new Producto($this->dbAdapter);
            $datos = $this->request->getPost();
            $this->form->get("idMarca")->setValue($datos['idMarca']);
            $this->form->get("idCategoria")->setValue($datos['idCategoria']);
            $this->form->get("referencia")->setValue($datos['referencia']);
            $this->form->get("codigo")->setValue($datos['codigo']);
            $this->form->get("nombre")->setValue($datos['nombre']);
            $registros = $this->Producto->consultaAvanzadaProducto($datos['idMarca'],$datos['idCategoria'],$datos['referencia'],$datos['codigo'],$datos['nombre']);
        }
        
        // consultamos todos los productos y los devolvemos a la vista    
        $view = new ViewModel(array('form'=>$this->form,
                                    'campoId'=>$campoId,
                                    'campoNombre'=>$campoNombre,
                                    'botonClose'=> $botonClose,
                                    'contenedorDialog'=> $contenedorDialog,
                                    'modal'=> $modal,
                                    'registros'=>$registros));
        $view->setTerminal(true);
        return $view;
    }
    
    public function buscar2Action()
    {
        $this->validarSession();
        // se obtiene el adapter
        $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
        $this->form = new FormProducto($this->getServiceLocator(),$this->getRequest()->getBaseUrl());
        /** Campos para saber en donde se deben devolver los valores de la busqueda **/
        $campoId=$this->params()->fromQuery('campoId',null) == null? 'idProducto':$this->params()->fromQuery('campoId',null);
        $campoNombre=$this->params()->fromQuery('campoNombre',null)== null?'nombreProducto':$this->params()->fromQuery('campoNombre',null);
        /*****************************************************************************/
        $registros = array();
        if(count($this->request->getPost())>0)
        {
            $this->Producto = new Producto($this->dbAdapter);
            $datos = $this->request->getPost();
            $this->form->get("idMarca")->setValue($datos['idMarca']);
            $this->form->get("idCategoria")->setValue($datos['idCategoria']);
            $this->form->get("referencia")->setValue($datos['referencia']);
            $this->form->get("codigo")->setValue($datos['codigo']);
            $this->form->get("nombre")->setValue($datos['nombre']);
            $registros = $this->Producto->consultaAvanzadaProducto($datos['idMarca'],$datos['idCategoria'],$datos['referencia'],$datos['codigo'],$datos['nombre']);
        }
        // consultamos todos los productos y los devolvemos a la vista    
        $view = new ViewModel(array('form'=>$this->form,'campoId'=>$campoId,'campoNombre'=>$campoNombre,'registros'=>$registros));
        $view->setTerminal(true);
        return $view;
    }
    
    public function eliminarAction()
    {
        //$this->validarSession();
        $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
        $this->Producto = new Producto($this->dbAdapter);
        $id=$this->params()->fromQuery('id',null);
        if($id != null)
        {
            $this->Producto->eliminarProducto($id);
            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl().'/admin/producto');
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
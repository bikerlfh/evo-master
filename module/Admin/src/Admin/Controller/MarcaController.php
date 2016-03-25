<?php
namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Admin\Form\FormMarca;
use Application\Model\Entity\Marca;
use Zend\Session\Container;

class MarcaController extends AbstractActionController
{
    private $Marca;
    private $form;
    
    private $user_session;
    public function __construct() {
        $this->user_session = new Container();
    }
    public function indexAction()
    {
        //$this->validarSession();
        // se asigna el layout admin
        $this->layout('layout/admin'); 
        // se obtiene el adapter
        $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
        // Parametro pasado por get, con el cual se sabe si se seleccionó objeto para modificar
        $id=$this->params()->fromQuery('id',null);
        
        $this->Marca = new Marca($this->dbAdapter);
        $this->form = new FormMarca($this->getServiceLocator(),$this->getRequest()->getBaseUrl());
        $this->configurarBotonesFormulario(false);
        // Si se ha enviado parámetros por post, se evalua si se va a modificar o a guardar
        if(count($this->request->getPost())>0)
        {
            $datos=$this->request->getPost();
            // Si se envia el id de la categoria se modifica este.
            if ($datos["idMarca"] != null) 
            {
                $returnCrud=$this->consultarMessage("errorUpdate");
                if($this->Marca->modificarMarca($datos['idMarca'],$datos['codigo'],$datos['descripcion']))
                    $returnCrud=$this->consultarMessage("okUpdate");
            }
            else
            {
                $returnCrud=$this->consultarMessage("errorSave");
                // se guarda la nueva categoria
                if($this->Marca->guardarMarca($datos['codigo'],$datos['descripcion']))
                    $returnCrud=$this->consultarMessage("okSave");
            }
            return new ViewModel(array('form'=>$this->form,'msg'=>$returnCrud,'registros'=>$this->Marca->consultarTodoMarca()));
        }
        // si existe el parametro $id  se consulta la categoria y se carga el formulario.
        else if(isset($id))
        {
            $this->Marca->consultarMarcaPorIdMarca($this->params()->fromQuery('id'));
            $this->form->get("idMarca")->setValue($this->Marca->getIdMarca());
            $this->form->get("codigo")->setValue($this->Marca->getCodigo());
            $this->form->get("descripcion")->setValue($this->Marca->getDescripcion());
            $this->configurarBotonesFormulario(true);
        }
        return new ViewModel(array('form'=>$this->form,'registros'=>$this->Marca->consultarTodoMarca()));
    }
    public function buscarAction()
    {
        $this->validarSession();
        // se obtiene el adapter
        $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
        $this->Marca = new Marca($this->dbAdapter);
        
        //****Campos modal *****//
        $botonClose = $this->params()->fromQuery('botonClose',null) == null ? 'btnClosePop' :$this->params()->fromQuery('botonClose',null);
        $contenedorDialog = $this->params()->fromQuery('contenedorDialog',null) == null ? 'modal-dialog-display' :$this->params()->fromQuery('contenedorDialog',null);
        $modal = $this->params()->fromQuery('modal',null) == null ? 'textModal' :$this->params()->fromQuery('modal',null);
        
        // consultamos todas las marcas y los devolvemos a la vista    
        $view = new ViewModel(array('botonClose'=> $botonClose,
                                    'contenedorDialog'=> $contenedorDialog,
                                    'modal'=> $modal,
                                    'registros'=>$this->Marca->consultarTodoMarca()));
        $view->setTerminal(true);
        return $view;
    }
    public function eliminarAction()
    {
        //$this->validarSession();
        $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
        $this->Marca = new Marca($this->dbAdapter);
        $id=$this->params()->fromQuery('id',null);
        if($id != null)
        {
            $this->Marca->eliminarMarca($id);
            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl().'/admin/marca');
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
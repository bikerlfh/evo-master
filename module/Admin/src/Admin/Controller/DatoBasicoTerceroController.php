<?php
namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Admin\Form\FormDatoBasicoTercero;
use Application\Model\Entity\DatoBasicoTercero;
use Zend\Session\Container;

class DatoBasicoTerceroController extends AbstractActionController
{
    private $DatoBasicoTercero;
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
        
        $this->DatoBasicoTercero = new DatoBasicoTercero($this->dbAdapter);
        $this->form = new FormDatoBasicoTercero($this->getServiceLocator(),$this->getRequest()->getBaseUrl());
        $this->configurarBotonesFormulario(false);
        // Si se ha enviado parámetros por post, se evalua si se va a modificar o a guardar
        if(count($this->request->getPost())>0)
        {
            $datos=$this->request->getPost();
            // Si se envia el id de la categoria se modifica este.
            if ($datos["idDatoBasicoTercero"] != null) 
            {
                $returnCrud=$this->consultarMessage("errorUpdate");
                if($this->DatoBasicoTercero->modificarDatoBasicoTercero($datos['idDatoBasicoTercero'],$datos['idTipoDocumento'],$datos['nit'],$datos['descripcion'],$datos['primerNombre'],$datos['segundoNombre'],$datos['primerApellido'],$datos['segundoApellido'],$datos['direccion'],$datos['telefono']))
                    $returnCrud=$this->consultarMessage("okUpdate");
            }
            else
            {
                $returnCrud=$this->consultarMessage("errorSave");
                // se guarda la nueva categoria
                if($this->DatoBasicoTercero->guardarDatoBasicoTercero($datos['idTipoDocumento'],$datos['nit'],$datos['descripcion'],$datos['primerNombre'],$datos['segundoNombre'],$datos['primerApellido'],$datos['segundoApellido'],$datos['direccion'],$datos['telefono']))
                    $returnCrud=$this->consultarMessage("okSave");
            }
            return new ViewModel(array('form'=>$this->form,'msg'=>$returnCrud,'registros'=>$this->DatoBasicoTercero->consultarTodoDatoBasicoTercero()));
        }
        // si existe el parametro $id  se consulta la categoria y se carga el formulario.
        else if(isset($id))
        {
            $this->DatoBasicoTercero->consultarDatoBasicoTerceroPorIdDatoBasicoTercero($this->params()->fromQuery('id'));
            $this->form->get("idDatoBasicoTercero")->setValue($this->DatoBasicoTercero->getIdDatoBasicoTercero());
            $this->form->get("idTipoDocumento")->setValue($this->DatoBasicoTercero->getidTipoDocumento());
            $this->form->get("nit")->setValue($this->DatoBasicoTercero->getnit());
            $this->form->get("descripcion")->setValue($this->DatoBasicoTercero->getdescripcion());
            $this->form->get("primerNombre")->setValue($this->DatoBasicoTercero->getprimerNombre());
            $this->form->get("segundoNombre")->setValue($this->DatoBasicoTercero->getsegundoNombre());
            $this->form->get("primerApellido")->setValue($this->DatoBasicoTercero->getprimerApellido());
            $this->form->get("segundoApellido")->setValue($this->DatoBasicoTercero->getsegundoApellido());
            $this->form->get("direccion")->setValue($this->DatoBasicoTercero->getdireccion());
            $this->form->get("telefono")->setValue($this->DatoBasicoTercero->gettelefono());
            $this->configurarBotonesFormulario(true);
        }
        return new ViewModel(array('form'=>$this->form,'registros'=>$this->DatoBasicoTercero->consultarTodoDatoBasicoTercero()));
    }
    public function buscarAction()
    {
        $this->validarSession();
        // se obtiene el adapter
        $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
        $this->DatoBasicoTercero = new DatoBasicoTercero($this->dbAdapter);
        // consultamos todos los terceros y los devolvemos a la vista    
        $view = new ViewModel(array('registros'=>$this->DatoBasicoTercero->consultarTodoDatoBasicoTercero()));
        $view->setTerminal(true);
        return $view;
    }
    public function eliminarAction()
    {
        //$this->validarSession();
        $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
        $this->DatoBasicoTercero = new DatoBasicoTercero($this->dbAdapter);
        $id=$this->params()->fromQuery('id',null);
        if($id != null)
        {
            $this->DatoBasicoTercero->eliminarDatoBasicoTercero($id);
            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl().'/admin/datobasicotercero');
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
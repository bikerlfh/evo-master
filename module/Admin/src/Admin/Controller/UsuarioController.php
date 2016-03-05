<?php
namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Admin\Form\FormUsuario;
use Application\Model\Entity\Usuario;

class UsuarioController extends AbstractActionController
{
    private $Usuario;
    private $form;
    public function indexAction()
    {
        //$this->validarSession();
        // se asigna el layout admin
        $this->layout('layout/admin'); 
        // se obtiene el adapter
        $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
        // Parametro pasado por get, con el cual se sabe si se seleccionó objeto para modificar
        $id=$this->params()->fromQuery('id',null);
        
        $this->Usuario = new Usuario($this->dbAdapter);
        $this->form = new FormUsuario($this->getServiceLocator(),$this->getRequest()->getBaseUrl());
        $this->configurarBotonesFormulario(false);
        // Si se ha enviado parámetros por post, se evalua si se va a modificar o a guardar
        if(count($this->request->getPost())>0)
        {
            $datos=$this->request->getPost();
            // Si se envia el id de la categoria se modifica este.
            if ($datos["idUsuario"] != null) 
            {
                $returnCrud=$this->consultarMessage("errorUpdate");
                if($this->Usuario->modificarUsuario($datos['idUsuario'],$datos['email'],$datos['idDatoBasicoTercero'],$datos['idTipoUsuario'],$datos["clave"]))
                    $returnCrud=$this->consultarMessage("okUpdate");
            }
            else
            {
                $returnCrud=$this->consultarMessage("errorSave");
                // se guarda la nueva categoria
                if($this->Usuario->guardarUsuario($datos['clave'],$datos['email'],$datos['idDatoBasicoTercero'],$datos['idTipoUsuario']))
                    $returnCrud=$this->consultarMessage("okSave");
            }
            return new ViewModel(array('form'=>$this->form,'msg'=>$returnCrud,'registros'=>$this->Usuario->consultarTodoUsuario()));
        }
        // si existe el parametro $id  se consulta la categoria y se carga el formulario.
        else if(isset($id))
        {
            $this->Usuario->consultarUsuarioPorIdUsuario($this->params()->fromQuery('id'));
            $this->form->get("idUsuario")->setValue($this->Usuario->getIdUsuario());
            $this->form->get("email")->setValue($this->Usuario->getemail());
            $this->form->get("clave")->setAttribute("required", false);
            $this->form->get("idDatoBasicoTercero")->setValue($this->Usuario->getidDatoBasicoTercero());
            $this->form->get("idTipoUsuario")->setValue($this->Usuario->getidTipoUsuario());
            $this->configurarBotonesFormulario(true);
        }
        return new ViewModel(array('form'=>$this->form,'registros'=>$this->Usuario->consultarTodoUsuario()));
    }
    
    public function eliminarAction()
    {
        //$this->validarSession();
        $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
        $this->Usuario = new Usuario($this->dbAdapter);
        $id=$this->params()->fromQuery('id',null);
        if($id != null)
        {
            $this->Usuario->eliminarUsuario($id);
            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl().'/admin/usuario');
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
    }
}
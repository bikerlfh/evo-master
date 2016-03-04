<?php
namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Admin\Form\FormTipoUsuario;
use Application\Model\Entity\TipoUsuario;

class TipoUsuarioController extends AbstractActionController
{
    private $TipoUsuario;
    private $form;
    public function indexAction()
    {
        // se asigna el layout admin
        $this->layout('layout/admin'); 
        // se obtiene el adapter
        $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
        // Parametro pasado por get, con el cual se sabe si se seleccionó objeto para modificar
        $id=$this->params()->fromQuery('id',null);
        
        $this->TipoUsuario = new TipoUsuario($this->dbAdapter);
        $this->form = new FormTipoUsuario($this->getServiceLocator(),$this->getRequest()->getBaseUrl());
        $this->configurarBotonesFormulario(false);
        // Si se ha enviado parámetros por post, se evalua si se va a modificar o a guardar
        if(count($this->request->getPost())>0)
        {
            $datos=$this->request->getPost();
            // Si se envia el id de la viapago se modifica este.
            if ($datos["idTipoUsuario"] != null) 
            {
                $returnCrud=$this->consultarMessage("errorUpdate");
                if($this->TipoUsuario->modificarTipoUsuario($datos['idTipoUsuario'],$datos['codigo'],$datos['descripcion']))
                    $returnCrud=$this->consultarMessage("okUpdate");
            }
            else
            {
                $returnCrud=$this->consultarMessage("errorSave");
                // se guarda la nueva viapago
                if($this->TipoUsuario->guardarTipoUsuario($datos['codigo'],$datos['descripcion']))
                    $returnCrud=$this->consultarMessage("okSave");
            }
            return new ViewModel(array('form'=>$this->form,'msg'=>$returnCrud,'registros'=>$this->TipoUsuario->consultarTodoTipoUsuario()));
        }
        // si existe el parametro $id  se consulta la viapago y se carga el formulario.
        else if(isset($id))
        {
            $this->TipoUsuario->consultarTipoUsuarioPorIdTipoUsuario($this->params()->fromQuery('id'));
            $this->form->get("idTipoUsuario")->setValue($this->TipoUsuario->getIdTipoUsuario());
            $this->form->get("codigo")->setValue($this->TipoUsuario->getCodigo());
            $this->form->get("descripcion")->setValue($this->TipoUsuario->getDescripcion());
            $this->configurarBotonesFormulario(true);
        }
        return new ViewModel(array('form'=>$this->form,'registros'=>$this->TipoUsuario->consultarTodoTipoUsuario()));
    }
    
    public function eliminarAction()
    {
        $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
        $this->TipoUsuario = new TipoUsuario($this->dbAdapter);
        $id=$this->params()->fromQuery('id',null);
        if($id != null)
        {
            $this->TipoUsuario->eliminarTipoUsuario($id);
            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl().'/admin/tipousuario');
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
}
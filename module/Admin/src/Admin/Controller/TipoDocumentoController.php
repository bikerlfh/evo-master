<?php
namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Admin\Form\FormTipoDocumento;
use Application\Model\Entity\TipoDocumento;

class TipoDocumentoController extends AbstractActionController
{
    private $TipoDocumento;
    private $form;
    public function indexAction()
    {
        $this->validarSession();
        // se asigna el layout admin
        $this->layout('layout/admin'); 
        // se obtiene el adapter
        $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
        // Parametro pasado por get, con el cual se sabe si se seleccionó objeto para modificar
        $id=$this->params()->fromQuery('id',null);
        
        $this->TipoDocumento = new TipoDocumento($this->dbAdapter);
        $this->form = new FormTipoDocumento($this->getServiceLocator(),$this->getRequest()->getBaseUrl());
        $this->configurarBotonesFormulario(false);
        // Si se ha enviado parámetros por post, se evalua si se va a modificar o a guardar
        if(count($this->request->getPost())>0)
        {
            $datos=$this->request->getPost();
            // Si se envia el id de la viapago se modifica este.
            if ($datos["idTipoDocumento"] != null) 
            {
                $returnCrud=$this->consultarMessage("errorUpdate");
                if($this->TipoDocumento->modificarTipoDocumento($datos['idTipoDocumento'],$datos['codigo'],$datos['descripcion']))
                    $returnCrud=$this->consultarMessage("okUpdate");
            }
            else
            {
                $returnCrud=$this->consultarMessage("errorSave");
                // se guarda la nueva viapago
                if($this->TipoDocumento->guardarTipoDocumento($datos['codigo'],$datos['descripcion']))
                    $returnCrud=$this->consultarMessage("okSave");
            }
            return new ViewModel(array('form'=>$this->form,'msg'=>$returnCrud,'registros'=>$this->TipoDocumento->consultarTodoTipoDocumento()));
        }
        // si existe el parametro $id  se consulta la viapago y se carga el formulario.
        else if(isset($id))
        {
            $this->TipoDocumento->consultarTipodocumentoPoridTipoDocumento($this->params()->fromQuery('id'));
            $this->form->get("idTipoDocumento")->setValue($this->TipoDocumento->getIdTipoDocumento());
            $this->form->get("codigo")->setValue($this->TipoDocumento->getCodigo());
            $this->form->get("descripcion")->setValue($this->TipoDocumento->getDescripcion());
            $this->configurarBotonesFormulario(true);
        }
        return new ViewModel(array('form'=>$this->form,'registros'=>$this->TipoDocumento->consultarTodoTipoDocumento()));
    }
    
    public function eliminarAction()
    {
        $this->validarSession();
        $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
        $this->TipoDocumento = new TipoDocumento($this->dbAdapter);
        $id=$this->params()->fromQuery('id',null);
        if($id != null)
        {
            $this->TipoDocumento->eliminarTipoDocumento($id);
            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl().'/admin/viapago');
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
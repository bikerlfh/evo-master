<?php
namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Admin\Form\FormViaPago;
use Application\Model\Entity\ViaPago;

class ViaPagoController extends AbstractActionController
{
    private $ViaPago;
    private $form;
    public function indexAction()
    {
        // se asigna el layout admin
        $this->layout('layout/admin'); 
        // se obtiene el adapter
        $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
        // Parametro pasado por get, con el cual se sabe si se seleccionó objeto para modificar
        $id=$this->params()->fromQuery('id',null);
        
        $this->ViaPago = new ViaPago($this->dbAdapter);
        $this->form = new FormViaPago($this->getServiceLocator(),$this->getRequest()->getBaseUrl());
        $this->configurarBotonesFormulario(false);
        // Si se ha enviado parámetros por post, se evalua si se va a modificar o a guardar
        if(count($this->request->getPost())>0)
        {
            $datos=$this->request->getPost();
            // Si se envia el id de la viapago se modifica este.
            if ($datos["idViaPago"] != null) 
            {
                $returnCrud=$this->consultarMessage("errorUpdate");
                if($this->ViaPago->modificarViaPago($datos['idViaPago'],$datos['codigo'],$datos['descripcion']))
                    $returnCrud=$this->consultarMessage("okUpdate");
            }
            else
            {
                $returnCrud=$this->consultarMessage("errorSave");
                // se guarda la nueva viapago
                if($this->ViaPago->guardarViaPago($datos['codigo'],$datos['descripcion']))
                    $returnCrud=$this->consultarMessage("okSave");
            }
            return new ViewModel(array('form'=>$this->form,'msg'=>$returnCrud,'registros'=>$this->ViaPago->consultarTodoViaPago()));
        }
        // si existe el parametro $id  se consulta la viapago y se carga el formulario.
        else if(isset($id))
        {
            $this->ViaPago->consutlarViapagoPoridViaPago($this->params()->fromQuery('id'));
            $this->form->get("idViaPago")->setValue($this->ViaPago->getIdViaPago());
            $this->form->get("codigo")->setValue($this->ViaPago->getCodigo());
            $this->form->get("descripcion")->setValue($this->ViaPago->getDescripcion());
            $this->configurarBotonesFormulario(true);
        }
        return new ViewModel(array('form'=>$this->form,'registros'=>$this->ViaPago->consultarTodoViaPago()));
    }
    
    public function eliminarAction()
    {
        $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
        $this->ViaPago = new ViaPago($this->dbAdapter);
        $id=$this->params()->fromQuery('id',null);
        if($id != null)
        {
            $this->ViaPago->eliminarViaPago($id);
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
}
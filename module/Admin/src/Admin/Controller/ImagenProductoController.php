<?php

namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Admin\Form\FormImagenProducto;
use Zend\Session\Container;
use Application\Model\Entity\ImagenProducto;

class ImagenProductoController extends AbstractActionController
{
    private $form;
    private $ImagenProducto;
    
     private $user_session;
    public function __construct() {
        $this->user_session = new Container();
    }
    
    public function indexAction()
    {
        $this->validarSession();
        $this->layout('layout/admin');
        $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
        // Parametro pasado por get, con el cual se sabe si se seleccionó objeto para modificar
        $id=$this->params()->fromQuery('id',null);
        $this->ImagenProducto = new ImagenProducto($this->dbAdapter);
        $this->form = new FormImagenProducto($this->getServiceLocator(),$this->getRequest()->getBaseUrl());
        $this->configurarBotonesFormulario(false);
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
                foreach ($data['image-file'] as $imagen) 
                {
                    $path = $this->getRequest()->getBaseUrl().$imagen['tmp_name'];
                    $path = str_replace($this->getRequest()->getBaseUrl().".", $this->getRequest()->getBaseUrl(), $path);
                    $returnCrud = $this->consultarMessage("errorSave");
                    // Validamos que la imagen subida no se este guardada en la db
                    if(!$this->ImagenProducto->consultarImagenProductoPorUrl($imagen['tmp_name']))
                    {
                        if ($this->ImagenProducto->guardarImagenProducto($data['idProducto'], $path,$this->user_session->idUsuario)) 
                        {
                            $returnCrud = $this->consultarMessage("okSave");
                        }
                    }
                    else
                    {
                        $returnCrud = $this->consultarMessage("okSave");
                    }
                }
                return new ViewModel(array('form' => $this->form,'msg'=>$returnCrud,'registros'=>$this->ImagenProducto->consultarTodoImagenProducto()));
            }
        }
        // si existe el parametro $id  se consulta la proveedoroficina y se carga el formulario.
        else if(isset($id))
        {
            $this->ImagenProducto->consultarImagenProductoPorIdImagenProducto($this->params()->fromQuery('id'));
            $this->form->get("idImagenProducto")->setValue($this->ImagenProducto->getIdImagenProducto());
            $this->form->get("idProducto")->setValue($this->ImagenProducto->getIdProducto());
            $this->form->get("url")->setValue($this->ImagenProducto->getUrl());
            $this->configurarBotonesFormulario(true);
        }
        return new ViewModel(array('form' => $this->form,'registros'=>$this->ImagenProducto->consultarTodoImagenProducto()));
    }
    
    public function eliminarAction()
    {
        try 
        {
            $this->validarSession();
            //asignamos el layout
            $this->layout('layout/admin');
            $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
            $this->ImagenProducto = new ImagenProducto($this->dbAdapter);
            $returnCrud="errorDelete";
            $id=$this->params()->fromQuery('id',null);
            if (isset($id)) 
            {   
                $this->ImagenProducto->consultarImagenProductoPorIdImagenProducto($id);    
                $nombre=str_replace($this->getRequest()->getBaseUrl(),".", $this->ImagenProducto->getUrl());
                
                unlink($nombre);
                array_map('unlink', glob($nombre));
                if($this->ImagenProducto->eliminarImagenProducto($id))
                {
                    $returnCrud="okDelete";
                }
            }
            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl().'/admin/imagenproducto/index?msg='.$returnCrud);
        } 
        catch (Exception $ex) 
        {
            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl().'/admin/imagenproducto/index?msg=errorDesconocido');
        }
    }
    
    private function consultarMessage($nameMensaje)
    {
        $serviceLocator=$this->getServiceLocator()->get('Config');
        $mensaje=$serviceLocator['MsgCrud'];
        $mensaje= $mensaje[$nameMensaje];
        return $mensaje['function']."('".$mensaje['title']."','".$mensaje['message']."');";
    }
    private function configurarBotonesFormulario($eliminar)
    {
        if ($eliminar == true)
        {
            $this->form->get("btnGuardar")->setAttribute("type", "hidden");
            $this->form->get("image-file")->setAttribute("type", "hidden");
            $this->form->get("url")->setAttribute("type", "text");
            //$this->form->get("btnModificar")->setAttribute("type", "submit");
            $this->form->get("btnEliminar")->setAttribute("type", "button");
          
        }
        else
        {
            $this->form->get("image-file")->setAttribute("type", "file");
            $this->form->get("url")->setAttribute("type", "hidden");
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
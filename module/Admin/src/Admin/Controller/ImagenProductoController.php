<?php

namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Admin\Form\FormBase;
use Admin\Form\FormImagenProducto;
use Zend\Session\Container;
use Application\Model\Entity\ImagenProducto;
use Application\Model\Clases\FuncionesBase;

class ImagenProductoController extends AbstractActionController
{
    private $form;
    private $ImagenProducto;
    
     private $user_session;
    public function __construct() {
        $this->user_session = new Container('user');
    }
    
    public function indexAction()
    {
        $this->validarSession();
        $this->layout('layout/admin');
        $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
        // Parametro pasado por get, con el cual se sabe si se seleccionó objeto para modificar
        $id=$this->params()->fromQuery('id',null);
        //Parámetro que se usa para consultar todas las imagenes de ese producto.
        $idProducto = $this->params()->fromQuery('idProducto',null);
        
        $this->ImagenProducto = new ImagenProducto($this->dbAdapter);
        $this->form = new FormImagenProducto($this->getServiceLocator(),$this->getRequest()->getBaseUrl());
        /**************************************************************************************/
        // Se agregan el botones del formulario base
        /**************************************************************************************/
        $formBase = new FormBase($this->getServiceLocator(),$this->getRequest()->getBaseUrl());
        $this->form->add($formBase->get("btnBuscarProducto"));
        /**************************************************************************************/
        $this->configurarBotonesFormulario(false);
        $request = $this->getRequest();
        if ($request->isPost()) 
        {
            // Make certain to merge the files info!
            $data = array_merge_recursive(
                $request->getPost()->toArray(),
                $request->getFiles()->toArray()
            );
            /***************** SE CAMBIA EL NOMBRE DE LA IMAGEN***************/
            $numImgProducto = count($this->ImagenProducto->consultarImagenProductoPorIdProducto($data['idProducto']));
            $referencia = str_replace(' ','_', str_replace(' - ','_',$data['nombreProducto'])).'_' ;
            $i =0;
            foreach ($data['image-file'] as $imagen)
            {
                $numImgProducto++;
                $imagen['name'] = $referencia.($numImgProducto).'.'.explode ('.',$imagen['name'])[1];
                $data['image-file'][$i] = $imagen;
                $i++;
            }
             /******************************************************************/
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
                return new ViewModel(array('form' => $this->form,'msg'=>$returnCrud));
            }
        }
        // si existe el parametro $id  se consulta la proveedoroficina y se carga el formulario.
        else if(isset($id))
        {
            $this->ImagenProducto->consultarImagenProductoPorIdImagenProducto($this->params()->fromQuery('id'));
            $this->form->get("idImagenProducto")->setValue($this->ImagenProducto->getIdImagenProducto());
            $this->form->get("idProducto")->setValue($this->ImagenProducto->getIdProducto());
            $this->form->get("nombreProducto")->setValue($this->params()->fromQuery('nombreProducto'));
            $this->form->get("url")->setValue($this->ImagenProducto->getUrl());
            $this->configurarBotonesFormulario(true);
            return new ViewModel(array('form' => $this->form));
            
        }
        else if(isset($idProducto))
        {
            $this->form->get("idProducto")->setValue($idProducto);
            $this->form->get("nombreProducto")->setValue($this->params()->fromQuery('nombreProducto'));
            $this->configurarBotonesFormulario(false);
            return new ViewModel(array('form' => $this->form,'registros'=>$this->ImagenProducto->consultarImagenProductoPorIdProducto($this->params()->fromQuery('idProducto'))));

        }
        return new ViewModel(array('form' => $this->form));
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
    
    private function consultarMessage($nameMensaje,$propio = false)
    {
        return FuncionesBase::consultarMessage($this->getServiceLocator()->get('Config'), $nameMensaje, $propio);
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
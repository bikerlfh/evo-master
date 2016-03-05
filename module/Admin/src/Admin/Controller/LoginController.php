<?php
namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Session\Container;
use Application\Model\Entity\Usuario;
use Application\Model\Clases\ValoresSesion;

class LoginController extends AbstractActionController
{
    private $ValoresSesion;
    public function indexAction() 
    {
        $view=new ViewModel();
        // evita que cargue con layout
        $view->setTerminal(true);
        return $view;
    }
    
    public function loginAction()
    {
        $this->destroySession();
        //debug_zval_dump("no entro");
        $view=new ViewModel();
        $view->setTerminal(true);
        $datos=$this->request->getPost();
        if(isset($datos))
        {
            $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
            $this->Usuario=new Usuario($this->dbAdapter);
            //<== si se encontro el usuario ===>
            if ($this->Usuario->logIn($datos['email'],$datos['pass']))
            {
                // Se crea la sesion
                $this->createSession();
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl().'/admin/index');
            }
        }
        return $this->redirect()->toUrl($this->getRequest()->getBaseUrl().'/admin/login');
    }
    public function exitAction()
    {
        $this->destroySession();
        return $this->redirect()->toUrl(str_replace("/public","", $this->getRequest()->getBaseUrl()).'/admin/login');
    }
    private function createSession()
    {
        $user_session = new Container('user');
        $user_session->idUsuario=$this->Usuario->getIdUsuario();
        $user_session->username =  $this->Usuario->DatoBasicoTercero->getDescripcion();
        $user_session->tipousuario = $this->Usuario->TipoUsuario->getDescripcion(); 
        /*
        $this->ValoresSesion = ValoresSesion::obtenerInstancia();
        $this->ValoresSesion->idUsuarioSesion =$user_session->idUsuario;
        $this->ValoresSesion->username =$user_session->username;
        $this->ValoresSesion->tipousuario =$user_session->tipousuario;
        $this->ValoresSesion->Container = $user_session;*/
    }
    private function destroySession()
    {
        $session = new Container('user');
        $session->getManager()->getStorage()->clear('user');
        //\Application\Model\Clases\ValoresSesion::destruirSesion();
    }
}

<?php
/**
 * Autor :    Luis Fernando Henriquez Arciniegas
 *
 * @link      https://github.com/bikerlfh/evo-master for the source repository
 * @copyright Copyright (c) 2016 EvoMaster
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Session\Container;
use Application\Model\Entity\Usuario;

class LoginController extends AbstractActionController
{
    private $Usuario;
    
    public function indexAction()
    {
        return new ViewModel();
    }
    
    public function loginAction()
    {
        $this->destroySession();
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
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl().'/');
            }
        }
        return $this->redirect()->toUrl($this->getRequest()->getBaseUrl().'/admin/login');
    }
    public function logoutAction()
    {
        $this->destroySession();
        return $this->redirect()->toUrl(str_replace("/public","", $this->getRequest()->getBaseUrl()).'/');
    }
    private function createSession()
    {
        $user_session = new Container('user');
        $user_session->idUsuario=$this->Usuario->getIdUsuario();
        $user_session->username =  $this->Usuario->DatoBasicoTercero->getDescripcion();
        $user_session->tipousuario = $this->Usuario->TipoUsuario->getDescripcion(); 
        $user_session->timeStartSession = date('H:i:s');
        $user_session->timeLastActivity = date('H:i:s');
    }
    private function destroySession()
    {
        $session = new Container('user');
        $session->getManager()->getStorage()->clear('user');
        unset($_SESSION['user']);
    }
}

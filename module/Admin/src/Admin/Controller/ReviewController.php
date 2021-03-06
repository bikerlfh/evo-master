<?php
namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Admin\Form\FormReview;
use Application\Model\Entity\Review;
use Zend\Session\Container;


class ReviewController extends AbstractActionController
{
    private $Review;
    private $form;
    
    private $user_session;
    public function __construct() {
        $this->user_session = new Container('user');
    }
    
    public function indexAction()
    {
        $this->validarSession();
        // se asigna el layout admin
        $this->layout('layout/admin'); 
        // se obtiene el adapter
        $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
        // Parametro pasado por get, con el cual se sabe si se seleccionó objeto para modificar
        $id=$this->params()->fromQuery('idReview',null);
        
        $this->Review = new Review($this->dbAdapter);
        $this->form = new FormReview($this->getServiceLocator(),$this->getRequest()->getBaseUrl());
        $this->configurarBotonesFormulario(false);
        // Si se ha enviado parámetros por post, se evalua si se va a modificar o a guardar
        if(count($this->request->getPost())>0)
        {
            $datos=$this->request->getPost();
            // Si se envia el id de la review se modifica este.
            if ($datos["idReview"] != null) 
            {
                $returnCrud=$this->consultarMessage("errorUpdate");
                if($this->Review->modificarReview($datos['idReview'],$datos['idPedidoVentaPosicion'],$datos['puntuacion'],$datos['mensaje']))
                    $returnCrud=$this->consultarMessage("okUpdate");
            }
            else
            {
                $returnCrud=$this->consultarMessage("errorSave");
                // se guarda la nueva review
                if($this->Review->guardarReview($datos['idPedidoVentaPosicion'],$datos['puntuacion'],$datos['mensaje'],$this->user_session->idUsuario))
                    $returnCrud=$this->consultarMessage("okSave");
            }
            return new ViewModel(array('form'=>$this->form,'msg'=>$returnCrud));
        }
        // si existe el parametro $id  se consulta la review y se carga el formulario.
        else if(isset($id))
        {
            $this->Review->consultarReviewPorIdReview($this->params()->fromQuery('idReview'));
            $this->form->get("idReview")->setValue($this->Review->getIdReview());
            $this->form->get("idPedidoVentaPosicion")->setValue($this->Review->getIdPedidoVentaPosicion());
            $this->form->get("puntuacion")->setValue($this->Review->getPuntuacion());
            $this->form->get("mensaje")->setValue($this->Review->getMensaje());
            $this->configurarBotonesFormulario(true);
        }
        return new ViewModel(array('form'=>$this->form));
    }
    public function buscarAction()
    {
        $this->validarSession();
        // se obtiene el adapter
        $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
        $this->Review = new Review($this->dbAdapter);
        
       // Parametro que se utiliza para determinar si se va a redirigir a alguna vista en particular el id del saldo inventario seleccionado
        // Si el origen es saldoinventario/index, al dar click en la fila, esta debe redirigir al formualrio de saldo inventario
        $origen = $this->params()->fromQuery('origen', null);
        //**** OJO: la Uri se debe enviar a la busqueda *****//
        $Uri = $this->getRequest()->getRequestUri();
        
        // consultamos todas las reviews y los devolvemos a la vista    
        $view = new ViewModel(array('Uri'=> $Uri,
                                    'origen'=> $origen,
                                    'registros'=>$this->Review->consultarTodoReview()));
        $view->setTerminal(true);
        return $view;
    }
    public function eliminarAction()
    {
        //$this->validarSession();
        $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
        $this->Review = new Review($this->dbAdapter);
        $id=$this->params()->fromQuery('id',null);
        if($id != null)
        {
            $this->Review->eliminarReview($id);
            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl().'/admin/review');
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
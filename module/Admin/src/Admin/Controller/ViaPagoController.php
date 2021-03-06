<?php
namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Admin\Form\FormViaPago;
use Application\Model\Entity\ViaPago;
use Application\Model\Clases\FuncionesBase;
use Zend\Session\Container;

class ViaPagoController extends AbstractActionController
{
    private $ViaPago;
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
        $id=$this->params()->fromQuery('id',null);
        
        $this->ViaPago = new ViaPago($this->dbAdapter);
        $this->form = new FormViaPago($this->getServiceLocator(),$this->getRequest()->getBaseUrl());
        $this->configurarBotonesFormulario(false);
        // Si se ha enviado parámetros por post, se evalua si se va a modificar o a guardar
        if(count($this->request->getPost())>0)
        {
            try {
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
            } catch (\Exception $e) {
                $returnCrud = $this->consultarMessage($e->getMessage(), true);
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
        $this->validarSession();
        $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
        $this->ViaPago = new ViaPago($this->dbAdapter);
        $id=$this->params()->fromQuery('id',null);
        if($id != null)
        {
            $this->ViaPago->eliminarViaPago($id);
            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl().'/admin/viapago');
        }
    }
    private function consultarMessage($nameMensaje, $propio = false) {

        return FuncionesBase::consultarMessage($this->getServiceLocator()->get('Config'), $nameMensaje, $propio);
    }

    private function configurarBotonesFormulario($modificarBool) {

        FuncionesBase::configurarBotonesFormulario($this->form, $modificarBool);
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
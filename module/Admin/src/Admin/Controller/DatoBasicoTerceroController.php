<?php
namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Admin\Form\FormDatoBasicoTercero;
use Application\Model\Entity\DatoBasicoTercero;
use Zend\Session\Container;

class DatoBasicoTerceroController extends AbstractActionController
{
    private $DatoBasicoTercero;
    private $form;
    
    private $user_session;
    public function __construct() {
        $this->user_session = new Container('user');
    }
    
    public function indexAction()
    {
        //$this->validarSession();
        // se asigna el layout admin
        $this->layout('layout/admin'); 
        // se obtiene el adapter
        $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
        // Parametro pasado por get, con el cual se sabe si se seleccionó objeto para modificar
        $id=$this->params()->fromQuery('idDatoBasicoTercero',null);
        
        $this->DatoBasicoTercero = new DatoBasicoTercero($this->dbAdapter);
        $this->form = new FormDatoBasicoTercero($this->getServiceLocator(),$this->getRequest()->getBaseUrl());
        $this->configurarBotonesFormulario(false);
        // Si se ha enviado parámetros por post, se evalua si se va a modificar o a guardar
        if(count($this->request->getPost())>0)
        {
            $datos=$this->request->getPost();
            // Si se envia el id de la categoria se modifica este.
            if ($datos["idDatoBasicoTercero"] != null) 
            {
                $returnCrud=$this->consultarMessage("errorUpdate");
                if($this->DatoBasicoTercero->modificarDatoBasicoTercero($datos['idDatoBasicoTercero'],$datos['idTipoDocumento'],$datos['nit'],$datos['descripcion'],$datos['nombre'],$datos['apellido'],$datos['direccion'],$datos['telefono']))
                    $returnCrud=$this->consultarMessage("okUpdate");
            }
            else
            {
                $returnCrud=$this->consultarMessage("errorSave");
                // se guarda la nueva categoria
                if($this->DatoBasicoTercero->guardarDatoBasicoTercero($datos['idTipoDocumento'],$datos['nit'],$datos['descripcion'],$datos['nombre'],$datos['apellido'],$datos['direccion'],$datos['telefono']))
                    $returnCrud=$this->consultarMessage("okSave");
            }
            return new ViewModel(array('form'=>$this->form,'msg'=>$returnCrud));
        }
        // si existe el parametro $id  se consulta la categoria y se carga el formulario.
        else if(isset($id))
        {
            $this->DatoBasicoTercero->consultarDatoBasicoTerceroPorIdDatoBasicoTercero($this->params()->fromQuery('idDatoBasicoTercero'));
            $this->form->get("idDatoBasicoTercero")->setValue($this->DatoBasicoTercero->getIdDatoBasicoTercero());
            $this->form->get("idTipoDocumento")->setValue($this->DatoBasicoTercero->getIdTipoDocumento());
            $this->form->get("nit")->setValue($this->DatoBasicoTercero->getNit());
            $this->form->get("descripcion")->setValue($this->DatoBasicoTercero->getDescripcion());
            $this->form->get("nombre")->setValue($this->DatoBasicoTercero->getNombre());
            $this->form->get("apellido")->setValue($this->DatoBasicoTercero->getApellido());
            $this->form->get("direccion")->setValue($this->DatoBasicoTercero->getDireccion());
            $this->form->get("telefono")->setValue($this->DatoBasicoTercero->getTelefono());
            $this->configurarBotonesFormulario(true);
        }
        return new ViewModel(array('form'=>$this->form));
    }
    public function buscarAction()
    {
        $this->validarSession();
        // se obtiene el adapter
        $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
        
        $this->form = new FormDatoBasicoTercero($this->getServiceLocator(),$this->getRequest()->getBaseUrl());
        /** Campos para saber en donde se deben devolver los valores de la busqueda **/
        $campoId=$this->params()->fromQuery('campoId',null) == null? 'idDatoBasicoTercero':$this->params()->fromQuery('campoId',null);
        $campoNombre=$this->params()->fromQuery('campoNombre',null)== null?'nombreTercero':$this->params()->fromQuery('campoNombre',null);
        
        // Parametro que se utiliza para determinar si se va a redirigir a alguna vista en particular el id del saldo inventario seleccionado
        // Si el origen es saldoinventario/index, al dar click en la fila, esta debe redirigir al formualrio de saldo inventario
        $origen = $this->params()->fromQuery('origen', null);
        //**** OJO: la Uri se debe enviar a la busqueda *****//
        $Uri = $this->getRequest()->getRequestUri();
        
        $registros = array();
        if(count($this->request->getPost()) > 0)
        {
            $this->DatoBasicoTercero = new DatoBasicoTercero($this->dbAdapter);
            $datos = $this->request->getPost();
            
            $this->form->get("nit")->setValue($datos['nit']);
            $this->form->get("descripcion")->setValue($datos['descripcion']);
            
            $registros = $this->DatoBasicoTercero->consultaAvanzadaDatoBasicoTercero($datos['nit'], $datos['descripcion']);
        }
        // consultamos todos los terceros y los devolvemos a la vista    
        $view = new ViewModel(array('form'=> $this->form,
                                    'campoId'=>$campoId, 
                                    'campoNombre'=> $campoNombre,
                                    'Uri'=> $Uri,
                                    'origen'=> $origen,
                                    'registros'=> $registros));
        $view->setTerminal(true);
        return $view;
    }
    public function eliminarAction()
    {
        //$this->validarSession();
        $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
        $this->DatoBasicoTercero = new DatoBasicoTercero($this->dbAdapter);
        $id=$this->params()->fromQuery('id',null);
        if($id != null)
        {
            $this->DatoBasicoTercero->eliminarDatoBasicoTercero($id);
            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl().'/admin/datobasicotercero');
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
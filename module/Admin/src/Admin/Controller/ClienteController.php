<?php
namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Admin\Form\FormBase;
use Admin\Form\FormCliente;
use Application\Model\Entity\Cliente;
use Zend\Session\Container;
use Admin\Form\FormDatoBasicoTercero;


class ClienteController extends AbstractActionController
{
    private $Cliente;
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
        $id=$this->params()->fromQuery('idCliente',null);
        
        $this->Cliente = new Cliente($this->dbAdapter);
        $this->form = new FormCliente($this->getServiceLocator(),$this->getRequest()->getBaseUrl());
        /**************************************************************************************/
        // Se agregan el boton de buscar Tercero
        /**************************************************************************************/
        $formBase = new FormBase($this->getServiceLocator(),$this->getRequest()->getBaseUrl());
        $this->form->add($formBase->get("btnBuscarTercero"));
        /**************************************************************************************/
        $this->configurarBotonesFormulario(false);
        // Si se ha enviado parámetros por post, se evalua si se va a modificar o a guardar
        if(count($this->request->getPost())>0)
        {
            $datos=$this->request->getPost();
            // Si se envia el id de la proveedor se modifica este.
            if ($datos["idCliente"] != null) 
            {
                $returnCrud=$this->consultarMessage("errorUpdate");
                if($this->Cliente->modificarCliente($datos['idCliente'],$datos['idDatoBasicoTercero'],$datos['idMunicipio'],$datos['email'],$datos['direccion'],$datos['telefono']))
                    $returnCrud=$this->consultarMessage("okUpdate");
            }
            else
            {
                $returnCrud=$this->consultarMessage("errorSave");
                // se guarda la nueva proveedor
                if($this->Cliente->guardarCliente($datos['idDatoBasicoTercero'],$datos['idMunicipio'],$datos['email'],$datos['direccion'],$datos['telefono'],$this->user_session->idUsuario))
                    $returnCrud=$this->consultarMessage("okSave");
            }
                return new ViewModel(array('form'=>$this->form,'msg'=>$returnCrud));
        }
        // si existe el parametro $id  se consulta la proveedor y se carga el formulario.
        else if(isset($id))
        {
            $this->Cliente->consultarClientePorIdCliente($this->params()->fromQuery('idCliente'));
            $this->form->get("idCliente")->setValue($this->Cliente->getIdCliente());
            
            //campos Tercero
            $this->form->get("idDatoBasicoTercero")->setValue($this->Cliente->getIdDatoBasicoTercero());
            $descripcionTercero = $this->Cliente->DatoBasicoTercero->getNit() . ' - ' . $this->Cliente->DatoBasicoTercero->getDescripcion();
            $this->form->get("nombreTercero")->setValue($descripcionTercero);
            
            $this->form->get("idMunicipio")->setValue($this->Cliente->getIdMunicipio());
            
            $this->form->get("email")->setValue($this->Cliente->getEmail());
            $this->form->get("telefono")->setValue($this->Cliente->getTelefono());
            $this->form->get("direccion")->setValue($this->Cliente->getDireccion());
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
        $campoId=$this->params()->fromQuery('campoId',null) == null? 'idCliente':$this->params()->fromQuery('campoId',null);
        $campoNombre=$this->params()->fromQuery('campoNombre',null)== null?'nombreCliente':$this->params()->fromQuery('campoNombre',null);
        
        // Parametro que se utiliza para determinar si se va a redirigir a alguna vista en particular el id del saldo inventario seleccionado
        // Si el origen es saldoinventario/index, al dar click en la fila, esta debe redirigir al formualrio de saldo inventario
        $origen = $this->params()->fromQuery('origen', null);
        
        //**** OJO: la Uri se debe enviar a la busqueda *****//
        $Uri = $this->getRequest()->getRequestUri();
        
        $registros = array();
        if(count($this->request->getPost()) > 0)
        {
            $this->Cliente = new Cliente($this->dbAdapter);
            $datos = $this->request->getPost();
            
            $this->form->get("nit")->setValue($datos["nit"]);
            $this->form->get("descripcion")->setValue($datos["descripcion"]);
            
            $registros = $this->Cliente->consultaAvanzadaCliente($datos["nit"],$datos["descripcion"]);
        }
        
        // consultamos todos los Clientees y los devolvemos a la vista    
        $view = new ViewModel(array('form'=>$this->form,
                                    'campoId'=>$campoId,
                                    'campoNombre'=>$campoNombre,
                                    'Uri'=> $Uri,
                                    'origen'=>$origen,
                                    'registros'=>$registros ));
        $view->setTerminal(true);
        return $view;
    }
    
    public function eliminarAction()
    {
        //$this->validarSession();
        $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
        $this->Cliente = new Cliente($this->dbAdapter);
        $id=$this->params()->fromQuery('id',null);
        if($id != null)
        {
            $this->Cliente->eliminarCliente($id);
            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl().'/admin/cliente');
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
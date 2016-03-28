<?php
namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Admin\Form\FormPromocion;
use Application\Model\Entity\Promocion;
use Zend\Session\Container;


class PromocionController extends AbstractActionController
{
    private $Promocion;
    private $form;
    
    private $user_session;
    public function __construct() {
        $this->user_session = new Container();
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
        
        $this->Promocion = new Promocion($this->dbAdapter);
        $this->form = new FormPromocion($this->getServiceLocator(),$this->getRequest()->getBaseUrl());
        $this->form ->get('btnBuscarSaldoInventario')->setAttribute('onClick',"usar_ajax('".$this->getRequest()->getBaseUrl()."/admin/saldoinventario/buscar?campoValorVenta=valorAnterior','#modal-dialog-display','')");
        $this->configurarBotonesFormulario(false);
        // Si se ha enviado parámetros por post, se evalua si se va a modificar o a guardar
        if(count($this->request->getPost())>0)
        {
            $datos=$this->request->getPost();
            $fechaDesde = new \DateTime($datos['fechaDesde']);
            $fechaDesde =$fechaDesde->format('d-m-Y');
            $fechaHasta = new \DateTime($datos['fechaHasta']);
            $fechaHasta =$fechaHasta->format('d-m-Y');
            // Si se envia el id de la promocion se modifica este.
            if ($datos["idPromocion"] != null) 
            {
                $returnCrud=$this->consultarMessage("errorUpdate");
                if($this->Promocion->modificarPromocion($datos['idPromocion'],$datos['idSaldoInventario'],$datos['valorAnterior'],$datos['valorPromocion'],$fechaDesde,$fechaHasta,$datos['estado']))
                    $returnCrud=$this->consultarMessage("okUpdate");
            }
            else
            {
                $returnCrud=$this->consultarMessage("errorSave");
                // se guarda la nueva promocion
                if($this->Promocion->guardarPromocion($datos['idSaldoInventario'],$datos['valorAnterior'],$datos['valorPromocion'],$fechaDesde,$fechaHasta,$datos['estado'],$this->user_session->idUsuario))
                    $returnCrud=$this->consultarMessage("okSave");
            }
            return new ViewModel(array('form'=>$this->form,'msg'=>$returnCrud));
        }
        // si existe el parametro $id  se consulta la promocion y se carga el formulario.
        else if(isset($id))
        {
            $this->Promocion->consultarPromocionPorIdPromocion($this->params()->fromQuery('id'));
            $this->form->get("idPromocion")->setValue($this->Promocion->getIdPromocion());
            $this->form->get("idSaldoInventario")->setValue($this->Promocion->getIdSaldoInventario());
            $this->form->get("valorAnterior")->setValue($this->Promocion->getValorAnterior());
            $this->form->get("valorPromocion")->setValue($this->Promocion->getValorPromocion());
            $this->form->get("fechaDesde")->setValue($this->Promocion->getFechaDesde());
            $this->form->get("fechaHasta")->setValue($this->Promocion->getFechaHasta());
            $this->form->get("estado")->setValue($this->Promocion->getEstado());
            $this->configurarBotonesFormulario(true);
        }
        return new ViewModel(array('form'=>$this->form));
    }
    public function buscarAction()
    {
        $this->validarSession();
        // se obtiene el adapter
        $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
        $this->Promocion = new Promocion($this->dbAdapter);
        
         //****Campos modal *****//
        $botonClose = $this->params()->fromQuery('botonClose',null) == null ? 'btnClosePop' :$this->params()->fromQuery('botonClose',null);
        $contenedorDialog = $this->params()->fromQuery('contenedorDialog',null) == null ? 'modal-dialog-display' :$this->params()->fromQuery('contenedorDialog',null);
        $modal = $this->params()->fromQuery('modal',null) == null ? 'textModal' :$this->params()->fromQuery('modal',null);
        
        
        // consultamos todas las promocions y los devolvemos a la vista    
        $view = new ViewModel(array('botonClose'=> $botonClose,
                                    'contenedorDialog'=> $contenedorDialog,
                                    'modal'=> $modal,
                                    'registros'=>$this->Promocion->consultarTodoPromocion()));
        $view->setTerminal(true);
        return $view;
    }
    public function eliminarAction()
    {
        //$this->validarSession();
        $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
        $this->Promocion = new Promocion($this->dbAdapter);
        $id=$this->params()->fromQuery('id',null);
        if($id != null)
        {
            $this->Promocion->eliminarPromocion($id);
            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl().'/admin/promocion');
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
        $this->user_session = $_SESSION['user'];
    }
}
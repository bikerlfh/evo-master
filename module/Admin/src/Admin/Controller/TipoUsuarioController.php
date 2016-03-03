<?php

namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Admin\Form\FormTipoUsuario;
use Application\Model\Entity\TipoUsuario;

class TipoUsuarioController extends AbstractActionController
{
    private $TipoUsuario;
    public function indexAction()
    {
        $this->layout('layout/admin'); 
        $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
        $from = new FormTipoUsuario("viaPago",$this->getServiceLocator());
        if(count($this->request->getPost())>0)
        {
            $datos=$this->request->getPost();
            $this->TipoUsuario = new TipoUsuario($this->dbAdapter);
            // Si se envia el id de la viapago,
            // se debe modificar esta.
            if (isset($datos["idTipoUsuario"])) 
            {
                $returnCrud="errorSave";
                if($this->TipoUsuario->modificarTipoUsuario($datos['idTipoUsuario'],$datos['codigo'],$datos['descripcion']))
                    $returnCrud="okSave";
            }
            else
            {
                $returnCrud="errorSave";
                // se guarda la nueva via pago
                if($this->TipoUsuario->guardarTipoUsuario($datos['codigo'],$datos['descripcion']))
                    $returnCrud="okSave";           
            }
            return new ViewModel(array('form'=>$from,'msg'=>$returnCrud));
        }
        return new ViewModel(array('form'=>$from));
    }
}
<?php
namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Admin\Form\FormTipoCuenta;
use Application\Model\Entity\TipoCuenta;

class TipoCuentaController extends AbstractActionController
{
    private $TipoCuenta;
    public function indexAction()
    {
        $this->layout('layout/admin'); 
        $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
        $from = new FormTipoCuenta("tipocuenta",$this->getServiceLocator());
        if(count($this->request->getPost())>0)
        {
            $datos=$this->request->getPost();
            $this->TipoCuenta = new TipoCuenta($this->dbAdapter);
            // Si se envia el id de la tipo cuenta,
            // se debe modificar esta.
            if (!isset($datos["idTipoCuenta"])) 
            {
                $returnCrud="errorSave";
                if($this->TipoCuenta->modificarTipoCuenta($datos['idTipoCuenta'],$datos['codigo'],$datos['descripcion']))
                    $returnCrud="okSave";
            }
            else
            {
                $returnCrud="errorSave";
                // se guarda la nueva tipo cuenta
                if($this->TipoCuenta->guardarTipoCuenta($datos['codigo'],$datos['descripcion']))
                    $returnCrud="okSave";           
            }
            return new ViewModel(array('form'=>$from,'msg'=>$returnCrud));
        }
        return new ViewModel(array('form'=>$from));
    }
}
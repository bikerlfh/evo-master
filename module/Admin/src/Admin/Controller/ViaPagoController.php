<?php

namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Admin\Form\FormViaPago;
use Application\Model\Entity\ViaPago;

class ViaPagoController extends AbstractActionController
{
    private $ViaPago;
    public function indexAction()
    {
        $this->layout('layout/admin'); 
        $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
        $from = new FormViaPago("viaPago",$this->getServiceLocator());
        if(count($this->request->getPost())>0)
        {
            $datos=$this->request->getPost();
            $this->ViaPago = new ViaPago($this->dbAdapter);
            // Si se envia el id de la viapago,
            // se debe modificar esta.
            if (!isset($datos["idViaPago"])) 
            {
                $returnCrud="errorSave";
                if($this->ViaPago->modificarViaPago($datos['idViaPago'],$datos['codigo'],$datos['descripcion']))
                    $returnCrud="okSave";
            }
            else
            {
                $returnCrud="errorSave";
                // se guarda la nueva via pago
                if($this->ViaPago->guardarViaPago($datos['codigo'],$datos['descripcion']))
                    $returnCrud="okSave";           
            }
            return new ViewModel(array('form'=>$from,'msg'=>$returnCrud));
        }
        return new ViewModel(array('form'=>$from));
    }
}

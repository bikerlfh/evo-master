<?php

namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Admin\Form\FormTipoDocumento;
use Application\Model\Entity\TipoDocumento;
class TipoDocumentoController extends AbstractActionController
{
    private $TipoDocumento;
    public function indexAction()
    {
        $this->layout('layout/admin'); 
        $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
        $from = new FormTipoDocumento("tipodocumento",$this->getServiceLocator());
        if(count($this->request->getPost())>0)
        {
            $datos=$this->request->getPost();
            $this->TipoDocumento = new TipoDocumento($this->dbAdapter);
            // Si se envia el id de la viapago,
            // se debe modificar esta.
            if (!isset($datos["idTipoDocumento"])) 
            {
                $returnCrud="errorSave";
                if($this->TipoDocumento->modificarTipoDocumento($datos['idTipoDocumento'],$datos['codigo'],$datos['descripcion']))
                    $returnCrud="okSave";
            }
            else
            {
                $returnCrud="errorSave";
                // se guarda la nueva tipo documento
                if($this->TipoDocumento->guardarTipoDocumento($datos['codigo'],$datos['descripcion']))
                    $returnCrud="okSave";           
            }
            return new ViewModel(array('form'=>$from,'msg'=>$returnCrud));
        }
        return new ViewModel(array('form'=>$from));
    }
}

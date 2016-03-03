<?php
namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Admin\Form\FormEstadoPedido;
use Application\Model\Entity\EstadoPedido;

class EstadoPedidoController extends AbstractActionController
{
    private $EstadoPedido;
    public function indexAction()
    {
        $this->layout('layout/admin'); 
        $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
        $from = new FormEstadoPedido("estadopedido",$this->getServiceLocator());
        if(count($this->request->getPost())>0)
        {
            $datos=$this->request->getPost();
            $this->EstadoPedido = new EstadoPedido($this->dbAdapter);
            // Si se envia el id del estado pedido,
            // se debe modificar esta.
            if (!isset($datos["idEstadoPedido"])) 
            {
                $returnCrud="errorSave";
                if($this->EstadoPedido->modificarEstadoPedido($datos['idEstadoPedido'],$datos['codigo'],$datos['descripcion']))
                    $returnCrud="okSave";
            }
            else
            {
                $returnCrud="errorSave";
                // se guarda la nueva categoria
                if($this->EstadoPedido->guardarEstadoPedido($datos['codigo'],$datos['descripcion']))
                    $returnCrud="okSave";           
            }
            return new ViewModel(array('form'=>$from,'msg'=>$returnCrud));
        }
        return new ViewModel(array('form'=>$from));
    }
}
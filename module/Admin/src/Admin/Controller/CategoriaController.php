<?php
namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Admin\Form\FormCategoria;
use Application\Model\Entity\Categoria;

class CategoriaController extends AbstractActionController
{
    private $Categoria;
    public function indexAction()
    {
        $this->layout('layout/admin'); 
        $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
        $from = new FormCategoria("categoria",$this->getServiceLocator());
        if(count($this->request->getPost())>0)
        {
            $datos=$this->request->getPost();
            $this->Categoria = new Categoria($this->dbAdapter);
            // Si se envia el id de la categoria,
            // se debe modificar esta.
            if (isset($datos["idCategoria"])) 
            {
                $returnCrud="errorSave";
                if($this->Categoria->modificarCategoria($datos['idCategoria'],$datos['codigo'],
                                                        $datos['descripcion'],1))
                    $returnCrud="okSave";
            }
            else
            {
                $returnCrud="errorSave";
                // se guarda la nueva categoria
                if($this->Categoria->guardarCategoria($datos['codigo'],$datos['descripcion'],1))
                    $returnCrud="okSave";           
            }
            return new ViewModel(array('form'=>$from,'msg'=>$returnCrud));
        }
        return new ViewModel(array('form'=>$from));
    }
}
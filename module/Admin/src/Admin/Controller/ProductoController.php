<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonModule for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Admin\Form\FormCategoria;
use Application\Model\Entity\Categoria;

class ProductoController extends AbstractActionController
{
    private $Categoria;
    
    public function categoriaAction()
    {
        $this->layout('layout/admin'); 
        $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
        $from = new FormCategoria("categoria",$this->getServiceLocator());
        if(count($this->request->getPost())>0)
        {
            $datos=$this->request->getPost();
            if (isset($datos['idCategoria'])) 
            {
                $this->Categoria = new Categoria($this->dbAdapter);
                if($this->Categoria->guardarCategoria($datos['codigo'],$datos['descripcion'],1))
                    $returnCrud="okSave";
                else
                    $returnCrud="errorSave";            
                return new ViewModel(array('form'=>$from,'msg'=>$returnCrud));
            }     
        }
        return new ViewModel(array('form'=>$from));
    }  
}

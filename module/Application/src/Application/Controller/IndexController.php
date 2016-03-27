<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Application\Model\Entity;

class IndexController extends AbstractActionController
{
    private $Categoria;
    private $Marca;
    public function indexAction()
    {
        $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
        $this->Categoria = new Entity\Categoria($this->dbAdapter);
        $this->Marca = new Entity\Marca($this->dbAdapter);
        //$this->Categoria->consultarTodoCategoria();
        return new ViewModel(array('categorias'=>$this->Categoria->consultarTodoCategoria(),
                                   'marcas'=>$this->Marca->consultarTodoMarca()));
    }
}

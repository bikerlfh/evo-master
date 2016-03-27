<?php
/**
 * Autor :    Luis Fernando Henriquez Arciniegas
 *
 * @link      https://github.com/bikerlfh/evo-master for the source repository
 * @copyright Copyright (c) 2016 EvoMaster
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
        
        return new ViewModel(array('categorias'=>$this->Categoria->consultarTodoCategoriaCountNumeroProductos(),
                                   'marcas'=>$this->Marca->consultarTodoMarcaCountNumeroProductos()));
    }
}

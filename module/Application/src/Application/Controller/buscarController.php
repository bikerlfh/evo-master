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

class BuscarController extends AbstractActionController
{
    private $Categoria;
    private $Marca;
    private $Producto;
    
    
    public function indexAction()
    {
        $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
        $this->Producto = new Entity\Producto($this->dbAdapter);
        $this->Categoria = new Entity\Categoria($this->dbAdapter);
        $this->Marca = new Entity\Marca($this->dbAdapter);
        $where = array();
        foreach ($this->params()->fromQuery() as $parametro => $value)
        {
            switch($parametro)
            {
                case "nombreProducto":
                    $where['nombre']=$value;
                    break;
                case "idMarca":
                    $where['idMarca']=$value;
                    break;
                case "idCategoria":
                    $where['idCategoria']=$value;
                    break;
            }
        }
        $productos = $this->Producto->vistaConsultaProducto($where);
        
        return new ViewModel(array('categorias'=>$this->Categoria->consultarTodoCategoriaCountNumeroProductos(),
                                   'marcas'=>$this->Marca->consultarTodoMarcaCountNumeroProductos(),
                                   'productos'=>$productos));
    }
    
    private function consultarMessage($nameMensaje)
    {
        $serviceLocator=$this->getServiceLocator()->get('Config');
        $mensaje=$serviceLocator['MsgCliente'][$nameMensaje];
        return $mensaje['function']."('".$mensaje['title']."','".$mensaje['message']."');";
    }
}

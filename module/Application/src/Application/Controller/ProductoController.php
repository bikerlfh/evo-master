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
use Application\Model\Entity\Producto;
use Application\Model\Entity\ImagenProducto;

class ProductoController extends AbstractActionController
{
    private $Producto;
    private $ImagenProducto;
    
    public function indexAction()
    {
        $id =  $this->params()->fromRoute('idProducto', 0);
        
        $param = $this->getEvent()->getRouteMatch()->getParams('idProducto');
        $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
        $this->Producto = new Entity\Producto($this->dbAdapter);
        
        return new ViewModel();
    }
    
    public function productodetalleAction()
    {
        $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
        $this->Producto =  new Producto($this->dbAdapter);
        $idSaldoInventario =  $this->params()->fromRoute('idSaldoInventario', 0);
        $idPromocion =  $this->params()->fromQuery('promocion', 0);
        $producto = null;
        $where = array();
        if ($idSaldoInventario > 0) 
        {
            $where = array('idSaldoInventario'=>$idSaldoInventario);
        }
        else if($idPromocion > 0)
        {
            $where = array('idPromocion'=>$idPromocion);
        }
        
        $producto = $this->Producto->vistaConsultaProducto($where);
        if(count($producto)> 0){
            $producto = $producto[0];
        }
        if(count($producto) > 0)
        {
            $this->ImagenProducto =  new ImagenProducto($this->dbAdapter);
            $imagenes = $this->ImagenProducto->consultarImagenProductoPorIdProducto($producto['idProducto']);
            return new ViewModel(array('Producto'=>$producto,'imagenesProducto'=>$imagenes));
        }
        return $this->redirect()->toRoute('home');
    }
}

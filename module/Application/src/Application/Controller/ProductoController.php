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
use Application\Model\Clases\BusquedaCliente;
use Application\Model\Entity\ImagenProducto;

class ProductoController extends AbstractActionController
{
    private $Producto;
    private $BusquedaCliente;
    private $ImagenProducto;
    
    public function indexAction()
    {
        return new ViewModel();
    }
    
    public function productodetalleAction()
    {
        $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
        $this->BusquedaCliente =  new BusquedaCliente($this->dbAdapter);
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
        
        $producto = $this->BusquedaCliente->vistaConsultaProducto($where);
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

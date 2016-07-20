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

class CartController extends AbstractActionController
{
    private $BusquedaCliente;
    public function indexAction()
    {
        return new ViewModel();
    }
    
    public function addToCartAction()
    {
        if (count($this->request->getPost()) > 0) 
        {
            $data = $this->request->getPost();
            $idSaldoInventario = $data['idSaldoInventario'];
            
            $productoCargado = false;
            // Se valida si el producto ya esta en el carrito
            if (count($this->ShoppingCart()->cart()) > 0) {
                foreach ($this->ShoppingCart()->cart() as $token => $value) {
                    if($value->getId() == $idSaldoInventario)
                    {
                        $cantidad = $value->getQty() + $data['qty'];
                        $value->setQty($cantidad);
                        $productoCargado = true;
                        // si el producto no tiene cantidad se remueve del carro
                         if ($cantidad <= 0) {
                            $this->ShoppingCart()->remove($token);
                        }
                        break;
                    }
                }
            }
            // Si el producto no esta cargado en el carrito, se procede a agregarse.
            if(!$productoCargado)
            {
                $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
                $this->BusquedaCliente =  new BusquedaCliente($this->dbAdapter);
                $where = array('idSaldoInventario'=>$idSaldoInventario);
                $Producto = $this->BusquedaCliente->vistaConsultaProductoSimple($where);
                if (count($Producto)>0) 
                {
                    $Producto = $Producto[0];
                    $precio = $Producto['idPromocion'] != null? $Producto['valorPromocion'] : $Producto['valorVenta'];
                    $product = array(
                        'id'         => $Producto['idSaldoInventario'],
                        'codigo'     => $Producto['codigo'],
                        'referencia' => $Producto['referencia'],
                        'qty'        => $data['qty'],
                        'price'      => $precio,
                        'product'    => $Producto['nombre'],
                        'imgUrl'     => $Producto['urlImg'],
                    );
                    $this->ShoppingCart()->insert($product);
                }
            }
        }
        $view = new ViewModel();    
        $view->setTerminal(true);
        return $this->redirect()->toUrl($this->getRequest()->getBaseUrl().'/application/cart/viewcart');
    }
    public function deleteToCartAction()
    {
        if (count($this->request->getPost()) > 0)
        {
            $token = $this->request->getPost()['token'];
            if (!empty($token)) {
                $this->ShoppingCart()->remove($token);
            }
        }
        $view = new ViewModel();    
        $view->setTerminal(true);
        return $this->redirect()->toUrl($this->getRequest()->getBaseUrl().'/application/cart/viewcart');
    }
    
    public function cartAction()
    {
        return new ViewModel(array('cart'=>$this->ShoppingCart()->cart(),
                                   'total_items' => $this->ShoppingCart()->total_items(),
                                   'total_sum' => $this->ShoppingCart()->total_sum()));
    }
    
    public function viewcartAction()
    {
        $view = new ViewModel(array('cart'=>$this->ShoppingCart()->cart(),
                                    'total_items' => $this->ShoppingCart()->total_items(),
                                    'total_sum' => $this->ShoppingCart()->total_sum()));    
        $view->setTerminal(true);
        return $view;
    }
}

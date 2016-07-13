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
use Application\Model\Entity;

class BuscarController extends AbstractActionController
{
    private $Categoria;
    private $Marca;
    private $BusquedaCliente;
    
    
    public function indexAction()
    {
        $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
        $this->BusquedaCliente = new BusquedaCliente($this->dbAdapter);
        $this->Categoria = new Entity\Categoria($this->dbAdapter);
        $this->Marca = new Entity\Marca($this->dbAdapter);
        $where = array();
        $idMarca = null;
        $idCategoria = null;
        $filtro = null;
        $pageSize = 50;
        $pageNumber = 1;
        // Se recorren los parametros para generar el Where
        foreach ($this->params()->fromQuery() as $parametro => $value)
        {
            switch($parametro)
            {
                case "filtro":
                    $filtro = $value;
                    //$where->like('nombre', "%".$value."%")->like('descripcionMarca', "%".$value."%");
                    $like =" LIKE '%".$value."%'";
                    $where = array(" nombre ".$like." OR descripcionMarca ".$like." OR descripcionCategoria ".$like);
                    break;
                case "idMarca":
                    $where['idMarca']=$value;
                    $idMarca = $value;
                    break;
                case "idCategoria":
                    $where['idCategoria']=$value;
                    $idCategoria = $value;
                    break;
                case "pageSize":
                    $pageSize = $value;
                    break;
                case "pageNumber":
                    $pageNumber = $value;
                    break;
            }
        }
        //$productos = $this->BusquedaCliente->vistaConsultaProducto($where);
        $productos = $this->BusquedaCliente->busquedaProductoPaginada($pageSize,$pageNumber,$idMarca,$idCategoria,$filtro);
        
        return new ViewModel(array('categorias'=>$this->Categoria->consultarTodoCategoriaCountNumeroProductos(),
                                   'marcas'=>$this->Marca->consultarTodoMarcaCountNumeroProductos(),
                                   'productos'=>$productos,
                                   'pageCount'=>$this->BusquedaCliente->pageCount,
                                   'pageNumber'=> $pageNumber));
    }
    
    private function consultarMessage($nameMensaje)
    {
        $serviceLocator=$this->getServiceLocator()->get('Config');
        $mensaje=$serviceLocator['MsgCliente'][$nameMensaje];
        return $mensaje['function']."('".$mensaje['title']."','".$mensaje['message']."');";
    }
}

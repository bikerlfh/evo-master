<?php
namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Admin\Form\FormCategoria;
use Application\Model\Entity\Categoria;

class CategoriaController extends AbstractActionController
{
    private $Categoria;
    private $form;
    public function indexAction()
    {
        // se asigna el layout admin
        $this->layout('layout/admin'); 
        // se obtiene el adapter
        $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
        // Parametro pasado por get, con el cual se sabe si se seleccionó objeto para modificar
        $id=$this->params()->fromQuery('id',null);
        
        $this->Categoria = new Categoria($this->dbAdapter);
        $this->form = new FormCategoria("frmcategoria",$this->getServiceLocator());
        
        // Si se ha enviado parámetros por post, se evalua si se va a modificar o a guardar
        if(count($this->request->getPost())>0)
        {
            $datos=$this->request->getPost();
            // Si se envia el id de la categoria se modifica este.
            if ($datos["idCategoria"] != null) 
            {
                $returnCrud=$this->consultarMessage("errorUpdate");
                if($this->Categoria->modificarCategoria($datos['idCategoria'],$datos['codigo'],$datos['descripcion']))
                    $returnCrud=$this->consultarMessage("okUpdate");
            }
            else
            {
                $returnCrud=$this->consultarMessage("errorSave");
                // se guarda la nueva categoria
                if($this->Categoria->guardarCategoria($datos['codigo'],$datos['descripcion']))
                    $returnCrud=$this->consultarMessage("okSave");
            }
            return new ViewModel(array('form'=>$this->form,'msg'=>$returnCrud,'categorias'=>$this->Categoria->consultarTodoCategoria()));
        }
        // si existe el parametro $id  se consulta la categoria y se carga el formulario.
        else if(isset($id))
        {
            $this->Categoria->consultarCategoriaPorIdCategoria($this->params()->fromQuery('id'));
            $this->form->get("idCategoria")->setValue($this->Categoria->getIdCategoria());
            $this->form->get("codigo")->setValue($this->Categoria->getCodigo());
            $this->form->get("descripcion")->setValue($this->Categoria->getDescripcion());
        }
        return new ViewModel(array('form'=>$this->form,'categorias'=>$this->Categoria->consultarTodoCategoria()));
    }
    
    private function consultarMessage($nameMensaje)
    {
        $serviceLocator=$this->getServiceLocator()->get('Config');
        $mensaje=$serviceLocator['MsgCrud'];
        $mensaje= $mensaje[$nameMensaje];
        return $mensaje['function']."('".$mensaje['title']."','".$mensaje['message']."');";
    }
}
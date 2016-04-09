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
use Application\Form\FormRegistro;

class IndexController extends AbstractActionController
{
    private $Categoria;
    private $Marca;
    private $Usuario;
    private $Cliente;
    private $form;
    
    public function indexAction()
    {
        $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
        $this->Categoria = new Entity\Categoria($this->dbAdapter);
        $this->Marca = new Entity\Marca($this->dbAdapter);
        
        return new ViewModel(array('categorias'=>$this->Categoria->consultarTodoCategoriaCountNumeroProductos(),
                                   'marcas'=>$this->Marca->consultarTodoMarcaCountNumeroProductos()));
    }
    
    public function registerAction()
    {
        $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
        $this->form = new FormRegistro($this->getServiceLocator(),$this->getRequest()->getBaseUrl());
        if(count($this->request->getPost())>0)
        {
            $datos=$this->request->getPost();
            $this->Usuario = new Entity\Usuario($this->dbAdapter);
            // Si no hay un tercero con ese nit, Se debe crear el tercero
            $resultado = $this->Usuario->guardarUsuarioCliente($datos['idTipoDocumento'], $datos['nit'], $datos['nombre'], $datos['apellido'], 
                                                               $datos['direccion'], $datos['telefono'],$datos['idMunicipio'],$datos['email'],  md5($datos['clave']));
           
            if ($resultado == "true")
            {
                return new ViewModel(array('form'=>$this->form,"msg"=>$this->consultarMessage("okRegistroUsuario"))); 
            }
            else 
            {
                // Se debe devolver un mensaje de advertencia pues el email utilizado ya esta en uso.
                $this->form->get("idTipoDocumento")->setValue($datos['idTipoDocumento']);
                $this->form->get("nit")->setValue($datos['nit']);
                $this->form->get("nombre")->setValue($datos['nombre']);
                $this->form->get("apellido")->setValue($datos['apellido']);
                $this->form->get("direccion")->setValue($datos['direccion']);
                $this->form->get("telefono")->setValue($datos['telefono']);
                $this->form->get("email")->setValue($datos['email']);
             
            }
            return new ViewModel(array('form'=>$this->form,"msgError"=>$resultado)); 
        }
        return new ViewModel(array('form'=>$this->form));
    }
    
    public function generarSelectDepartamentoAction()
    {
        $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
        $Departamento = new Entity\Departamento($this->dbAdapter);
        $idPais=$this->params()->fromQuery('idPais',null);
        $options = $Departamento->generarOptionsSelect(array('idPais'=>$idPais));
        $select = "<select id='idDepartamento' name='idDepartamento'>";
        foreach ($options as $value => $key) {
            $select .="<option value='".$value."'>".$key."</option>";
        }
        echo $select.'</select>';
        return $this->response;
    }
    public function generarSelectMunicipioAction()
    {
        $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
        $Municipio = new Entity\Municipio($this->dbAdapter);
        $idDepartamento=$this->params()->fromQuery('idDepartamento',null);
        $options = $Municipio->generarOptionsSelect(array('idDepartamento'=>$idDepartamento));
        $select = "<select id='idMunicipio' name='idMunicipio'>";
        foreach ($options as $value => $key) {
            $select .="<option value='".$value."'>".$key."</option>";
        }
        echo $select.'</select>';
        return $this->response;
    }
    
    private function consultarMessage($nameMensaje)
    {
        $serviceLocator=$this->getServiceLocator()->get('Config');
        $mensaje=$serviceLocator['MsgCliente'][$nameMensaje];
        return $mensaje['function']."('".$mensaje['title']."','".$mensaje['message']."');";
    }
}

<?php
/*
* @autor Luis Fernando Henriquez
* @copyrigth 2016
* @nota cuando se necesite alguno de estos botones, este debe agregarse al formulario  que lo necesite desde el controlador. 
*/

namespace Admin\Form;
use Zend\Form\Form;

class FormBase extends Form
{
    
    private $cssClass;
    private $basePath;
    public function __construct($serviceLocator,$basePath = null)
    {
        parent::__construct("frmbase");
        $this->basePath = $basePath;
        $this->cssClass = $serviceLocator->get('Config');
        $this->cssClass = $this->cssClass['cssClass'];
        $this->generarCampos();    
    }
    private function generarCampos()
    {
        /*************** btn Buscar Producto********************************/
        $this->add(array(
                'name'=>'btnBuscarProducto',			
                'attributes'=>array(
                        'id'=>'btnBuscarProducto',
                        'type'=>'button',
                        'value'=>'Buscar',
                        'title'=>'Buscar',
                        'data-target'=>"#textModal",
                        'data-toggle'=>"modal",
                        'onClick'=>"usar_ajax('".$this->basePath."/admin/producto/buscar','#modal-dialog-display','')",
                        'style'=>'margin:2px',
                        'class'=>$this->cssClass['btnBuscar']
                )
        ));
        /*************** btn Buscar Producto********************************/
        
        /*************** btn Buscar Proveedor********************************/
        $this->add(array(
                'name'=>'btnBuscarProveedor',			
                'attributes'=>array(
                        'id'=>'btnBuscarProveedor',
                        'type'=>'button',
                        'value'=>'Buscar',
                        'title'=>'Buscar',
                        'data-target'=>"#textModal",
                        'data-toggle'=>"modal",
                        'onClick'=>"usar_ajax('".$this->basePath."/admin/proveedor/buscar','#modal-dialog-display','')",
                        'style'=>'margin:2px',
                        'class'=>$this->cssClass['btnBuscar']
                )
        ));
        /*************** btn Buscar Producto ********************************/
        /*************** btn Buscar Tercero ********************************/
        $this->add(array(
                'name'=>'btnBuscarTercero',			
                'attributes'=>array(
                        'id'=>'btnBuscarTercero',
                        'type'=>'button',
                        'value'=>'Buscar',
                        'title'=>'Buscar',
                        'data-target'=>"#textModal",
                        'data-toggle'=>"modal",
                        'onClick'=>"usar_ajax('".$this->basePath."/admin/datobasicotercero/buscar','#modal-dialog-display','')",
                        'style'=>'margin:2px',
                        'class'=>$this->cssClass['btnBuscar']
                )
        ));
        /*************** btn Buscar Tercero********************************/
    }
}
?>

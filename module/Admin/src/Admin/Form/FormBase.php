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
        //Variable que se usa para asignar campos para modal 2
        $parametrosGet = "?botonClose=btnClosePop2&contenedorDialog=modal-dialog-display2&modal=textModal2";
        
        /*************** btn Buscar Producto********************************/
        $this->add(array(
                'name'=>'btnBuscarProducto',			
                'attributes'=>array(
                        'id'=>'btnBuscarProducto',
                        'type'=>'button',
                        'value'=>'Buscar',
                        'title'=>'Buscar',
                        //'data-target'=>"#textModal",
                        'data-toggle'=>"modal",
                        'onClick'=>"showBusquedaOnModal(this,'".$this->basePath."/admin/producto/buscar','')",
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
                        'onClick'=>"showBusquedaOnModal(this,'".$this->basePath."/admin/proveedor/buscar','')",
                        'style'=>'margin:2px',
                        'class'=>$this->cssClass['btnBuscar']
                )
        ));
         /*************** btn Buscar Cliente********************************/
        $this->add(array(
                'name'=>'btnBuscarCliente',			
                'attributes'=>array(
                        'id'=>'btnBuscarCliente',
                        'type'=>'button',
                        'value'=>'Buscar',
                        'title'=>'Buscar',
                        'data-target'=>"#textModal",
                        'data-toggle'=>"modal",
                        'onClick'=>"showBusquedaOnModal(this,'".$this->basePath."/admin/cliente/buscar','')",
                        'style'=>'margin:2px',
                        'class'=>$this->cssClass['btnBuscar']
                )
        ));
        /*************** btn Buscar Cliente ********************************/
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
                        'onClick'=>"showBusquedaOnModal(this,'".$this->basePath."/admin/datobasicotercero/buscar','')",
                        'style'=>'margin:2px',
                        'class'=>$this->cssClass['btnBuscar']
                )
        ));
        /*************** btn Buscar Tercero********************************/
        /*************** btn Buscar Categoria ********************************/
        $this->add(array(
                'name'=>'btnBuscarCategoria',			
                'attributes'=>array(
                        'id'=>'btnBuscarCategoria',
                        'type'=>'button',
                        'value'=>'Buscar',
                        'title'=>'Buscar',
                        'data-target'=>"#textModal",
                        'data-toggle'=>"modal",
                        'onClick'=>"showBusquedaOnModal(this,'".$this->basePath."/admin/categoria/buscar','')",
                        'style'=>'margin:2px',
                        'class'=>$this->cssClass['btnBuscar']
                )
        ));
        /*************** btn Buscar Categoria********************************/
        /*************** btn Buscar Marca ********************************/
        $this->add(array(
                'name'=>'btnBuscarMarca',			
                'attributes'=>array(
                        'id'=>'btnBuscarMarca',
                        'type'=>'button',
                        'value'=>'Buscar',
                        'title'=>'Buscar',
                        'data-target'=>"#textModal",
                        'data-toggle'=>"modal",
                        'onClick'=>"showBusquedaOnModal(this,'".$this->basePath."/admin/marca/buscar','')",
                        'style'=>'margin:2px',
                        'class'=>$this->cssClass['btnBuscar']
                )
        ));
        /*************** btn Buscar Marca********************************/
         /*************** btn Buscar Municipio ********************************/
        $this->add(array(
                'name'=>'btnBuscarMunicipio',			
                'attributes'=>array(
                        'id'=>'btnBuscarMunicipio',
                        'type'=>'button',
                        'value'=>'Buscar',
                        'title'=>'Buscar',
                        'data-target'=>"#textModal",
                        'data-toggle'=>"modal",
                        'onClick'=>"showBusquedaOnModal(this,'".$this->basePath."/admin/municipio/buscar','')",
                        'style'=>'margin:2px',
                        'class'=>$this->cssClass['btnBuscar']
                )
        ));
        /*************** btn Buscar Municipio********************************/
        /*************** btn Buscar Proveedor Oficina ********************************/
        $this->add(array(
                'name'=>'btnBuscarProveedorOficina',			
                'attributes'=>array(
                        'id'=>'btnBuscarProveedorOficina',
                        'type'=>'button',
                        'value'=>'Buscar',
                        'title'=>'Buscar',
                        'data-target'=>"#textModal",
                        'data-toggle'=>"modal",
                        'onClick'=>"showBusquedaOnModal(this,'".$this->basePath."/admin/proveedoroficina/buscar','')",
                        'style'=>'margin:2px',
                        'class'=>$this->cssClass['btnBuscar']
                )
        ));
         /*************** btn Buscar Saldo Inventario ********************************/
        $this->add(array(
                'name'=>'btnBuscarSaldoInventario',			
                'attributes'=>array(
                        'id'=>'btnBuscarSaldoInventario',
                        'type'=>'button',
                        'value'=>'Buscar',
                        'title'=>'Buscar',
                        'data-target'=>"#textModal",
                        'data-toggle'=>"modal",
                        'onClick'=>"showBusquedaOnModal(this,'".$this->basePath."/admin/saldoinventario/buscar','')",
                        'style'=>'margin:2px',
                        'class'=>$this->cssClass['btnBuscar']
                )
        ));
        
        /*************** btn Buscar Municipio********************************/
        $this->add(array(
                'name'=>'btnGuardar',			
                'attributes'=>array(
                        'id'=>'btnGuardar',
                        'type'=>'submit',
                        'value'=>'Guardar',
                        'title'=>'Guardar',
                        'class'=>$this->cssClass['btnGuardar']
                )
        ));
        $this->add(array(
                'name'=>'btnModificar',			
                'attributes'=>array(
                        'id'=>'btnModificar',
                        'type'=>'submit',
                        'value'=>'Modificar',
                        'title'=>'Modificar',
                        'style'=>'margin:2px',
                        'class'=>$this->cssClass['btnModificar']
                )
        ));
    }
}
?>

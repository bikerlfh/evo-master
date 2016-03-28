<?php
return array(
    'controllers' => array(
        'invokables' => array(
            'Admin\Controller\Index' => 'Admin\Controller\IndexController',
            'Admin\Controller\Login' => 'Admin\Controller\LoginController',
            'Admin\Controller\Categoria' => 'Admin\Controller\CategoriaController',
            'Admin\Controller\Cliente' => 'Admin\Controller\ClienteController',
            'Admin\Controller\DatoBasicoTercero' => 'Admin\Controller\DatoBasicoTerceroController',
            'Admin\Controller\Departamento' => 'Admin\Controller\DepartamentoController',
            'Admin\Controller\EstadoPedido' => 'Admin\Controller\EstadoPedidoController',
            'Admin\Controller\ImagenProducto' => 'Admin\Controller\ImagenProductoController',
            'Admin\Controller\Marca' => 'Admin\Controller\MarcaController',
            'Admin\Controller\MovimientoInventario' => 'Admin\Controller\MovimientoInventarioController',
            'Admin\Controller\Municipio' => 'Admin\Controller\MunicipioController',
            'Admin\Controller\Pais' => 'Admin\Controller\PaisController',
            'Admin\Controller\PedidoCompra' => 'Admin\Controller\PedidoCompraController',
            'Admin\Controller\Producto' => 'Admin\Controller\ProductoController',
            'Admin\Controller\Proveedor' => 'Admin\Controller\ProveedorController',
            'Admin\Controller\ProveedorOficina' => 'Admin\Controller\ProveedorOficinaController',
            'Admin\Controller\ProveedorCuenta' => 'Admin\Controller\ProveedorCuentaController',
            'Admin\Controller\SaldoInventario' => 'Admin\Controller\SaldoInventarioController',
            'Admin\Controller\TipoCuenta' => 'Admin\Controller\TipoCuentaController',
            'Admin\Controller\TipoDocumento' => 'Admin\Controller\TipoDocumentoController',
            'Admin\Controller\TipoUsuario' => 'Admin\Controller\TipoUsuarioController',
            'Admin\Controller\Usuario' => 'Admin\Controller\UsuarioController',
            'Admin\Controller\ViaPago' => 'Admin\Controller\ViaPagoController',
            'Admin\Controller\EstadoPedidoVenta' => 'Admin\Controller\EstadoPedidoVentaController',
            'Admin\Controller\PedidoVenta' => 'Admin\Controller\PedidoVentaController',
            'Admin\Controller\Promocion' => 'Admin\Controller\PromocionController',
        ),
    ),
    'router' => array(
        'routes' => array(
            'admin' => array(
                'type'    => 'Literal',
                'options' => array(
                    // Change this to something specific to your module
                    'route'    => '/admin',
                    'defaults' => array(
                        // Change this value to reflect the namespace in which
                        // the controllers for your module are found
                        '__NAMESPACE__' => 'Admin\Controller',
                        'controller'    => 'Index',
                        'action'        => 'index',
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    // This route is a sane default when developing a module;
                    // as you solidify the routes for your module, however,
                    // you may want to remove it and replace it with more
                    // specific routes.
                    'default' => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route'    => '/[:controller[/:action]]',
                            'constraints' => array(
                                'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'action'     => '[a-zA-Z][a-zA-Z0-9_-]*',
                            ),
                            'defaults' => array(
                            ),
                        ),
                    ),
                ),
            ),
            /*
            'buscarPedidoCompra' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/admin/pedidocompra/index/:idPedidoCompra[/]',
                    'constraints' => array(
                        'idPedidoCompra' => '[0-9]*'
                        ),
                    'defaults' => array(
                        'controller' => 'Admin\Controller\PedidoCompra',
                        'action' => 'index',
                    ),
                ),
           ),*/
        ),
    ),
    'view_manager' => array(
        'template_path_stack' => array(
             __DIR__ . '/../view',
        ),
    ),
);

USE [evo]
GO
/****** Object:  Schema [Compra]    Script Date: 27/03/2016 14:53:59 ******/
CREATE SCHEMA [Compra]
GO
/****** Object:  Schema [Inventario]    Script Date: 27/03/2016 14:53:59 ******/
CREATE SCHEMA [Inventario]
GO
/****** Object:  Schema [Producto]    Script Date: 27/03/2016 14:53:59 ******/
CREATE SCHEMA [Producto]
GO
/****** Object:  Schema [Seguridad]    Script Date: 27/03/2016 14:53:59 ******/
CREATE SCHEMA [Seguridad]
GO
/****** Object:  Schema [Tercero]    Script Date: 27/03/2016 14:53:59 ******/
CREATE SCHEMA [Tercero]
GO
/****** Object:  Schema [Venta]    Script Date: 27/03/2016 14:53:59 ******/
CREATE SCHEMA [Venta]
GO
/****** Object:  Table [Compra].[EstadoPedido]    Script Date: 27/03/2016 14:53:59 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [Compra].[EstadoPedido](
	[idEstadoPedido] [smallint] IDENTITY(1,1) NOT NULL,
	[codigo] [varchar](5) NOT NULL,
	[descripcion] [varchar](10) NOT NULL,
 CONSTRAINT [PK_EstadoPedido] PRIMARY KEY CLUSTERED 
(
	[idEstadoPedido] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [Compra].[PedidoCompra]    Script Date: 27/03/2016 14:53:59 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [Compra].[PedidoCompra](
	[idPedidoCompra] [bigint] IDENTITY(1,1) NOT NULL,
	[numeroPedido] [bigint] NOT NULL,
	[idEstadoPedido] [smallint] NOT NULL,
	[idProveedor] [bigint] NOT NULL,
	[fechaPedido] [datetime] NOT NULL,
	[urlDocumentoPago] [varchar](150) NULL,
	[idUsuarioCreacion] [bigint] NOT NULL,
 CONSTRAINT [PK_PedidoCompra] PRIMARY KEY CLUSTERED 
(
	[idPedidoCompra] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [Compra].[PedidoCompraPosicion]    Script Date: 27/03/2016 14:53:59 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [Compra].[PedidoCompraPosicion](
	[idPedidoCompraPosicion] [bigint] IDENTITY(1,1) NOT NULL,
	[idPedidoCompra] [bigint] NOT NULL,
	[idProducto] [bigint] NOT NULL,
	[cantidad] [bigint] NOT NULL,
	[valorCompra] [decimal](10, 2) NOT NULL,
	[idUsuarioCreacion] [bigint] NOT NULL,
 CONSTRAINT [PK_PedidoCompraPosicion] PRIMARY KEY CLUSTERED 
(
	[idPedidoCompraPosicion] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]

GO
/****** Object:  Table [Compra].[TipoCuenta]    Script Date: 27/03/2016 14:53:59 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [Compra].[TipoCuenta](
	[idTipoCuenta] [smallint] IDENTITY(1,1) NOT NULL,
	[codigo] [varchar](5) NOT NULL,
	[descripcion] [varchar](15) NOT NULL,
 CONSTRAINT [PK_TipoCuenta] PRIMARY KEY CLUSTERED 
(
	[idTipoCuenta] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [Compra].[ViaPago]    Script Date: 27/03/2016 14:53:59 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [Compra].[ViaPago](
	[idViaPago] [smallint] IDENTITY(1,1) NOT NULL,
	[codigo] [varchar](5) NOT NULL,
	[descripcion] [varchar](15) NOT NULL,
 CONSTRAINT [PK_ViaPago] PRIMARY KEY CLUSTERED 
(
	[idViaPago] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [Inventario].[MovimientoInventario]    Script Date: 27/03/2016 14:53:59 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [Inventario].[MovimientoInventario](
	[idMovimientoInventario] [bigint] IDENTITY(1,1) NOT NULL,
	[idProducto] [bigint] NOT NULL,
	[idProveedor] [bigint] NOT NULL,
	[entradaSalida] [bit] NOT NULL,
	[cantidad] [bigint] NOT NULL,
	[valorMovimiento] [decimal](10, 2) NOT NULL,
	[fecha] [datetime] NOT NULL,
	[idUsuarioCreacion] [bigint] NOT NULL,
 CONSTRAINT [PK_MovimientoInventario] PRIMARY KEY CLUSTERED 
(
	[idMovimientoInventario] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]

GO
/****** Object:  Table [Inventario].[SaldoInventario]    Script Date: 27/03/2016 14:53:59 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [Inventario].[SaldoInventario](
	[idSaldoInventario] [bigint] IDENTITY(1,1) NOT NULL,
	[idProducto] [bigint] NOT NULL,
	[idProveedor] [bigint] NOT NULL,
	[cantidad] [int] NOT NULL,
	[costoTotal] [decimal](10, 2) NOT NULL,
	[valorVenta] [decimal](10, 2) NOT NULL,
	[fechaCreacion] [datetime] NOT NULL,
	[fechaModificacion] [datetime] NOT NULL,
	[idUsuarioCreacion] [bigint] NOT NULL,
	[idUsuarioModificacion] [bigint] NOT NULL,
 CONSTRAINT [PK_SaldoInventario] PRIMARY KEY CLUSTERED 
(
	[idSaldoInventario] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]

GO
/****** Object:  Table [Producto].[Categoria]    Script Date: 27/03/2016 14:53:59 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [Producto].[Categoria](
	[idCategoria] [int] IDENTITY(1,1) NOT NULL,
	[idCategoriaCentral] [int] NULL,
	[codigo] [varchar](5) NOT NULL,
	[descripcion] [varchar](20) NOT NULL,
 CONSTRAINT [PK_Categoria] PRIMARY KEY CLUSTERED 
(
	[idCategoria] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [Producto].[ImagenProducto]    Script Date: 27/03/2016 14:53:59 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [Producto].[ImagenProducto](
	[idImagenProducto] [bigint] IDENTITY(1,1) NOT NULL,
	[idProducto] [bigint] NOT NULL,
	[url] [varchar](200) NOT NULL,
	[idUsuarioCreacion] [bigint] NOT NULL,
 CONSTRAINT [PK_ImagenProducto] PRIMARY KEY CLUSTERED 
(
	[idImagenProducto] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [Producto].[Marca]    Script Date: 27/03/2016 14:53:59 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [Producto].[Marca](
	[idMarca] [smallint] IDENTITY(1,1) NOT NULL,
	[codigo] [varchar](5) NOT NULL,
	[descripcion] [varchar](20) NOT NULL,
 CONSTRAINT [PK_Marca] PRIMARY KEY CLUSTERED 
(
	[idMarca] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [Producto].[Producto]    Script Date: 27/03/2016 14:53:59 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [Producto].[Producto](
	[idProducto] [bigint] IDENTITY(1,1) NOT NULL,
	[idMarca] [smallint] NOT NULL,
	[idCategoria] [int] NOT NULL,
	[codigo] [varchar](10) NOT NULL,
	[nombre] [varchar](50) NOT NULL,
	[referencia] [varchar](50) NOT NULL,
	[descripcion] [varchar](max) NOT NULL,
	[especificacion] [varchar](max) NULL,
	[idUsuarioCreacion] [bigint] NOT NULL,
	[fechaCreacion] [datetime] NOT NULL,
 CONSTRAINT [PK_Producto] PRIMARY KEY CLUSTERED 
(
	[idProducto] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [Producto].[Promocion]    Script Date: 27/03/2016 14:53:59 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [Producto].[Promocion](
	[idPromocion] [bigint] IDENTITY(1,1) NOT NULL,
	[idSaldoInventario] [bigint] NOT NULL,
	[valorAnterior] [decimal](10, 2) NOT NULL,
	[valorPromocion] [decimal](10, 2) NOT NULL,
	[fechaDesde] [datetime] NOT NULL,
	[fechaHasta] [datetime] NOT NULL,
	[estado] [bit] NOT NULL,
	[idUsuarioCreacion] [bigint] NOT NULL,
 CONSTRAINT [PK_Promocion] PRIMARY KEY CLUSTERED 
(
	[idPromocion] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]

GO
/****** Object:  Table [Seguridad].[TipoUsuario]    Script Date: 27/03/2016 14:53:59 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [Seguridad].[TipoUsuario](
	[idTipoUsuario] [smallint] IDENTITY(1,1) NOT NULL,
	[codigo] [varchar](5) NOT NULL,
	[descripcion] [varchar](15) NOT NULL,
 CONSTRAINT [PK_TipoUsuario] PRIMARY KEY CLUSTERED 
(
	[idTipoUsuario] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [Seguridad].[Usuario]    Script Date: 27/03/2016 14:53:59 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [Seguridad].[Usuario](
	[idUsuario] [bigint] IDENTITY(1,1) NOT NULL,
	[idTipoUsuario] [smallint] NOT NULL,
	[idDatoBasicoTercero] [bigint] NOT NULL,
	[email] [varchar](150) NOT NULL,
	[clave] [varchar](32) NOT NULL,
 CONSTRAINT [PK_Usuario] PRIMARY KEY CLUSTERED 
(
	[idUsuario] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [Tercero].[Cliente]    Script Date: 27/03/2016 14:53:59 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [Tercero].[Cliente](
	[idCliente] [bigint] IDENTITY(1,1) NOT NULL,
	[idDatoBasicoTercero] [bigint] NOT NULL,
	[idMunicipio] [int] NOT NULL,
	[email] [varchar](150) NOT NULL,
	[direccion] [varchar](150) NULL,
	[telefono] [varchar](50) NULL,
 CONSTRAINT [PK_Cliente] PRIMARY KEY CLUSTERED 
(
	[idCliente] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [Tercero].[DatoBasicoTercero]    Script Date: 27/03/2016 14:53:59 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [Tercero].[DatoBasicoTercero](
	[idDatoBasicoTercero] [bigint] IDENTITY(1,1) NOT NULL,
	[idTipoDocumento] [smallint] NOT NULL,
	[nit] [bigint] NOT NULL,
	[descripcion] [varchar](150) NOT NULL,
	[primerNombre] [varchar](30) NULL,
	[segundoNombre] [varchar](30) NULL,
	[primerApellido] [varchar](30) NULL,
	[segundoApellido] [varchar](30) NULL,
	[direccion] [varchar](50) NULL,
	[telefono] [varchar](50) NULL,
 CONSTRAINT [PK_DatoBasicoTercero] PRIMARY KEY CLUSTERED 
(
	[idDatoBasicoTercero] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [Tercero].[Departamento]    Script Date: 27/03/2016 14:53:59 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [Tercero].[Departamento](
	[idDepartamento] [int] IDENTITY(1,1) NOT NULL,
	[idPais] [int] NOT NULL,
	[codigo] [varchar](5) NOT NULL,
	[descripcion] [varchar](15) NOT NULL,
 CONSTRAINT [PK_Departamento] PRIMARY KEY CLUSTERED 
(
	[idDepartamento] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [Tercero].[Municipio]    Script Date: 27/03/2016 14:53:59 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [Tercero].[Municipio](
	[idMunicipio] [int] IDENTITY(1,1) NOT NULL,
	[idDepartamento] [int] NOT NULL,
	[codigo] [varchar](5) NOT NULL,
	[descripcion] [varchar](15) NOT NULL,
 CONSTRAINT [PK_Municipio] PRIMARY KEY CLUSTERED 
(
	[idMunicipio] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [Tercero].[Pais]    Script Date: 27/03/2016 14:53:59 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [Tercero].[Pais](
	[idPais] [int] IDENTITY(1,1) NOT NULL,
	[codigo] [varchar](5) NOT NULL,
	[descripcion] [varchar](15) NOT NULL,
 CONSTRAINT [PK_Pais] PRIMARY KEY CLUSTERED 
(
	[idPais] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [Tercero].[Proveedor]    Script Date: 27/03/2016 14:53:59 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [Tercero].[Proveedor](
	[idProveedor] [bigint] IDENTITY(1,1) NOT NULL,
	[idDatoBasicoTercero] [bigint] NOT NULL,
	[email] [varchar](150) NOT NULL,
	[webSite] [varchar](150) NOT NULL,
	[idUsuarioCreacion] [bigint] NOT NULL,
 CONSTRAINT [PK_Proveedor] PRIMARY KEY CLUSTERED 
(
	[idProveedor] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [Tercero].[ProveedorCuenta]    Script Date: 27/03/2016 14:53:59 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [Tercero].[ProveedorCuenta](
	[idProveedorCuenta] [bigint] IDENTITY(1,1) NOT NULL,
	[idProveedorOficina] [bigint] NOT NULL,
	[numeroCuentaBancaria] [varchar](50) NULL,
	[idTipoCuenta] [smallint] NULL,
	[idViaPago] [smallint] NOT NULL,
 CONSTRAINT [PK_ProveedorCuenta] PRIMARY KEY CLUSTERED 
(
	[idProveedorCuenta] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [Tercero].[ProveedorOficina]    Script Date: 27/03/2016 14:53:59 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [Tercero].[ProveedorOficina](
	[idProveedorOficina] [bigint] IDENTITY(1,1) NOT NULL,
	[idProveedor] [bigint] NOT NULL,
	[idMunicipio] [int] NOT NULL,
	[email] [varchar](150) NULL,
	[webSite] [varchar](150) NULL,
	[direccion] [varchar](150) NULL,
	[telefono] [varchar](50) NULL,
 CONSTRAINT [PK_ProveedorOficina] PRIMARY KEY CLUSTERED 
(
	[idProveedorOficina] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [Tercero].[TipoDocumento]    Script Date: 27/03/2016 14:53:59 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [Tercero].[TipoDocumento](
	[idTipoDocumento] [smallint] IDENTITY(1,1) NOT NULL,
	[codigo] [varchar](2) NOT NULL,
	[descripcion] [varchar](20) NOT NULL,
 CONSTRAINT [PK_TipoDocumento] PRIMARY KEY CLUSTERED 
(
	[idTipoDocumento] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [Venta].[EstadoPedidoVenta]    Script Date: 27/03/2016 14:53:59 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [Venta].[EstadoPedidoVenta](
	[idEstadoPedidoVenta] [smallint] IDENTITY(1,1) NOT NULL,
	[codigo] [varchar](5) NOT NULL,
	[descripcion] [varchar](10) NOT NULL,
 CONSTRAINT [PK_EstadoVenta] PRIMARY KEY CLUSTERED 
(
	[idEstadoPedidoVenta] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [Venta].[PedidoVenta]    Script Date: 27/03/2016 14:53:59 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [Venta].[PedidoVenta](
	[idPedidoVenta] [bigint] IDENTITY(1,1) NOT NULL,
	[numeroPedido] [bigint] NOT NULL,
	[idCliente] [bigint] NOT NULL,
	[idEstadoPedidoVenta] [smallint] NOT NULL,
	[idViaPago] [smallint] NULL,
	[fechaPedido] [datetime] NOT NULL,
	[urlDocumentoPago] [varchar](150) NULL,
	[idUsuarioCreacion] [bigint] NOT NULL,
 CONSTRAINT [PK_PedidoVenta] PRIMARY KEY CLUSTERED 
(
	[idPedidoVenta] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [Venta].[PedidoVentaPosicion]    Script Date: 27/03/2016 14:53:59 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [Venta].[PedidoVentaPosicion](
	[idPedidoVentaPosicion] [bigint] IDENTITY(1,1) NOT NULL,
	[idPedidoVenta] [bigint] NOT NULL,
	[idSaldoInventario] [bigint] NOT NULL,
	[idProducto] [bigint] NOT NULL,
	[cantidad] [smallint] NOT NULL,
	[valorVenta] [decimal](10, 2) NOT NULL,
	[idUsuarioCreacion] [bigint] NOT NULL,
 CONSTRAINT [PK_PedidoVentaPosicion] PRIMARY KEY CLUSTERED 
(
	[idPedidoVentaPosicion] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]
GO

ALTER TABLE [Venta].[PedidoVentaPosicion] ADD  CONSTRAINT [DF_PedidoVentaPosicion_idSaldoInventario]  DEFAULT ((0)) FOR [idSaldoInventario]
GO

ALTER TABLE [Venta].[PedidoVentaPosicion]  WITH CHECK ADD  CONSTRAINT [FK_PedidoVentaPosicion_PedidoVenta] FOREIGN KEY([idPedidoVenta])
REFERENCES [Venta].[PedidoVenta] ([idPedidoVenta])
GO

ALTER TABLE [Venta].[PedidoVentaPosicion] CHECK CONSTRAINT [FK_PedidoVentaPosicion_PedidoVenta]
GO

ALTER TABLE [Venta].[PedidoVentaPosicion]  WITH CHECK ADD  CONSTRAINT [FK_PedidoVentaPosicion_SaldoInventario] FOREIGN KEY([idSaldoInventario])
REFERENCES [Inventario].[SaldoInventario] ([idSaldoInventario])
GO

ALTER TABLE [Venta].[PedidoVentaPosicion] CHECK CONSTRAINT [FK_PedidoVentaPosicion_SaldoInventario]
GO

SET IDENTITY_INSERT [Compra].[EstadoPedido] ON 

INSERT [Compra].[EstadoPedido] ([idEstadoPedido], [codigo], [descripcion]) VALUES (1, N'01', N'SOLICITADO')
INSERT [Compra].[EstadoPedido] ([idEstadoPedido], [codigo], [descripcion]) VALUES (2, N'02', N'AUTORIZADO')
INSERT [Compra].[EstadoPedido] ([idEstadoPedido], [codigo], [descripcion]) VALUES (3, N'03', N'RECIBIDO')
INSERT [Compra].[EstadoPedido] ([idEstadoPedido], [codigo], [descripcion]) VALUES (4, N'04', N'ANULADO')
INSERT [Compra].[EstadoPedido] ([idEstadoPedido], [codigo], [descripcion]) VALUES (6, N'05', N'CANCELADO')
SET IDENTITY_INSERT [Compra].[EstadoPedido] OFF
SET IDENTITY_INSERT [Compra].[PedidoCompra] ON 

INSERT [Compra].[PedidoCompra] ([idPedidoCompra], [numeroPedido], [idEstadoPedido], [idProveedor], [fechaPedido], [urlDocumentoPago], [idUsuarioCreacion]) VALUES (29, 1, 2, 2, CAST(0x0000A5D00021AA63 AS DateTime), N'./public/imguploads/compra/pedido_compra_1.jpg', 1)
INSERT [Compra].[PedidoCompra] ([idPedidoCompra], [numeroPedido], [idEstadoPedido], [idProveedor], [fechaPedido], [urlDocumentoPago], [idUsuarioCreacion]) VALUES (36, 2, 2, 2, CAST(0x0000A5D500DD2A29 AS DateTime), N'./public/imguploads/compra/pedido_compra_2.jpg', 1)
SET IDENTITY_INSERT [Compra].[PedidoCompra] OFF
SET IDENTITY_INSERT [Compra].[PedidoCompraPosicion] ON 

INSERT [Compra].[PedidoCompraPosicion] ([idPedidoCompraPosicion], [idPedidoCompra], [idProducto], [cantidad], [valorCompra], [idUsuarioCreacion]) VALUES (43, 29, 1, 1, CAST(10000.00 AS Decimal(10, 2)), 1)
INSERT [Compra].[PedidoCompraPosicion] ([idPedidoCompraPosicion], [idPedidoCompra], [idProducto], [cantidad], [valorCompra], [idUsuarioCreacion]) VALUES (50, 36, 1, 20, CAST(25000.00 AS Decimal(10, 2)), 1)
SET IDENTITY_INSERT [Compra].[PedidoCompraPosicion] OFF
SET IDENTITY_INSERT [Compra].[TipoCuenta] ON 

INSERT [Compra].[TipoCuenta] ([idTipoCuenta], [codigo], [descripcion]) VALUES (2, N'01', N'AHORROS')
SET IDENTITY_INSERT [Compra].[TipoCuenta] OFF
SET IDENTITY_INSERT [Compra].[ViaPago] ON 

INSERT [Compra].[ViaPago] ([idViaPago], [codigo], [descripcion]) VALUES (2, N'01', N'CONSIGNACION')
SET IDENTITY_INSERT [Compra].[ViaPago] OFF
SET IDENTITY_INSERT [Inventario].[MovimientoInventario] ON 

INSERT [Inventario].[MovimientoInventario] ([idMovimientoInventario], [idProducto], [idProveedor], [entradaSalida], [cantidad], [valorMovimiento], [fecha], [idUsuarioCreacion]) VALUES (35, 1, 2, 1, 1, CAST(10000.00 AS Decimal(10, 2)), CAST(0x0000A5D00021C420 AS DateTime), 1)
INSERT [Inventario].[MovimientoInventario] ([idMovimientoInventario], [idProducto], [idProveedor], [entradaSalida], [cantidad], [valorMovimiento], [fecha], [idUsuarioCreacion]) VALUES (36, 1, 2, 1, 20, CAST(25000.00 AS Decimal(10, 2)), CAST(0x0000A5D500E90193 AS DateTime), 1)
SET IDENTITY_INSERT [Inventario].[MovimientoInventario] OFF
SET IDENTITY_INSERT [Inventario].[SaldoInventario] ON 

INSERT [Inventario].[SaldoInventario] ([idSaldoInventario], [idProducto], [idProveedor], [cantidad], [costoTotal], [valorVenta], [fechaCreacion], [fechaModificacion], [idUsuarioCreacion], [idUsuarioModificacion]) VALUES (38, 1, 2, 22, CAST(22000.00 AS Decimal(10, 2)), CAST(1000.00 AS Decimal(10, 2)), CAST(0x0000A5D00021C420 AS DateTime), CAST(0x0000A5D501672E00 AS DateTime), 1, 1)
SET IDENTITY_INSERT [Inventario].[SaldoInventario] OFF
SET IDENTITY_INSERT [Producto].[Categoria] ON 

INSERT [Producto].[Categoria] ([idCategoria], [idCategoriaCentral], [codigo], [descripcion]) VALUES (3, NULL, N'02', N'Cpu')
INSERT [Producto].[Categoria] ([idCategoria], [idCategoriaCentral], [codigo], [descripcion]) VALUES (7, NULL, N'01', N'Board')
SET IDENTITY_INSERT [Producto].[Categoria] OFF
SET IDENTITY_INSERT [Producto].[ImagenProducto] ON 

INSERT [Producto].[ImagenProducto] ([idImagenProducto], [idProducto], [url], [idUsuarioCreacion]) VALUES (110, 1, N'/evo-master/public/imguploads/01_Board_GIGABYTE_Z170M-D3H_DDR3_1.png', 1)
INSERT [Producto].[ImagenProducto] ([idImagenProducto], [idProducto], [url], [idUsuarioCreacion]) VALUES (111, 1, N'/evo-master/public/imguploads/01_Board_GIGABYTE_Z170M-D3H_DDR3_1.png', 1)
SET IDENTITY_INSERT [Producto].[ImagenProducto] OFF
SET IDENTITY_INSERT [Producto].[Marca] ON 

INSERT [Producto].[Marca] ([idMarca], [codigo], [descripcion]) VALUES (1, N'01', N'ASUS')
INSERT [Producto].[Marca] ([idMarca], [codigo], [descripcion]) VALUES (2, N'02', N'GigaByte')
SET IDENTITY_INSERT [Producto].[Marca] OFF
SET IDENTITY_INSERT [Producto].[Producto] ON 

INSERT [Producto].[Producto] ([idProducto], [idMarca], [idCategoria], [codigo], [nombre], [referencia], [descripcion], [especificacion], [idUsuarioCreacion], [fechaCreacion]) VALUES (1, 2, 7, N'01', N'Board GIGABYTE Z170M-D3H DDR3', N'GA-Z170M-D3H DDR3 rev1.0', N'Las placas GIGABYTE de la serie 100 compatibles con los últimos procesadores de 6 ª generación Intel ® Core ™, una CPU de escritorio de 14nm, que cuenta con un mejor rendimiento, eficiencia energética y soporte para memoria DDR3 / DDR3L, trayendo características de vanguardia y el máximo rendimiento a su próxima generación de PC. GIGABYTE ofrece un rendimiento de almacenamiento considerablemente más rápida con el soporte para PCIe y la interfaz SATA para dispositivos SSD M.2.

 

Ofrece una resolución y expansión de sonido de alta calidad para crear los efectos de sonido más realista para los jugadores profesionales. EasyTune de GIGABYTE ™ es una interfaz sencilla y fácil de usar que permite a los usuarios ajustar sus configuraciones del sistema o ajuste de sistema y memoria relojes y voltajes en un entorno Windows. Con Smart Quick Boost, un clic es todo lo que se necesita para overclockear automáticamente el sistema, dando un aumento de rendimiento adicional cuando más lo necesita.', N'Soporta Intel ® procesador Core ™ i7 / Intel ® Core i5 ™ / Intel ® Core i3 ™ / Intel ® Pentium ® procesadores / Intel ® Celeron ® procesadores en el paquete LGA1151 Intel ® Express Chipset Z170 4 slots x DDR3 DIMM que admiten hasta 32 GB de memoria del sistema Soporte para DDR3 3200 (OC) / 3000 (OC) / 2800 (OC) / 2666 (OC) / 2400 (OC) / 2133 MHz módulos de memoria Audio Realtek ® codec ALC892 Soporte para la tecnología 2-Way AMD CrossFire', 1, CAST(0x0000A5C10089FF58 AS DateTime))
SET IDENTITY_INSERT [Producto].[Producto] OFF
SET IDENTITY_INSERT [Seguridad].[TipoUsuario] ON 

INSERT [Seguridad].[TipoUsuario] ([idTipoUsuario], [codigo], [descripcion]) VALUES (1, N'01', N'Administrador')
SET IDENTITY_INSERT [Seguridad].[TipoUsuario] OFF
SET IDENTITY_INSERT [Seguridad].[Usuario] ON 

INSERT [Seguridad].[Usuario] ([idUsuario], [idTipoUsuario], [idDatoBasicoTercero], [email], [clave]) VALUES (1, 1, 1, N'bikerlfh@hotmail.com', N'202cb962ac59075b964b07152d234b70')
SET IDENTITY_INSERT [Seguridad].[Usuario] OFF
SET IDENTITY_INSERT [Tercero].[Cliente] ON 

INSERT [Tercero].[Cliente] ([idCliente], [idDatoBasicoTercero], [idMunicipio], [email], [direccion], [telefono]) VALUES (1, 2, 4, N'cliente.1@hotmail.com', N'13123', N'123')
SET IDENTITY_INSERT [Tercero].[Cliente] OFF
SET IDENTITY_INSERT [Tercero].[DatoBasicoTercero] ON 

INSERT [Tercero].[DatoBasicoTercero] ([idDatoBasicoTercero], [idTipoDocumento], [nit], [descripcion], [primerNombre], [segundoNombre], [primerApellido], [segundoApellido], [direccion], [telefono]) VALUES (1, 1, 1075239048, N'LUIS FERNANDO HENRIQUEZ ARCINIEGAS', N'LUIS', N'FERNANDO', N'HENRIQUEZ', N'ARCINIEGAS', N'cll 14a # ', N'123456789')
INSERT [Tercero].[DatoBasicoTercero] ([idDatoBasicoTercero], [idTipoDocumento], [nit], [descripcion], [primerNombre], [segundoNombre], [primerApellido], [segundoApellido], [direccion], [telefono]) VALUES (2, 1, 1, N'cliente 1', N'cliente 1 ', N'123', N'cliente 1', N'123', N'asd', N'123123')
SET IDENTITY_INSERT [Tercero].[DatoBasicoTercero] OFF
SET IDENTITY_INSERT [Tercero].[Departamento] ON 

INSERT [Tercero].[Departamento] ([idDepartamento], [idPais], [codigo], [descripcion]) VALUES (3, 1, N'01', N'HUILA')
INSERT [Tercero].[Departamento] ([idDepartamento], [idPais], [codigo], [descripcion]) VALUES (10, 1, N'02', N'CANADA')
SET IDENTITY_INSERT [Tercero].[Departamento] OFF
SET IDENTITY_INSERT [Tercero].[Municipio] ON 

INSERT [Tercero].[Municipio] ([idMunicipio], [idDepartamento], [codigo], [descripcion]) VALUES (2, 10, N'01', N'CALGARY')
INSERT [Tercero].[Municipio] ([idMunicipio], [idDepartamento], [codigo], [descripcion]) VALUES (4, 3, N'0101', N'NEIVA')
SET IDENTITY_INSERT [Tercero].[Municipio] OFF
SET IDENTITY_INSERT [Tercero].[Pais] ON 

INSERT [Tercero].[Pais] ([idPais], [codigo], [descripcion]) VALUES (1, N'1', N'COLOMBIA')
SET IDENTITY_INSERT [Tercero].[Pais] OFF
SET IDENTITY_INSERT [Tercero].[Proveedor] ON 

INSERT [Tercero].[Proveedor] ([idProveedor], [idDatoBasicoTercero], [email], [webSite], [idUsuarioCreacion]) VALUES (2, 1, N'bikerlfh@hotmail.com', N'www.bikerlfh.com1', 1)
SET IDENTITY_INSERT [Tercero].[Proveedor] OFF
SET IDENTITY_INSERT [Tercero].[ProveedorOficina] ON 

INSERT [Tercero].[ProveedorOficina] ([idProveedorOficina], [idProveedor], [idMunicipio], [email], [webSite], [direccion], [telefono]) VALUES (1, 2, 4, N'luisferhenriquez@gmail.com', N'www.bikerlfh.com', N'asdasd', N'12312123')
SET IDENTITY_INSERT [Tercero].[ProveedorOficina] OFF
SET IDENTITY_INSERT [Tercero].[TipoDocumento] ON 

INSERT [Tercero].[TipoDocumento] ([idTipoDocumento], [codigo], [descripcion]) VALUES (1, N'01', N'C.C')
SET IDENTITY_INSERT [Tercero].[TipoDocumento] OFF
SET IDENTITY_INSERT [Venta].[EstadoPedidoVenta] ON 

INSERT [Venta].[EstadoPedidoVenta] ([idEstadoPedidoVenta], [codigo], [descripcion]) VALUES (1, N'01', N'SOLICITADO')
INSERT [Venta].[EstadoPedidoVenta] ([idEstadoPedidoVenta], [codigo], [descripcion]) VALUES (2, N'02', N'AUTORIZADO')
INSERT [Venta].[EstadoPedidoVenta] ([idEstadoPedidoVenta], [codigo], [descripcion]) VALUES (3, N'03', N'DESPACHADO')
INSERT [Venta].[EstadoPedidoVenta] ([idEstadoPedidoVenta], [codigo], [descripcion]) VALUES (4, N'04', N'ANULADO')
INSERT [Venta].[EstadoPedidoVenta] ([idEstadoPedidoVenta], [codigo], [descripcion]) VALUES (5, N'05', N'CANCELADO')
SET IDENTITY_INSERT [Venta].[EstadoPedidoVenta] OFF
SET IDENTITY_INSERT [Venta].[PedidoVenta] ON 

INSERT [Venta].[PedidoVenta] ([idPedidoVenta], [numeroPedido], [idCliente], [idEstadoPedidoVenta], [idViaPago], [fechaPedido], [urlDocumentoPago], [idUsuarioCreacion]) VALUES (2, 1, 1, 1, NULL, CAST(0x0000A5D1016BFEEA AS DateTime), NULL, 1)
SET IDENTITY_INSERT [Venta].[PedidoVenta] OFF
SET IDENTITY_INSERT [Venta].[PedidoVentaPosicion] ON 

INSERT [Venta].[PedidoVentaPosicion] ([idPedidoVentaPosicion], [idPedidoVenta], [idProducto], [cantidad], [valorVenta], [idUsuarioCreacion]) VALUES (1, 2, 1, 1, CAST(13000.00 AS Decimal(10, 2)), 1)
SET IDENTITY_INSERT [Venta].[PedidoVentaPosicion] OFF
SET ANSI_PADDING ON

GO
/****** Object:  Index [IX_EstadoPedido]    Script Date: 27/03/2016 14:53:59 ******/
ALTER TABLE [Compra].[EstadoPedido] ADD  CONSTRAINT [IX_EstadoPedido] UNIQUE NONCLUSTERED 
(
	[codigo] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, IGNORE_DUP_KEY = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
GO
SET ANSI_PADDING ON

GO
/****** Object:  Index [IX_TipoCuenta]    Script Date: 27/03/2016 14:53:59 ******/
ALTER TABLE [Compra].[TipoCuenta] ADD  CONSTRAINT [IX_TipoCuenta] UNIQUE NONCLUSTERED 
(
	[codigo] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, IGNORE_DUP_KEY = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
GO
SET ANSI_PADDING ON

GO
/****** Object:  Index [IX_ViaPago]    Script Date: 27/03/2016 14:53:59 ******/
ALTER TABLE [Compra].[ViaPago] ADD  CONSTRAINT [IX_ViaPago] UNIQUE NONCLUSTERED 
(
	[codigo] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, IGNORE_DUP_KEY = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
GO
/****** Object:  Index [IX_SaldoInventario]    Script Date: 27/03/2016 14:53:59 ******/
ALTER TABLE [Inventario].[SaldoInventario] ADD  CONSTRAINT [IX_SaldoInventario] UNIQUE NONCLUSTERED 
(
	[idProducto] ASC,
	[idProveedor] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, IGNORE_DUP_KEY = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
GO
SET ANSI_PADDING ON

GO
/****** Object:  Index [IX_Categoria]    Script Date: 27/03/2016 14:53:59 ******/
ALTER TABLE [Producto].[Categoria] ADD  CONSTRAINT [IX_Categoria] UNIQUE NONCLUSTERED 
(
	[codigo] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, IGNORE_DUP_KEY = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
GO
SET ANSI_PADDING ON

GO
/****** Object:  Index [IX_Marca]    Script Date: 27/03/2016 14:53:59 ******/
ALTER TABLE [Producto].[Marca] ADD  CONSTRAINT [IX_Marca] UNIQUE NONCLUSTERED 
(
	[codigo] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, IGNORE_DUP_KEY = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
GO
SET ANSI_PADDING ON

GO
/****** Object:  Index [IX_TipoUsuario]    Script Date: 27/03/2016 14:53:59 ******/
ALTER TABLE [Seguridad].[TipoUsuario] ADD  CONSTRAINT [IX_TipoUsuario] UNIQUE NONCLUSTERED 
(
	[codigo] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, IGNORE_DUP_KEY = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
GO
SET ANSI_PADDING ON

GO
/****** Object:  Index [IX_Usuario]    Script Date: 27/03/2016 14:53:59 ******/
ALTER TABLE [Seguridad].[Usuario] ADD  CONSTRAINT [IX_Usuario] UNIQUE NONCLUSTERED 
(
	[email] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, IGNORE_DUP_KEY = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
GO
/****** Object:  Index [IX_DatoBasicoTercero]    Script Date: 27/03/2016 14:53:59 ******/
ALTER TABLE [Tercero].[DatoBasicoTercero] ADD  CONSTRAINT [IX_DatoBasicoTercero] UNIQUE NONCLUSTERED 
(
	[nit] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, IGNORE_DUP_KEY = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
GO
SET ANSI_PADDING ON

GO
/****** Object:  Index [IX_Departamento]    Script Date: 27/03/2016 14:53:59 ******/
ALTER TABLE [Tercero].[Departamento] ADD  CONSTRAINT [IX_Departamento] UNIQUE NONCLUSTERED 
(
	[codigo] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, IGNORE_DUP_KEY = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
GO
SET ANSI_PADDING ON

GO
/****** Object:  Index [IX_Municipio]    Script Date: 27/03/2016 14:53:59 ******/
ALTER TABLE [Tercero].[Municipio] ADD  CONSTRAINT [IX_Municipio] UNIQUE NONCLUSTERED 
(
	[codigo] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, IGNORE_DUP_KEY = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
GO
SET ANSI_PADDING ON

GO
/****** Object:  Index [IX_Pais]    Script Date: 27/03/2016 14:53:59 ******/
ALTER TABLE [Tercero].[Pais] ADD  CONSTRAINT [IX_Pais] UNIQUE NONCLUSTERED 
(
	[codigo] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, IGNORE_DUP_KEY = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
GO
SET ANSI_PADDING ON

GO
/****** Object:  Index [IX_TipoDocumento]    Script Date: 27/03/2016 14:53:59 ******/
ALTER TABLE [Tercero].[TipoDocumento] ADD  CONSTRAINT [IX_TipoDocumento] UNIQUE NONCLUSTERED 
(
	[codigo] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, IGNORE_DUP_KEY = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
GO
ALTER TABLE [Compra].[PedidoCompra]  WITH CHECK ADD  CONSTRAINT [FK_PedidoCompra_EstadoPedido] FOREIGN KEY([idEstadoPedido])
REFERENCES [Compra].[EstadoPedido] ([idEstadoPedido])
GO
ALTER TABLE [Compra].[PedidoCompra] CHECK CONSTRAINT [FK_PedidoCompra_EstadoPedido]
GO
ALTER TABLE [Compra].[PedidoCompra]  WITH CHECK ADD  CONSTRAINT [FK_PedidoCompra_Proveedor] FOREIGN KEY([idProveedor])
REFERENCES [Tercero].[Proveedor] ([idProveedor])
GO
ALTER TABLE [Compra].[PedidoCompra] CHECK CONSTRAINT [FK_PedidoCompra_Proveedor]
GO
ALTER TABLE [Compra].[PedidoCompraPosicion]  WITH CHECK ADD  CONSTRAINT [FK_PedidoCompraPosicion_PedidoCompra] FOREIGN KEY([idPedidoCompra])
REFERENCES [Compra].[PedidoCompra] ([idPedidoCompra])
GO
ALTER TABLE [Compra].[PedidoCompraPosicion] CHECK CONSTRAINT [FK_PedidoCompraPosicion_PedidoCompra]
GO
ALTER TABLE [Compra].[PedidoCompraPosicion]  WITH CHECK ADD  CONSTRAINT [FK_PedidoCompraPosicion_Producto] FOREIGN KEY([idProducto])
REFERENCES [Producto].[Producto] ([idProducto])
GO
ALTER TABLE [Compra].[PedidoCompraPosicion] CHECK CONSTRAINT [FK_PedidoCompraPosicion_Producto]
GO
ALTER TABLE [Inventario].[MovimientoInventario]  WITH CHECK ADD  CONSTRAINT [FK_MovimientoInventario_Producto] FOREIGN KEY([idProducto])
REFERENCES [Producto].[Producto] ([idProducto])
GO
ALTER TABLE [Inventario].[MovimientoInventario] CHECK CONSTRAINT [FK_MovimientoInventario_Producto]
GO
ALTER TABLE [Inventario].[MovimientoInventario]  WITH CHECK ADD  CONSTRAINT [FK_MovimientoInventario_Proveedor] FOREIGN KEY([idProveedor])
REFERENCES [Tercero].[Proveedor] ([idProveedor])
GO
ALTER TABLE [Inventario].[MovimientoInventario] CHECK CONSTRAINT [FK_MovimientoInventario_Proveedor]
GO
ALTER TABLE [Inventario].[SaldoInventario]  WITH CHECK ADD  CONSTRAINT [FK_SaldoInventario_Producto] FOREIGN KEY([idProducto])
REFERENCES [Producto].[Producto] ([idProducto])
GO
ALTER TABLE [Inventario].[SaldoInventario] CHECK CONSTRAINT [FK_SaldoInventario_Producto]
GO
ALTER TABLE [Inventario].[SaldoInventario]  WITH CHECK ADD  CONSTRAINT [FK_SaldoInventario_Proveedor] FOREIGN KEY([idProveedor])
REFERENCES [Tercero].[Proveedor] ([idProveedor])
GO
ALTER TABLE [Inventario].[SaldoInventario] CHECK CONSTRAINT [FK_SaldoInventario_Proveedor]
GO
ALTER TABLE [Producto].[Categoria]  WITH CHECK ADD  CONSTRAINT [FK_Categoria_Categoria] FOREIGN KEY([idCategoriaCentral])
REFERENCES [Producto].[Categoria] ([idCategoria])
GO
ALTER TABLE [Producto].[Categoria] CHECK CONSTRAINT [FK_Categoria_Categoria]
GO
ALTER TABLE [Producto].[ImagenProducto]  WITH CHECK ADD  CONSTRAINT [FK_ImagenProducto_Producto] FOREIGN KEY([idProducto])
REFERENCES [Producto].[Producto] ([idProducto])
GO
ALTER TABLE [Producto].[ImagenProducto] CHECK CONSTRAINT [FK_ImagenProducto_Producto]
GO
ALTER TABLE [Producto].[Producto]  WITH CHECK ADD  CONSTRAINT [FK_Producto_Categoria] FOREIGN KEY([idCategoria])
REFERENCES [Producto].[Categoria] ([idCategoria])
GO
ALTER TABLE [Producto].[Producto] CHECK CONSTRAINT [FK_Producto_Categoria]
GO
ALTER TABLE [Producto].[Producto]  WITH CHECK ADD  CONSTRAINT [FK_Producto_Marca] FOREIGN KEY([idMarca])
REFERENCES [Producto].[Marca] ([idMarca])
GO
ALTER TABLE [Producto].[Producto] CHECK CONSTRAINT [FK_Producto_Marca]
GO
ALTER TABLE [Producto].[Promocion]  WITH CHECK ADD  CONSTRAINT [FK_Promocion_SaldoInventario] FOREIGN KEY([idSaldoInventario])
REFERENCES [Inventario].[SaldoInventario] ([idSaldoInventario])
GO
ALTER TABLE [Producto].[Promocion] CHECK CONSTRAINT [FK_Promocion_SaldoInventario]
GO
ALTER TABLE [Seguridad].[Usuario]  WITH CHECK ADD  CONSTRAINT [FK_Usuario_DatoBasicoTercero] FOREIGN KEY([idDatoBasicoTercero])
REFERENCES [Tercero].[DatoBasicoTercero] ([idDatoBasicoTercero])
GO
ALTER TABLE [Seguridad].[Usuario] CHECK CONSTRAINT [FK_Usuario_DatoBasicoTercero]
GO
ALTER TABLE [Seguridad].[Usuario]  WITH CHECK ADD  CONSTRAINT [FK_Usuario_TipoUsuario] FOREIGN KEY([idTipoUsuario])
REFERENCES [Seguridad].[TipoUsuario] ([idTipoUsuario])
GO
ALTER TABLE [Seguridad].[Usuario] CHECK CONSTRAINT [FK_Usuario_TipoUsuario]
GO
ALTER TABLE [Tercero].[Cliente]  WITH CHECK ADD  CONSTRAINT [FK_Cliente_DatoBasicoTercero] FOREIGN KEY([idDatoBasicoTercero])
REFERENCES [Tercero].[DatoBasicoTercero] ([idDatoBasicoTercero])
GO
ALTER TABLE [Tercero].[Cliente] CHECK CONSTRAINT [FK_Cliente_DatoBasicoTercero]
GO
ALTER TABLE [Tercero].[Cliente]  WITH CHECK ADD  CONSTRAINT [FK_Cliente_Municipio] FOREIGN KEY([idMunicipio])
REFERENCES [Tercero].[Municipio] ([idMunicipio])
GO
ALTER TABLE [Tercero].[Cliente] CHECK CONSTRAINT [FK_Cliente_Municipio]
GO
ALTER TABLE [Tercero].[DatoBasicoTercero]  WITH CHECK ADD  CONSTRAINT [FK_DatoBasicoTercero_TipoDocumento] FOREIGN KEY([idTipoDocumento])
REFERENCES [Tercero].[TipoDocumento] ([idTipoDocumento])
GO
ALTER TABLE [Tercero].[DatoBasicoTercero] CHECK CONSTRAINT [FK_DatoBasicoTercero_TipoDocumento]
GO
ALTER TABLE [Tercero].[Departamento]  WITH CHECK ADD  CONSTRAINT [FK_Departamento_Pais] FOREIGN KEY([idPais])
REFERENCES [Tercero].[Pais] ([idPais])
GO
ALTER TABLE [Tercero].[Departamento] CHECK CONSTRAINT [FK_Departamento_Pais]
GO
ALTER TABLE [Tercero].[Municipio]  WITH CHECK ADD  CONSTRAINT [FK_Municipio_Departamento] FOREIGN KEY([idDepartamento])
REFERENCES [Tercero].[Departamento] ([idDepartamento])
GO
ALTER TABLE [Tercero].[Municipio] CHECK CONSTRAINT [FK_Municipio_Departamento]
GO
ALTER TABLE [Tercero].[Proveedor]  WITH CHECK ADD  CONSTRAINT [FK_Proveedor_DatoBasicoTercero] FOREIGN KEY([idDatoBasicoTercero])
REFERENCES [Tercero].[DatoBasicoTercero] ([idDatoBasicoTercero])
GO
ALTER TABLE [Tercero].[Proveedor] CHECK CONSTRAINT [FK_Proveedor_DatoBasicoTercero]
GO
ALTER TABLE [Tercero].[ProveedorCuenta]  WITH CHECK ADD  CONSTRAINT [FK_ProveedorCuenta_ProveedorOficina] FOREIGN KEY([idProveedorOficina])
REFERENCES [Tercero].[ProveedorOficina] ([idProveedorOficina])
GO
ALTER TABLE [Tercero].[ProveedorCuenta] CHECK CONSTRAINT [FK_ProveedorCuenta_ProveedorOficina]
GO
ALTER TABLE [Tercero].[ProveedorCuenta]  WITH CHECK ADD  CONSTRAINT [FK_ProveedorCuenta_TipoCuenta] FOREIGN KEY([idTipoCuenta])
REFERENCES [Compra].[TipoCuenta] ([idTipoCuenta])
GO
ALTER TABLE [Tercero].[ProveedorCuenta] CHECK CONSTRAINT [FK_ProveedorCuenta_TipoCuenta]
GO
ALTER TABLE [Tercero].[ProveedorCuenta]  WITH CHECK ADD  CONSTRAINT [FK_ProveedorCuenta_ViaPago] FOREIGN KEY([idViaPago])
REFERENCES [Compra].[ViaPago] ([idViaPago])
GO
ALTER TABLE [Tercero].[ProveedorCuenta] CHECK CONSTRAINT [FK_ProveedorCuenta_ViaPago]
GO
ALTER TABLE [Tercero].[ProveedorOficina]  WITH CHECK ADD  CONSTRAINT [FK_ProveedorOficina_Municipio] FOREIGN KEY([idMunicipio])
REFERENCES [Tercero].[Municipio] ([idMunicipio])
GO
ALTER TABLE [Tercero].[ProveedorOficina] CHECK CONSTRAINT [FK_ProveedorOficina_Municipio]
GO
ALTER TABLE [Tercero].[ProveedorOficina]  WITH CHECK ADD  CONSTRAINT [FK_ProveedorOficina_Proveedor] FOREIGN KEY([idProveedor])
REFERENCES [Tercero].[Proveedor] ([idProveedor])
GO
ALTER TABLE [Tercero].[ProveedorOficina] CHECK CONSTRAINT [FK_ProveedorOficina_Proveedor]
GO
ALTER TABLE [Venta].[PedidoVenta]  WITH CHECK ADD  CONSTRAINT [FK_PedidoVenta_Cliente] FOREIGN KEY([idCliente])
REFERENCES [Tercero].[Cliente] ([idCliente])
GO
ALTER TABLE [Venta].[PedidoVenta] CHECK CONSTRAINT [FK_PedidoVenta_Cliente]
GO
ALTER TABLE [Venta].[PedidoVenta]  WITH CHECK ADD  CONSTRAINT [FK_PedidoVenta_EstadoVenta] FOREIGN KEY([idEstadoPedidoVenta])
REFERENCES [Venta].[EstadoPedidoVenta] ([idEstadoPedidoVenta])
GO
ALTER TABLE [Venta].[PedidoVenta] CHECK CONSTRAINT [FK_PedidoVenta_EstadoVenta]
GO
ALTER TABLE [Venta].[PedidoVenta]  WITH CHECK ADD  CONSTRAINT [FK_PedidoVenta_ViaPago] FOREIGN KEY([idViaPago])
REFERENCES [Compra].[ViaPago] ([idViaPago])
GO
ALTER TABLE [Venta].[PedidoVenta] CHECK CONSTRAINT [FK_PedidoVenta_ViaPago]
GO
ALTER TABLE [Venta].[PedidoVentaPosicion]  WITH CHECK ADD  CONSTRAINT [FK_PedidoVentaPosicion_PedidoVenta] FOREIGN KEY([idPedidoVenta])
REFERENCES [Venta].[PedidoVenta] ([idPedidoVenta])
GO
ALTER TABLE [Venta].[PedidoVentaPosicion] CHECK CONSTRAINT [FK_PedidoVentaPosicion_PedidoVenta]
GO
ALTER TABLE [Venta].[PedidoVentaPosicion]  WITH CHECK ADD  CONSTRAINT [FK_PedidoVentaPosicion_Producto] FOREIGN KEY([idProducto])
REFERENCES [Producto].[Producto] ([idProducto])
GO
ALTER TABLE [Venta].[PedidoVentaPosicion] CHECK CONSTRAINT [FK_PedidoVentaPosicion_Producto]
GO
EXEC sys.sp_addextendedproperty @name=N'MS_Description', @value=N'url del documento del pago escaneado' , @level0type=N'SCHEMA',@level0name=N'Compra', @level1type=N'TABLE',@level1name=N'PedidoCompra', @level2type=N'COLUMN',@level2name=N'urlDocumentoPago'
GO
EXEC sys.sp_addextendedproperty @name=N'MS_Description', @value=N'Es el costo total de la compra' , @level0type=N'SCHEMA',@level0name=N'Inventario', @level1type=N'TABLE',@level1name=N'SaldoInventario', @level2type=N'COLUMN',@level2name=N'costoTotal'
GO
EXEC sys.sp_addextendedproperty @name=N'MS_Description', @value=N'es el valor de la venta unitaria, este sa calcula asi; ' , @level0type=N'SCHEMA',@level0name=N'Inventario', @level1type=N'TABLE',@level1name=N'SaldoInventario', @level2type=N'COLUMN',@level2name=N'valorVenta'
GO

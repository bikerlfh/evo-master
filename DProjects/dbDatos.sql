USE [master]
GO
/****** Object:  Database [evo]    Script Date: 21/03/2016 2:12:47 ******/
CREATE DATABASE [evo]
 CONTAINMENT = NONE
 ON  PRIMARY 
( NAME = N'evo', FILENAME = N'C:\Program Files\Microsoft SQL Server\MSSQL11.MSSQLSERVER\MSSQL\DATA\evo.mdf' , SIZE = 5120KB , MAXSIZE = UNLIMITED, FILEGROWTH = 1024KB )
 LOG ON 
( NAME = N'evo_log', FILENAME = N'C:\Program Files\Microsoft SQL Server\MSSQL11.MSSQLSERVER\MSSQL\DATA\evo_log.ldf' , SIZE = 2048KB , MAXSIZE = 2048GB , FILEGROWTH = 10%)
GO
ALTER DATABASE [evo] SET COMPATIBILITY_LEVEL = 110
GO
IF (1 = FULLTEXTSERVICEPROPERTY('IsFullTextInstalled'))
begin
EXEC [evo].[dbo].[sp_fulltext_database] @action = 'enable'
end
GO
ALTER DATABASE [evo] SET ANSI_NULL_DEFAULT OFF 
GO
ALTER DATABASE [evo] SET ANSI_NULLS OFF 
GO
ALTER DATABASE [evo] SET ANSI_PADDING OFF 
GO
ALTER DATABASE [evo] SET ANSI_WARNINGS OFF 
GO
ALTER DATABASE [evo] SET ARITHABORT OFF 
GO
ALTER DATABASE [evo] SET AUTO_CLOSE OFF 
GO
ALTER DATABASE [evo] SET AUTO_CREATE_STATISTICS ON 
GO
ALTER DATABASE [evo] SET AUTO_SHRINK OFF 
GO
ALTER DATABASE [evo] SET AUTO_UPDATE_STATISTICS ON 
GO
ALTER DATABASE [evo] SET CURSOR_CLOSE_ON_COMMIT OFF 
GO
ALTER DATABASE [evo] SET CURSOR_DEFAULT  GLOBAL 
GO
ALTER DATABASE [evo] SET CONCAT_NULL_YIELDS_NULL OFF 
GO
ALTER DATABASE [evo] SET NUMERIC_ROUNDABORT OFF 
GO
ALTER DATABASE [evo] SET QUOTED_IDENTIFIER OFF 
GO
ALTER DATABASE [evo] SET RECURSIVE_TRIGGERS OFF 
GO
ALTER DATABASE [evo] SET  DISABLE_BROKER 
GO
ALTER DATABASE [evo] SET AUTO_UPDATE_STATISTICS_ASYNC OFF 
GO
ALTER DATABASE [evo] SET DATE_CORRELATION_OPTIMIZATION OFF 
GO
ALTER DATABASE [evo] SET TRUSTWORTHY OFF 
GO
ALTER DATABASE [evo] SET ALLOW_SNAPSHOT_ISOLATION OFF 
GO
ALTER DATABASE [evo] SET PARAMETERIZATION SIMPLE 
GO
ALTER DATABASE [evo] SET READ_COMMITTED_SNAPSHOT OFF 
GO
ALTER DATABASE [evo] SET HONOR_BROKER_PRIORITY OFF 
GO
ALTER DATABASE [evo] SET RECOVERY FULL 
GO
ALTER DATABASE [evo] SET  MULTI_USER 
GO
ALTER DATABASE [evo] SET PAGE_VERIFY CHECKSUM  
GO
ALTER DATABASE [evo] SET DB_CHAINING OFF 
GO
ALTER DATABASE [evo] SET FILESTREAM( NON_TRANSACTED_ACCESS = OFF ) 
GO
ALTER DATABASE [evo] SET TARGET_RECOVERY_TIME = 0 SECONDS 
GO
EXEC sys.sp_db_vardecimal_storage_format N'evo', N'ON'
GO
USE [evo]
GO
/****** Object:  Schema [Compra]    Script Date: 21/03/2016 2:12:47 ******/
CREATE SCHEMA [Compra]
GO
/****** Object:  Schema [Inventario]    Script Date: 21/03/2016 2:12:47 ******/
CREATE SCHEMA [Inventario]
GO
/****** Object:  Schema [Producto]    Script Date: 21/03/2016 2:12:47 ******/
CREATE SCHEMA [Producto]
GO
/****** Object:  Schema [Seguridad]    Script Date: 21/03/2016 2:12:47 ******/
CREATE SCHEMA [Seguridad]
GO
/****** Object:  Schema [Tercero]    Script Date: 21/03/2016 2:12:47 ******/
CREATE SCHEMA [Tercero]
GO
/****** Object:  Schema [Venta]    Script Date: 21/03/2016 2:12:47 ******/
CREATE SCHEMA [Venta]
GO
/****** Object:  StoredProcedure [Compra].[AutorizarPedidoCompra]    Script Date: 21/03/2016 2:12:47 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
-- =============================================
-- Author:		Luis Fernando Henriquez Arciniegas
-- Create date: 21/03/2016
-- Description:	Autoriza el Pedido Compra.
-- =============================================
CREATE PROCEDURE [Compra].[AutorizarPedidoCompra]
	@idPedidoCompra bigint,
	@urlDocumentoPago varchar(MAX),
	@idUsuario bigint
AS
BEGIN
	DECLARE @resultado VARCHAR(MAX)
	BEGIN TRY
		BEGIN TRAN
		SET NOCOUNT ON;

		DECLARE @idProveedor BIGINT = (SELECT idProveedor FROM Compra.PedidoCompra WHERE idPedidoCompra = @idPedidoCompra)
		DECLARE @idEstadoPedidoAutorizado smallint = (SELECT idEstadoPedido FROM Compra.EstadoPedido WHERE codigo = '02')
		IF @idEstadoPedidoAutorizado IS NULL
		BEGIN 
			ROLLBACK TRAN
			SELECT 'ERROR: No se ha encontrado el estado del pedido "02 - AUTORIZADO"' AS 'result'
		END
		-- SE ACTUALIZA EL ESTADO DEL PEDIDO
		UPDATE Compra.PedidoCompra 
		SET idEstadoPedido = @idEstadoPedidoAutorizado,
		urlDocumentoPago = @urlDocumentoPago
		WHERE idPedidoCompra = @idPedidoCompra

		-- SE RECORRE LOS DETALLES DEL PEDIDO PARA GENERAR EL MOVIMIENTO DEL INVENTARIO
		DECLARE @idProducto BIGINT, @cantidad BIGINT, @valorCompra DECIMAL(10,2)
		DECLARE cursor_detalle CURSOR FOR  
		SELECT idProducto,cantidad,valorCompra FROM Compra.PedidoCompraPosicion WHERE idPedidoCompra = @idPedidoCompra

		OPEN cursor_detalle   
		FETCH NEXT FROM cursor_detalle INTO @idProducto,@cantidad,@valorCompra 

		WHILE @@FETCH_STATUS = 0   
		BEGIN   
			EXEC [Inventario].[GuardarMovimientoInventario]
						@idProducto,
						@idProveedor,
						1,
						@cantidad ,
						@valorCompra,
						@idUsuario,
						@resultado  OUTPUT

			IF UPPER(@resultado) <> 'TRUE'
			BEGIN
				CLOSE cursor_detalle   
				DEALLOCATE cursor_detalle
				ROLLBACK TRAN
				SELECT @resultado AS 'result'
				RETURN
			END

			FETCH NEXT FROM cursor_detalle INTO @idProducto,@cantidad,@valorCompra    
		END   
		CLOSE cursor_detalle   
		DEALLOCATE cursor_detalle
		
		COMMIT TRAN
		SELECT 'true' AS 'result'
	END TRY
	BEGIN CATCH
		ROLLBACK TRAN
		SELECT 'ERROR:' + CONVERT(VARCHAR,ERROR_NUMBER()) + ' - ' + ERROR_MESSAGE() AS 'result';
	END CATCH
	
END

GO
/****** Object:  StoredProcedure [Compra].[ConsultaAvanzadaPedidoCompra]    Script Date: 21/03/2016 2:12:47 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
-- =============================================
-- Author:		Luis Fernando Henriquez Arciniegas
-- Create date: 13/03/2016
-- Description:	consulta avanzada pedido compra	
-- =============================================
CREATE PROCEDURE [Compra].[ConsultaAvanzadaPedidoCompra]
	@numeroPedido bigint,
	@idProveedor bigint ,
	@idEstadoPedido smallint 
AS
BEGIN
	-- SET NOCOUNT ON added to prevent extra result sets from
	-- interfering with SELECT statements.
	SET NOCOUNT ON;

    SELECT p.idPedidoCompra,p.numeroPedido,p.idEstadoPedido,p.idProveedor,p.fechaPedido,
	       estado.codigo + ' - ' + estado.descripcion AS 'descripcionEstadoPedido',
		   CONVERT(VARCHAR,dbt.nit) + ' - ' + dbt.descripcion AS 'nombreProveedor' ,
		   (SELECT pro.codigo + ' - ' + pro.nombre + ' : (' + CONVERT(VARCHAR,pp.cantidad)+') ,' FROM Compra.PedidoCompraPosicion pp
		    INNER JOIN Producto.Producto pro ON PP.idProducto = pro.idProducto
			WHERE pp.idPedidoCompra = p.idPedidoCompra
			FOR XML PATH('')) AS 'pedidoDetalle'
	FROM Compra.PedidoCompra p
	INNER JOIN Compra.EstadoPedido estado ON p.idEstadoPedido = estado.idEstadoPedido
	INNER JOIN Tercero.Proveedor proveedor ON p.idProveedor = proveedor.idProveedor
	INNER JOIN Tercero.DatoBasicoTercero dbt ON  proveedor.idDatoBasicoTercero = dbt.idDatoBasicoTercero
	WHERE (p.numeroPedido =  @numeroPedido OR @numeroPedido IS NULL) AND
	(p.idProveedor =  @idProveedor OR @idProveedor IS NULL) AND
	(p.idEstadoPedido =  @idEstadoPedido OR @idEstadoPedido IS NULL)
	
END

GO
/****** Object:  StoredProcedure [Compra].[GuardarPedidoCompra]    Script Date: 21/03/2016 2:12:47 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
-- =============================================
-- Author:		Luis Fernando Henriquez Arciniegas
-- Create date: 12/03/2016
-- Description:	Guarda el pedido compra devolviendo el idPedidoCompra
-- =============================================
CREATE PROCEDURE [Compra].[GuardarPedidoCompra]
	@idEstadoPedido smallint,
	@idProveedor bigint,
	@urlDocumentoPago varchar(max),
	@idUsuarioCreacion bigint
AS
BEGIN
	SET NOCOUNT ON;
	INSERT INTO [Compra].[PedidoCompra]
				([idEstadoPedido],
				 [numeroPedido],	
				 [idProveedor],
				 [fechaPedido],
				 [urlDocumentoPago],
				 [idUsuarioCreacion])
			VALUES
				(@idEstadoPedido,
				 (select case when max(numeroPedido) is null then 1 else max(numeroPedido)+1 end
				  from Compra.PedidoCompra),
				 @idProveedor,
				 GETDATE(),
				 @urlDocumentoPago,
				 @idUsuarioCreacion)
	
	SELECT @@IDENTITY AS 'idPedidoCompra';
END

GO
/****** Object:  StoredProcedure [Compra].[GuardarPedidoCompraPosicion]    Script Date: 21/03/2016 2:12:47 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
-- =============================================
-- Author:		Luis Fernando Henriquez Arciniegas
-- Create date: 14/03/2016
-- Description:	Guarda el pedido compra posicion
-- =============================================
CREATE PROCEDURE [Compra].[GuardarPedidoCompraPosicion]
	@idPedidoCompra bigint,
    @idProducto bigint,
    @cantidad bigint,
    @valorCompra decimal(10,2),
    @idUsuarioCreacion bigint
AS
BEGIN
	SET NOCOUNT ON;
	DECLARE @resultado VARCHAR(MAX)
BEGIN TRY
	BEGIN TRANSACTION
	DECLARE @idProveedor BIGINT = (SELECT idProveedor FROM Compra.PedidoCompra WHERE idPedidoCompra = @idPedidoCompra)
	
	INSERT INTO [Compra].[PedidoCompraPosicion]
				([idPedidoCompra]
				,[idProducto]
				,[cantidad]
				,[valorCompra]
				,[idUsuarioCreacion])
			VALUES
				(@idPedidoCompra
				,@idProducto
				,@cantidad
				,@valorCompra
				,@idUsuarioCreacion);

	 -- SE GENERA EL MOVIMIENTO DEL INVENTARIO
	 --EXEC Inventario.GuardarMovimientoInventario
		--				@idProducto,
		--				@idProveedor,
		--				1,
		--				@cantidad,
		--				@valorCompra,
		--				@idUsuarioCreacion,
		--				@resultado OUTPUT
	--IF @resultado <> 'true'
	--BEGIN
	--	ROLLBACK TRANSACTION
	--END
	--ELSE BEGIN
	--	COMMIT TRANSACTION
	--END
	COMMIT TRANSACTION
	SET @resultado = 'true'
END TRY
BEGIN CATCH
	ROLLBACK TRANSACTION
	SET @resultado = 'ERROR:' + CONVERT(VARCHAR,ERROR_NUMBER()) + ' - ' + ERROR_MESSAGE();
END CATCH
	
SELECT @resultado AS 'result';
END

GO
/****** Object:  StoredProcedure [Inventario].[GuardarMovimientoInventario]    Script Date: 21/03/2016 2:12:47 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
-- =============================================
-- Author:		Luis Fernando Henriquez Arciniegas
-- Create date: 14/03/2016
-- Description:	guarda el movimiento de inventario
-- Advertencia: Al invocar este procedimiento, se debe tener una transaccion
-- =============================================
CREATE PROCEDURE [Inventario].[GuardarMovimientoInventario]
	@idProducto bigint,
	@idProveedor bigint,
	@entradaSalida bit,
	@cantidad bigint,
	@valorMovimiento decimal(10,2),
	@idUsuarioCreacion bigint,
	@resultado VARCHAR(MAX) OUTPUT
AS
BEGIN
BEGIN TRY
	BEGIN TRAN
	DECLARE @valorVenta DECIMAL(10,2)
	-- CONSULTAMOS EL SALDO INVENTARIO
	DECLARE @idSaldoInventario BIGINT = (SELECT idSaldoInventario FROM Inventario.SaldoInventario WHERE idProducto = @idProducto AND idProveedor = @idProveedor)	
	
	-- SI NO HAY SALDO INVENTARIO DEL PRODUCTO Y PROVEEDOR SE CREA
	IF @idSaldoInventario IS NULL
	BEGIN
		-- GUARDA EL VALOR DE VENTA CON UNA GANANCIA DEL 30%
		SET @valorVenta = @valorMovimiento + ((@valorMovimiento * 30) /100)
		INSERT INTO [Inventario].[SaldoInventario]
				   ([idProducto]
				   ,[idProveedor]
				   ,[cantidad]
				   ,[valorCompra]
				   ,[valorVenta]
				   ,[idUsuarioCreacion]
				   ,[idUsuarioModificacion]
				   ,[fechaCreacion]
				   ,[fechaModificacion])
			 VALUES
				   (@idProducto,
				    @idProveedor,
				    @cantidad,
				    @valorMovimiento,
				    @valorVenta,
				    @idUsuarioCreacion,
				    @idUsuarioCreacion,
				    GETDATE(),
					GETDATE())
	END
	ELSE BEGIN
		-- SI EXISTE EL SALDO INVENTARIO SE RESTA LA CANTIDAD AL STOCK
		-- OJO, NO SE VALIDA POR AHORA SI EL PRODUCTO TIENE STOCK

		UPDATE Inventario.SaldoInventario 
		SET cantidad = CASE WHEN @entradaSalida = 1 THEN  
							(cantidad + @cantidad)
					   ELSE
							CASE WHEN (cantidad - @cantidad)< 0 THEN  0 ELSE (cantidad - @cantidad) END 
					   END,
		fechaModificacion = GETDATE(),
		idUsuarioModificacion = @idUsuarioCreacion
		WHERE idSaldoInventario = @idSaldoInventario
	END
	-- SE GUARDA EL MOVIMIENTO DE INVENTARIO
	INSERT INTO [Inventario].[MovimientoInventario]
				([idProducto]
				,[idProveedor]
				,[entradaSalida]
				,[cantidad]
				,[valorMovimiento]
				,[fecha]
				,[idUsuarioCreacion])
			VALUES
				(@idProducto,
				 @idProveedor ,
				 @entradaSalida ,
				 @cantidad ,
				 @valorMovimiento ,
				 GETDATE() ,
				 @idUsuarioCreacion)
	SET @resultado = 'true'
	COMMIT TRAN
	RETURN
END TRY
BEGIN CATCH
	ROLLBACK TRAN
	SET @resultado = 'ERROR:' + CONVERT(VARCHAR,ERROR_NUMBER()) + ' - ' + ERROR_MESSAGE();
END CATCH

END

GO
/****** Object:  StoredProcedure [Producto].[ConsultaAvanzadaProducto]    Script Date: 21/03/2016 2:12:47 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
-- =============================================
-- Author:		<Author,,Name>
-- Create date: <Create Date,,>
-- Description:	<Description,,>
-- =============================================
CREATE PROCEDURE [Producto].[ConsultaAvanzadaProducto]
	
	@idMarca int,
	@idCategoria int,
	@referencia varchar(max),
	@codigo varchar(max),
	@nombre varchar(max)
AS
BEGIN
	-- SET NOCOUNT ON added to prevent extra result sets from
	-- interfering with SELECT statements.
	SET NOCOUNT ON;

    -- Insert statements for procedure here
	SELECT p.*, m.codigo + ' - ' + m.descripcion 'descripcionMarca',c.codigo + ' - ' + c.descripcion 'descripcionCategoria' 
	FROM Producto.Producto p
	INNER JOIN Producto.Marca m ON p.idMarca = m.idMarca
	INNER JOIN Producto.Categoria c ON p.idCategoria = c.idCategoria
	 WHERE (p.idMarca = @idMarca OR @idMarca IS NULL) AND
	 (p.idCategoria = @idCategoria OR @idCategoria IS NULL) AND
	 (p.referencia = @referencia OR LEN(@referencia) = 0) AND
	 (UPPER(p.codigo) like '%'+ UPPER(@codigo)+'%' OR LEN(@codigo) = 0) AND
	 (UPPER(p.nombre) like '%'+ UPPER(@nombre)+'%' OR LEN(@nombre) = 0) 
	 
END

GO
/****** Object:  StoredProcedure [Tercero].[ConsultaAvanzadaProveedor]    Script Date: 21/03/2016 2:12:47 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO

-- =============================================
-- Author:		<Author,,Name>
-- Create date: <Create Date,,>
-- Description:	<Description,,>
-- =============================================
CREATE PROCEDURE [Tercero].[ConsultaAvanzadaProveedor]
	
	@nit bigint,
	@descripcion varchar(max)
AS
BEGIN
	-- SET NOCOUNT ON added to prevent extra result sets from
	-- interfering with SELECT statements.
	SET NOCOUNT ON;

    -- Insert statements for procedure here
	
	SELECT 
		proveedor.*,
		CONVERT(VARCHAR,dbt.nit)+' - ' +dbt.descripcion as 'descripcionTercero',
		dbt.nit
	FROM Tercero.Proveedor as proveedor
	INNER JOIN Tercero.DatoBasicoTercero as dbt on proveedor.idDatoBasicoTercero = dbt.idDatoBasicoTercero
	WHERE 
		(@nit IS NULL OR dbt.nit = @nit) AND
		(
			LEN(@descripcion) = 0 OR 
			UPPER(dbt.descripcion) LIKE '%' + UPPER(@descripcion) + '%' OR
			UPPER(dbt.primerNombre) LIKE '%' + UPPER(@descripcion) + '%' OR
			UPPER(dbt.segundoNombre) LIKE '%' + UPPER(@descripcion) + '%' OR
			UPPER(dbt.primerApellido) LIKE '%' + UPPER(@descripcion) + '%' OR
			UPPER(dbt.segundoApellido) LIKE '%' + UPPER(@descripcion) + '%' 
		)
	 
END
GO
/****** Object:  StoredProcedure [Tercero].[ConsultaAvanzadaTercero]    Script Date: 21/03/2016 2:12:47 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
-- =============================================
-- Author:		<Author,,Name>
-- Create date: <Create Date,,>
-- Description:	<Description,,>
-- =============================================
CREATE PROCEDURE [Tercero].[ConsultaAvanzadaTercero]
	
	@nit bigint,
	@descripcion varchar(max)
AS
BEGIN
	-- SET NOCOUNT ON added to prevent extra result sets from
	-- interfering with SELECT statements.
	SET NOCOUNT ON;

    -- Insert statements for procedure here
	
	SELECT 
		dbt.*,
		tipoDoc.codigo + ' - ' + tipoDoc.descripcion as 'descripcionTipoDocumento'
	FROM Tercero.DatoBasicoTercero as dbt
	INNER JOIN Tercero.TipoDocumento as tipoDoc on dbt.idTipoDocumento = tipoDoc.idTipoDocumento
	WHERE 
		(@nit IS NULL OR dbt.nit = @nit) AND
		(
			LEN(@descripcion) = 0 OR 
			UPPER(dbt.descripcion) LIKE '%' + UPPER(@descripcion) + '%' OR
			UPPER(dbt.primerNombre) LIKE '%' + UPPER(@descripcion) + '%' OR
			UPPER(dbt.segundoNombre) LIKE '%' + UPPER(@descripcion) + '%' OR
			UPPER(dbt.primerApellido) LIKE '%' + UPPER(@descripcion) + '%' OR
			UPPER(dbt.segundoApellido) LIKE '%' + UPPER(@descripcion) + '%' 
		)
	 
END
GO
/****** Object:  Table [Compra].[EstadoPedido]    Script Date: 21/03/2016 2:12:47 ******/
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
/****** Object:  Table [Compra].[PedidoCompra]    Script Date: 21/03/2016 2:12:47 ******/
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
/****** Object:  Table [Compra].[PedidoCompraPosicion]    Script Date: 21/03/2016 2:12:47 ******/
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
/****** Object:  Table [Compra].[TipoCuenta]    Script Date: 21/03/2016 2:12:47 ******/
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
/****** Object:  Table [Compra].[ViaPago]    Script Date: 21/03/2016 2:12:47 ******/
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
/****** Object:  Table [Inventario].[MovimientoInventario]    Script Date: 21/03/2016 2:12:47 ******/
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
/****** Object:  Table [Inventario].[SaldoInventario]    Script Date: 21/03/2016 2:12:47 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [Inventario].[SaldoInventario](
	[idSaldoInventario] [bigint] IDENTITY(1,1) NOT NULL,
	[idProducto] [bigint] NOT NULL,
	[idProveedor] [bigint] NOT NULL,
	[cantidad] [int] NOT NULL,
	[valorCompra] [decimal](10, 2) NOT NULL,
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
/****** Object:  Table [Producto].[Categoria]    Script Date: 21/03/2016 2:12:47 ******/
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
/****** Object:  Table [Producto].[ImagenProducto]    Script Date: 21/03/2016 2:12:47 ******/
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
/****** Object:  Table [Producto].[Marca]    Script Date: 21/03/2016 2:12:47 ******/
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
/****** Object:  Table [Producto].[Producto]    Script Date: 21/03/2016 2:12:47 ******/
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
/****** Object:  Table [Seguridad].[TipoUsuario]    Script Date: 21/03/2016 2:12:47 ******/
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
/****** Object:  Table [Seguridad].[Usuario]    Script Date: 21/03/2016 2:12:47 ******/
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
/****** Object:  Table [Tercero].[Cliente]    Script Date: 21/03/2016 2:12:47 ******/
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
/****** Object:  Table [Tercero].[DatoBasicoTercero]    Script Date: 21/03/2016 2:12:47 ******/
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
/****** Object:  Table [Tercero].[Departamento]    Script Date: 21/03/2016 2:12:47 ******/
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
/****** Object:  Table [Tercero].[Municipio]    Script Date: 21/03/2016 2:12:47 ******/
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
/****** Object:  Table [Tercero].[Pais]    Script Date: 21/03/2016 2:12:47 ******/
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
/****** Object:  Table [Tercero].[Proveedor]    Script Date: 21/03/2016 2:12:47 ******/
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
/****** Object:  Table [Tercero].[ProveedorCuenta]    Script Date: 21/03/2016 2:12:47 ******/
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
/****** Object:  Table [Tercero].[ProveedorOficina]    Script Date: 21/03/2016 2:12:47 ******/
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
/****** Object:  Table [Tercero].[TipoDocumento]    Script Date: 21/03/2016 2:12:47 ******/
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
/****** Object:  Table [Venta].[EstadoPedidoVenta]    Script Date: 21/03/2016 2:12:47 ******/
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
/****** Object:  Table [Venta].[PedidoVenta]    Script Date: 21/03/2016 2:12:47 ******/
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
/****** Object:  Table [Venta].[PedidoVentaPosicion]    Script Date: 21/03/2016 2:12:47 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [Venta].[PedidoVentaPosicion](
	[idPedidoVentaPosicion] [bigint] IDENTITY(1,1) NOT NULL,
	[idPedidoVenta] [bigint] NOT NULL,
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
SET IDENTITY_INSERT [Compra].[EstadoPedido] ON 

INSERT [Compra].[EstadoPedido] ([idEstadoPedido], [codigo], [descripcion]) VALUES (1, N'01', N'SOLICITADO')
INSERT [Compra].[EstadoPedido] ([idEstadoPedido], [codigo], [descripcion]) VALUES (2, N'02', N'AUTORIZADO')
INSERT [Compra].[EstadoPedido] ([idEstadoPedido], [codigo], [descripcion]) VALUES (3, N'03', N'RECIBIDO')
INSERT [Compra].[EstadoPedido] ([idEstadoPedido], [codigo], [descripcion]) VALUES (4, N'04', N'ANULADO')
INSERT [Compra].[EstadoPedido] ([idEstadoPedido], [codigo], [descripcion]) VALUES (6, N'05', N'CANCELADO')
SET IDENTITY_INSERT [Compra].[EstadoPedido] OFF
SET IDENTITY_INSERT [Compra].[PedidoCompra] ON 

INSERT [Compra].[PedidoCompra] ([idPedidoCompra], [numeroPedido], [idEstadoPedido], [idProveedor], [fechaPedido], [urlDocumentoPago], [idUsuarioCreacion]) VALUES (29, 1, 2, 2, CAST(0x0000A5D00021AA63 AS DateTime), N'./public/imguploads/compra/pedido_compra_1.jpg', 1)
SET IDENTITY_INSERT [Compra].[PedidoCompra] OFF
SET IDENTITY_INSERT [Compra].[PedidoCompraPosicion] ON 

INSERT [Compra].[PedidoCompraPosicion] ([idPedidoCompraPosicion], [idPedidoCompra], [idProducto], [cantidad], [valorCompra], [idUsuarioCreacion]) VALUES (43, 29, 1, 1, CAST(10000.00 AS Decimal(10, 2)), 1)
SET IDENTITY_INSERT [Compra].[PedidoCompraPosicion] OFF
SET IDENTITY_INSERT [Compra].[TipoCuenta] ON 

INSERT [Compra].[TipoCuenta] ([idTipoCuenta], [codigo], [descripcion]) VALUES (2, N'01', N'AHORROS')
SET IDENTITY_INSERT [Compra].[TipoCuenta] OFF
SET IDENTITY_INSERT [Compra].[ViaPago] ON 

INSERT [Compra].[ViaPago] ([idViaPago], [codigo], [descripcion]) VALUES (2, N'01', N'CONSIGNACION')
SET IDENTITY_INSERT [Compra].[ViaPago] OFF
SET IDENTITY_INSERT [Inventario].[MovimientoInventario] ON 

INSERT [Inventario].[MovimientoInventario] ([idMovimientoInventario], [idProducto], [idProveedor], [entradaSalida], [cantidad], [valorMovimiento], [fecha], [idUsuarioCreacion]) VALUES (35, 1, 2, 1, 1, CAST(10000.00 AS Decimal(10, 2)), CAST(0x0000A5D00021C420 AS DateTime), 1)
SET IDENTITY_INSERT [Inventario].[MovimientoInventario] OFF
SET IDENTITY_INSERT [Inventario].[SaldoInventario] ON 

INSERT [Inventario].[SaldoInventario] ([idSaldoInventario], [idProducto], [idProveedor], [cantidad], [valorCompra], [valorVenta], [fechaCreacion], [fechaModificacion], [idUsuarioCreacion], [idUsuarioModificacion]) VALUES (38, 1, 2, 1, CAST(10000.00 AS Decimal(10, 2)), CAST(13000.00 AS Decimal(10, 2)), CAST(0x0000A5D00021C420 AS DateTime), CAST(0x0000A5D00021C420 AS DateTime), 1, 1)
SET IDENTITY_INSERT [Inventario].[SaldoInventario] OFF
SET IDENTITY_INSERT [Producto].[Categoria] ON 

INSERT [Producto].[Categoria] ([idCategoria], [idCategoriaCentral], [codigo], [descripcion]) VALUES (3, NULL, N'02', N'CPU')
INSERT [Producto].[Categoria] ([idCategoria], [idCategoriaCentral], [codigo], [descripcion]) VALUES (7, NULL, N'01', N'BOARD')
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
SET IDENTITY_INSERT [Tercero].[DatoBasicoTercero] ON 

INSERT [Tercero].[DatoBasicoTercero] ([idDatoBasicoTercero], [idTipoDocumento], [nit], [descripcion], [primerNombre], [segundoNombre], [primerApellido], [segundoApellido], [direccion], [telefono]) VALUES (1, 1, 1075239048, N'LUIS FERNANDO HENRIQUEZ ARCINIEGAS', N'LUIS', N'FERNANDO', N'HENRIQUEZ', N'ARCINIEGAS', N'cll 14a # ', N'123456789')
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
SET ANSI_PADDING ON

GO
/****** Object:  Index [IX_EstadoPedido]    Script Date: 21/03/2016 2:12:47 ******/
ALTER TABLE [Compra].[EstadoPedido] ADD  CONSTRAINT [IX_EstadoPedido] UNIQUE NONCLUSTERED 
(
	[codigo] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, IGNORE_DUP_KEY = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
GO
SET ANSI_PADDING ON

GO
/****** Object:  Index [IX_TipoCuenta]    Script Date: 21/03/2016 2:12:47 ******/
ALTER TABLE [Compra].[TipoCuenta] ADD  CONSTRAINT [IX_TipoCuenta] UNIQUE NONCLUSTERED 
(
	[codigo] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, IGNORE_DUP_KEY = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
GO
SET ANSI_PADDING ON

GO
/****** Object:  Index [IX_ViaPago]    Script Date: 21/03/2016 2:12:47 ******/
ALTER TABLE [Compra].[ViaPago] ADD  CONSTRAINT [IX_ViaPago] UNIQUE NONCLUSTERED 
(
	[codigo] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, IGNORE_DUP_KEY = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
GO
/****** Object:  Index [IX_SaldoInventario]    Script Date: 21/03/2016 2:12:47 ******/
ALTER TABLE [Inventario].[SaldoInventario] ADD  CONSTRAINT [IX_SaldoInventario] UNIQUE NONCLUSTERED 
(
	[idProducto] ASC,
	[idProveedor] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, IGNORE_DUP_KEY = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
GO
SET ANSI_PADDING ON

GO
/****** Object:  Index [IX_Categoria]    Script Date: 21/03/2016 2:12:47 ******/
ALTER TABLE [Producto].[Categoria] ADD  CONSTRAINT [IX_Categoria] UNIQUE NONCLUSTERED 
(
	[codigo] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, IGNORE_DUP_KEY = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
GO
SET ANSI_PADDING ON

GO
/****** Object:  Index [IX_Marca]    Script Date: 21/03/2016 2:12:47 ******/
ALTER TABLE [Producto].[Marca] ADD  CONSTRAINT [IX_Marca] UNIQUE NONCLUSTERED 
(
	[codigo] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, IGNORE_DUP_KEY = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
GO
SET ANSI_PADDING ON

GO
/****** Object:  Index [IX_TipoUsuario]    Script Date: 21/03/2016 2:12:47 ******/
ALTER TABLE [Seguridad].[TipoUsuario] ADD  CONSTRAINT [IX_TipoUsuario] UNIQUE NONCLUSTERED 
(
	[codigo] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, IGNORE_DUP_KEY = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
GO
SET ANSI_PADDING ON

GO
/****** Object:  Index [IX_Usuario]    Script Date: 21/03/2016 2:12:47 ******/
ALTER TABLE [Seguridad].[Usuario] ADD  CONSTRAINT [IX_Usuario] UNIQUE NONCLUSTERED 
(
	[email] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, IGNORE_DUP_KEY = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
GO
/****** Object:  Index [IX_DatoBasicoTercero]    Script Date: 21/03/2016 2:12:47 ******/
ALTER TABLE [Tercero].[DatoBasicoTercero] ADD  CONSTRAINT [IX_DatoBasicoTercero] UNIQUE NONCLUSTERED 
(
	[nit] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, IGNORE_DUP_KEY = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
GO
SET ANSI_PADDING ON

GO
/****** Object:  Index [IX_Departamento]    Script Date: 21/03/2016 2:12:47 ******/
ALTER TABLE [Tercero].[Departamento] ADD  CONSTRAINT [IX_Departamento] UNIQUE NONCLUSTERED 
(
	[codigo] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, IGNORE_DUP_KEY = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
GO
SET ANSI_PADDING ON

GO
/****** Object:  Index [IX_Municipio]    Script Date: 21/03/2016 2:12:47 ******/
ALTER TABLE [Tercero].[Municipio] ADD  CONSTRAINT [IX_Municipio] UNIQUE NONCLUSTERED 
(
	[codigo] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, IGNORE_DUP_KEY = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
GO
SET ANSI_PADDING ON

GO
/****** Object:  Index [IX_Pais]    Script Date: 21/03/2016 2:12:47 ******/
ALTER TABLE [Tercero].[Pais] ADD  CONSTRAINT [IX_Pais] UNIQUE NONCLUSTERED 
(
	[codigo] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, IGNORE_DUP_KEY = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
GO
SET ANSI_PADDING ON

GO
/****** Object:  Index [IX_TipoDocumento]    Script Date: 21/03/2016 2:12:47 ******/
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
USE [master]
GO
ALTER DATABASE [evo] SET  READ_WRITE 
GO

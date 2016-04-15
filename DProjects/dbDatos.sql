USE [master]
GO
/****** Object:  Database [evo]    Script Date: 14/04/2016 23:55:09 ******/
CREATE DATABASE [evo]
 CONTAINMENT = NONE
 ON  PRIMARY 
( NAME = N'evo', FILENAME = N'C:\Program Files\Microsoft SQL Server\MSSQL11.MSSQLSERVER\MSSQL\DATA\evo.mdf' , SIZE = 4160KB , MAXSIZE = UNLIMITED, FILEGROWTH = 1024KB )
 LOG ON 
( NAME = N'evo_log', FILENAME = N'C:\Program Files\Microsoft SQL Server\MSSQL11.MSSQLSERVER\MSSQL\DATA\evo_log.ldf' , SIZE = 1040KB , MAXSIZE = 2048GB , FILEGROWTH = 10%)
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
ALTER DATABASE [evo] SET AUTO_CLOSE ON 
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
ALTER DATABASE [evo] SET RECOVERY SIMPLE 
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
/****** Object:  Schema [Compra]    Script Date: 14/04/2016 23:55:09 ******/
CREATE SCHEMA [Compra]
GO
/****** Object:  Schema [Inventario]    Script Date: 14/04/2016 23:55:09 ******/
CREATE SCHEMA [Inventario]
GO
/****** Object:  Schema [Producto]    Script Date: 14/04/2016 23:55:09 ******/
CREATE SCHEMA [Producto]
GO
/****** Object:  Schema [Seguridad]    Script Date: 14/04/2016 23:55:09 ******/
CREATE SCHEMA [Seguridad]
GO
/****** Object:  Schema [Tercero]    Script Date: 14/04/2016 23:55:09 ******/
CREATE SCHEMA [Tercero]
GO
/****** Object:  Schema [Venta]    Script Date: 14/04/2016 23:55:09 ******/
CREATE SCHEMA [Venta]
GO
/****** Object:  StoredProcedure [Compra].[AutorizarPedidoCompra]    Script Date: 14/04/2016 23:55:09 ******/
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
/****** Object:  StoredProcedure [Compra].[ConsultaAvanzadaPedidoCompra]    Script Date: 14/04/2016 23:55:09 ******/
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
	(p.idEstadoPedido =  @idEstadoPedido OR @idEstadoPedido IS NULL  OR @idEstadoPedido = 0)
	
END


GO
/****** Object:  StoredProcedure [Compra].[GuardarPedidoCompra]    Script Date: 14/04/2016 23:55:09 ******/
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
/****** Object:  StoredProcedure [Compra].[GuardarPedidoCompraPosicion]    Script Date: 14/04/2016 23:55:09 ******/
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
/****** Object:  StoredProcedure [Inventario].[ConsultaAvanzadaSaldoInventario]    Script Date: 14/04/2016 23:55:09 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO

-- =============================================
-- Author:		Ezequiel David Gutierrez Cardozo
-- Create date: 20/03/2016
-- Description:	Consulta avanzada de saldo inventario
-- =============================================
CREATE PROCEDURE [Inventario].[ConsultaAvanzadaSaldoInventario]
	
	@idProducto bigint,
	@idProveedor bigint
AS
BEGIN
	SET NOCOUNT ON;
	SELECT 
		si.idSaldoInventario,
		p.idProducto,
		p.idMarca,
		p.idCategoria,
		m.codigo + ' - ' + m.descripcion AS 'descripcionMarca' ,
		c.codigo + ' - ' + c.descripcion AS 'descripcionCategoria',
		si.idProducto,
		p.codigo + ' - ' + p.nombre AS 'descripcionProducto',
		si.idProveedor,
		Convert(varchar(20),dbt.nit) + ' - ' + dbt.descripcion AS 'descripcionProveedor',
		si.valorVenta,
		si.costoTotal,
		si.cantidad,
		CASE WHEN si.estado = 1 THEN 'Activo' ELSE 'Inactivo' END AS 'estado'
		
	FROM Inventario.SaldoInventario AS si
	INNER JOIN Producto.Producto AS p ON si.idProducto = p.idProducto
	INNER JOIN Producto.Marca AS m ON p.idMarca = m.idMarca
	INNER JOIN Producto.Categoria AS c ON p.idCategoria = c.idCategoria
	INNER JOIN Tercero.Proveedor AS pro on si.idProveedor = pro.idProveedor
	INNER JOIN Tercero.DatoBasicoTercero AS dbt on pro.idDatoBasicoTercero = dbt.idDatoBasicoTercero
	WHERE (@idProducto IS NULL OR si.idProducto = @idProducto) AND
		  (@idProveedor IS NULL OR si.idProveedor = @idProveedor)
	
	
END



GO
/****** Object:  StoredProcedure [Inventario].[GuardarMovimientoInventario]    Script Date: 14/04/2016 23:55:09 ******/
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
	DECLARE @cantidadInventario BIGINT  
	DECLARE @costoTotal DECIMAL(10,2)
	DECLARE @valorVenta DECIMAL(10,2)
	DECLARE @idSaldoInventario BIGINT
	-- CONSULTAMOS EL SALDO INVENTARIO
	SELECT @idSaldoInventario = idSaldoInventario,
		   @cantidadInventario = cantidad,
		   @costoTotal = costoTotal
	FROM Inventario.SaldoInventario WHERE idProducto = @idProducto AND idProveedor = @idProveedor
	
	-- SI NO HAY SALDO INVENTARIO DEL PRODUCTO Y PROVEEDOR SE CREA
	IF @idSaldoInventario IS NULL
	BEGIN
		-- GUARDA EL VALOR DE VENTA CON UNA GANANCIA DEL 30%
		SET @valorVenta = @valorMovimiento + ((@valorMovimiento * 30) /100)
		INSERT INTO [Inventario].[SaldoInventario]
				   ([idProducto]
				   ,[idProveedor]
				   ,[cantidad]
				   ,[costoTotal]
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
		
		IF @entradaSalida = 1
		BEGIN
			-- SI ES UNA ENTRADA
			SET @cantidadInventario += @cantidad;
			SET @costoTotal += @valorMovimiento;
		END
		ELSE BEGIN
			-- SI ES UNA SALIDA
			SET @cantidadInventario -= @cantidad;
			--  costoTotal = costoTotal - (cantidad * valorUnitario)
			SET @costoTotal -= (@cantidad * (@costoTotal/@cantidadInventario));
		END

		UPDATE Inventario.SaldoInventario 
		SET cantidad = @cantidadInventario,
		    costoTotal =  @costoTotal,
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
/****** Object:  StoredProcedure [Producto].[ConsultaAvanzadaProducto]    Script Date: 14/04/2016 23:55:09 ******/
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
/****** Object:  StoredProcedure [Producto].[ConsultaAvanzadaPromocion]    Script Date: 14/04/2016 23:55:09 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
-- =============================================
-- Author:		Luis Fernando Henriquez Arciniegas
-- Create date: 02/04/2016
-- Description:	Consulta avanzada de la promoción
-- =============================================
CREATE PROCEDURE [Producto].[ConsultaAvanzadaPromocion]
	@idProducto BIGINT,
	@idProveedor BIGINT,
	@estado BIT
AS
BEGIN
	-- SET NOCOUNT ON added to prevent extra result sets from
	-- interfering with SELECT statements.
	SET NOCOUNT ON;

    SELECT pro.*,p.codigo + ' - ' + p.nombre AS 'nombreProducto',CONVERT(VARCHAR,dbt.nit) + ' - ' + dbt.descripcion AS 'nombreProveedor'  
	FROM Producto.Promocion pro 
	INNER JOIN Inventario.SaldoInventario s ON pro.idSaldoInventario = s.idSaldoInventario
	INNER JOIN Producto.Producto p ON s.idProducto = p.idProducto
	INNER JOIN Tercero.Proveedor proveedor ON s.idProveedor =  proveedor.idProveedor
	INNER JOIN Tercero.DatoBasicoTercero dbt ON proveedor.idDatoBasicoTercero = dbt.idDatoBasicoTercero
	WHERE (s.idProducto = @idProducto OR @idProducto IS NULL) AND
	(s.idProveedor = @idProveedor OR @idProveedor IS NULL) AND
	(pro.estado = @estado OR @estado IS NULL) 
END

GO
/****** Object:  StoredProcedure [Seguridad].[RegistrarCliente]    Script Date: 14/04/2016 23:55:09 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
-- =============================================
-- Author:		Luis Fernando Henriquez Arciniegas
-- Create date: 07/04/2016
-- Description:	Registrar Cliente
-- =============================================
CREATE PROCEDURE [Seguridad].[RegistrarCliente] 
	 @idTipoDocumento smallint,
	 @nit bigint,
	 @nombre varchar(30),
	 @apellido varchar(30),
	 @direccion varchar(50),
	 @telefono varchar(50),
	 @idMunicipio int,
	 @email varchar(150),
	 @clave varchar(32)
	
AS
BEGIN
BEGIN TRY
	BEGIN TRAN
	-- SET NOCOUNT ON added to prevent extra result sets from
	-- interfering with SELECT statements.
	SET NOCOUNT ON;

	DECLARE @idTipoUsuario smallint = (SELECT idTipoUsuario FROM Seguridad.TipoUsuario WHERE UPPER(descripcion) LIKE '%CLIENTE%')
	DECLARE @idDatoBasicoTercero BIGINT = (SELECT idDatoBasicoTercero FROM Tercero.DatoBasicoTercero WHERE nit = @nit)
	DECLARE @idCliente BIGINT
	-- SE VALIDA QUE NO EXISTA UN USUARIO REGISTRADO CON EL MISMO EMAIL
	IF (SELECT COUNT(*) FROM Seguridad.Usuario WHERE email = @email) > 0
	BEGIN
		SELECT 'El correo electronico ya ' + @email + ' ya esta registrado' AS 'result'
		ROLLBACK TRAN
		RETURN;
	END

	IF @idDatoBasicoTercero IS NULL
	BEGIN 
		INSERT INTO [Tercero].[DatoBasicoTercero]
			   ([idTipoDocumento]
			   ,[nit]
			   ,[descripcion]
			   ,[nombre]
			   ,[apellido]
			   ,[direccion]
			   ,[telefono])
		 VALUES
			   (@idTipoDocumento, 
				@nit,
			    @nombre + ' ' + @apellido, 
			    @nombre,
			    @apellido,
			    @direccion,
			    @telefono)
		SET @idDatoBasicoTercero = @@IDENTITY;
	END

	-- SE CONSULTA SI YA EXISTE UN CLIENTE CON EL MISMO CORREO Y EL MISMO NIT
	SET @idCliente = (SELECT idCliente FROM Tercero.Cliente WHERE email = @email AND idDatoBasicoTercero = @idDatoBasicoTercero)
	IF @idCliente IS NULL
	BEGIN
		INSERT INTO [Tercero].[Cliente]
					([idDatoBasicoTercero]
					,[idMunicipio]
					,[email]
					,[direccion]
					,[telefono])
				VALUES
					(@idDatoBasicoTercero, 
					@idMunicipio, 
					@email, 
					@direccion,
					@telefono)
		SET @idCliente = @@IDENTITY;
	END

	INSERT INTO [Seguridad].[Usuario]
				([idTipoUsuario]
				,[idDatoBasicoTercero]
				,[email]
				,[clave])
			VALUES
				(@idTipoUsuario,
				@idDatoBasicoTercero,
				@email, 
				@clave)
	SELECT 'true' AS 'result'
	COMMIT TRAN
END TRY
BEGIN CATCH
	SELECT CONVERT(VARCHAR,ERROR_NUMBER()) + ' - ' + ERROR_MESSAGE() AS 'result';
	ROLLBACK TRAN
END CATCH

END

GO
/****** Object:  StoredProcedure [Tercero].[ConsultaAvanzadaCliente]    Script Date: 14/04/2016 23:55:09 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
-- =============================================
-- Author:		Ezequiel David Gutierrez
-- Create date: 21/03/2016
-- Description:	Consulta avanzada cliente
-- =============================================
CREATE PROCEDURE [Tercero].[ConsultaAvanzadaCliente]
	
	@nit bigint,
	@descripcion varchar(max)
AS
BEGIN
	-- SET NOCOUNT ON added to prevent extra result sets from
	-- interfering with SELECT statements.
	SET NOCOUNT ON;

    -- Insert statements for procedure here
	
	SELECT 
		cliente.*,
		CONVERT(VARCHAR,dbt.nit)+' - ' +dbt.descripcion as 'descripcionTercero',
		M.codigo + ' - ' + M.descripcion as 'descripcionMunicipio',
		dbt.nit
	FROM Tercero.Cliente as cliente
	INNER JOIN Tercero.DatoBasicoTercero as dbt on cliente.idDatoBasicoTercero = dbt.idDatoBasicoTercero
	INNER JOIN Tercero.Municipio AS M on cliente.idMunicipio = M.idMunicipio
	WHERE 
		(@nit IS NULL OR dbt.nit = @nit) AND
		(
			LEN(@descripcion) = 0 OR 
			UPPER(dbt.descripcion) LIKE '%' + UPPER(@descripcion) + '%' OR
			UPPER(dbt.nombre) LIKE '%' + UPPER(@descripcion) + '%' OR
			UPPER(dbt.apellido) LIKE '%' + UPPER(@descripcion) + '%' 
		)
	 
END



GO
/****** Object:  StoredProcedure [Tercero].[ConsultaAvanzadaProveedor]    Script Date: 14/04/2016 23:55:09 ******/
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
			UPPER(dbt.nombre) LIKE '%' + UPPER(@descripcion) + '%' OR
			UPPER(dbt.apellido) LIKE '%' + UPPER(@descripcion) + '%' 
		)
	 
END

GO
/****** Object:  StoredProcedure [Tercero].[ConsultaAvanzadaTercero]    Script Date: 14/04/2016 23:55:09 ******/
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
			UPPER(dbt.nombre) LIKE '%' + UPPER(@descripcion) + '%' OR
			UPPER(dbt.apellido) LIKE '%' + UPPER(@descripcion) + '%'
		)
	 
END

GO
/****** Object:  StoredProcedure [Venta].[AutorizarPedidoVenta]    Script Date: 14/04/2016 23:55:09 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
-- =============================================
-- Author:		Ezequiel David Gutierrez Cardozo
-- Create date: 27/03/2016
-- Description:	Autoriza el Pedido Venta.
-- =============================================
CREATE PROCEDURE [Venta].[AutorizarPedidoVenta]
	@idPedidoVenta bigint,
	@urlDocumentoPago varchar(MAX),
	@idUsuario bigint
AS
BEGIN
	DECLARE @resultado VARCHAR(MAX)
	BEGIN TRY
		BEGIN TRAN
		SET NOCOUNT ON;

		DECLARE @idEstadoPedidoAutorizado smallint = (SELECT idEstadoPedidoVenta FROM Venta.EstadoPedidoVenta WHERE codigo = '02')
		IF @idEstadoPedidoAutorizado IS NULL
		BEGIN 
			ROLLBACK TRAN
			SELECT 'ERROR: No se ha encontrado el estado del pedido "02 - AUTORIZADO"' AS 'result'
		END
		-- SE ACTUALIZA EL ESTADO DEL PEDIDO
		UPDATE Venta.PedidoVenta
		SET idEstadoPedidoVenta = @idEstadoPedidoAutorizado,
		urlDocumentoPago = @urlDocumentoPago
		WHERE idPedidoVenta = @idPedidoVenta

		-- SE RECORRE LOS DETALLES DEL PEDIDO PARA GENERAR EL MOVIMIENTO DEL INVENTARIO
		DECLARE @idProducto BIGINT, @cantidad BIGINT, @valorVenta DECIMAL(10,2),@idProveedor bigint
		DECLARE cursor_detalle CURSOR FOR  
		SELECT pp.idProducto,pp.cantidad,pp.valorVenta, si.idProveedor FROM Venta.PedidoVentaPosicion pp
		INNER JOIN Inventario.SaldoInventario AS si ON pp.idSaldoInventario = si.idSaldoInventario
		WHERE idPedidoVenta = @idPedidoVenta

		OPEN cursor_detalle   
		FETCH NEXT FROM cursor_detalle INTO @idProducto,@cantidad,@valorVenta, @idProveedor

		WHILE @@FETCH_STATUS = 0   
		BEGIN   
			EXEC [Inventario].[GuardarMovimientoInventario]
						@idProducto,
						@idProveedor,
						1,
						@cantidad ,
						@valorVenta,
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

			FETCH NEXT FROM cursor_detalle INTO @idProducto,@cantidad,@valorVenta, @idProveedor
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
/****** Object:  StoredProcedure [Venta].[ConsultaAvanzadaPedidoVenta]    Script Date: 14/04/2016 23:55:09 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
-- =============================================
-- Author:		Ezequiel David Gutierrez Cardozo
-- Create date: 27/03/2016
-- Description:	consulta avanzada pedido venta	
-- =============================================
CREATE PROCEDURE [Venta].[ConsultaAvanzadaPedidoVenta]
	@numeroPedido bigint,
	@idCliente bigint ,
	@idEstadoPedido smallint 
AS
BEGIN
	-- SET NOCOUNT ON added to prevent extra result sets from
	-- interfering with SELECT statements.
	SET NOCOUNT ON;

    SELECT p.idPedidoVenta,p.numeroPedido,p.idEstadoPedidoVenta,p.idCliente,p.fechaPedido,
	       estado.codigo + ' - ' + estado.descripcion AS 'descripcionEstadoPedido',
		   CONVERT(VARCHAR,dbt.nit) + ' - ' + dbt.descripcion AS 'nombreCliente' ,
		   (SELECT pro.codigo + ' - ' + pro.nombre + ' : (' + CONVERT(VARCHAR,pp.cantidad)+') ,' FROM Venta.PedidoVentaPosicion pp
		    INNER JOIN Producto.Producto pro ON PP.idProducto = pro.idProducto
			WHERE pp.idPedidoVenta = p.idPedidoVenta
			FOR XML PATH('')) AS 'pedidoDetalle'
	FROM Venta.PedidoVenta p
	INNER JOIN Venta.EstadoPedidoVenta estado ON p.idEstadoPedidoVenta = estado.idEstadoPedidoVenta
	INNER JOIN Tercero.Cliente cliente ON p.idCliente = cliente.idCliente
	INNER JOIN Tercero.DatoBasicoTercero dbt ON  cliente.idDatoBasicoTercero = dbt.idDatoBasicoTercero
	WHERE (p.numeroPedido =  @numeroPedido OR @numeroPedido IS NULL) AND
	(p.idCliente =  @idCliente OR @idCliente IS NULL) AND
	(p.idEstadoPedidoVenta =  @idEstadoPedido OR @idEstadoPedido IS NULL  OR @idEstadoPedido = 0)
	
END


GO
/****** Object:  StoredProcedure [Venta].[GuardarPedidoVenta]    Script Date: 14/04/2016 23:55:09 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO

-- =============================================
-- Author:		Ezequiel David Gutierrez
-- Create date: 21/03/2016
-- Description:	Guarda el pedido venta retorno idPedido
-- =============================================
CREATE PROCEDURE [Venta].[GuardarPedidoVenta]
	@idEstadoPedido smallint,
	@idCliente bigint,
	@urlDocumentoPago varchar(max),
	@idUsuarioCreacion bigint
AS
BEGIN
	SET NOCOUNT ON;
	INSERT INTO Venta.PedidoVenta
				(idEstadoPedidoVenta,
				 numeroPedido,	
				 idCliente,
				 fechaPedido,
				 urlDocumentoPago,
				 idUsuarioCreacion)
			VALUES
				(@idEstadoPedido,
				 (select case when max(numeroPedido) is null then 1 else max(numeroPedido)+1 end
				  from Venta.PedidoVenta),
				 @idCliente,
				 GETDATE(),
				 @urlDocumentoPago,
				 @idUsuarioCreacion)
	
	SELECT @@IDENTITY AS 'idPedidoVenta';
END




GO
/****** Object:  StoredProcedure [Venta].[GuardarPedidoVentaPosicion]    Script Date: 14/04/2016 23:55:09 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
-- =============================================
-- Author:		Ezequiel David Gutierrez
-- Create date: 21/03/2016
-- Description:	Guarda el pedido venta posicion
-- =============================================
CREATE PROCEDURE [Venta].[GuardarPedidoVentaPosicion]
	@idPedidoVenta bigint,
	@idSaldoInventario bigint,
    @idProducto bigint,
    @cantidad bigint,
    @valorVenta decimal(10,2),
    @idUsuarioCreacion bigint
AS
BEGIN
	SET NOCOUNT ON;
	DECLARE @resultado VARCHAR(MAX)
BEGIN TRY
	DECLARE @idCliente BIGINT = (SELECT idCliente FROM Venta.PedidoVenta WHERE idPedidoVenta = @idPedidoVenta)
	
	INSERT INTO [Venta].[PedidoVentaPosicion]
				([idPedidoVenta]
				,[idSaldoInventario]
				,[idProducto]
				,[cantidad]
				,[valorVenta]
				,[idUsuarioCreacion])
			VALUES
				(@idPedidoVenta
				,@idSaldoInventario
				,@idProducto
				,@cantidad
				,@valorVenta
				,@idUsuarioCreacion);
		set @resultado = 'true';

END TRY
BEGIN CATCH
	SET @resultado = 'ERROR:' + CONVERT(VARCHAR,ERROR_NUMBER()) + ' - ' + ERROR_MESSAGE();
END CATCH
	
SELECT @resultado AS 'result';
END

GO
/****** Object:  Table [Compra].[EstadoPedido]    Script Date: 14/04/2016 23:55:09 ******/
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
/****** Object:  Table [Compra].[PedidoCompra]    Script Date: 14/04/2016 23:55:10 ******/
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
/****** Object:  Table [Compra].[PedidoCompraPosicion]    Script Date: 14/04/2016 23:55:10 ******/
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
/****** Object:  Table [Compra].[TipoCuenta]    Script Date: 14/04/2016 23:55:10 ******/
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
/****** Object:  Table [Compra].[ViaPago]    Script Date: 14/04/2016 23:55:10 ******/
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
/****** Object:  Table [Inventario].[MovimientoInventario]    Script Date: 14/04/2016 23:55:10 ******/
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
/****** Object:  Table [Inventario].[SaldoInventario]    Script Date: 14/04/2016 23:55:10 ******/
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
	[estado] [bit] NOT NULL,
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
/****** Object:  Table [Producto].[Categoria]    Script Date: 14/04/2016 23:55:10 ******/
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
/****** Object:  Table [Producto].[ImagenProducto]    Script Date: 14/04/2016 23:55:10 ******/
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
/****** Object:  Table [Producto].[Marca]    Script Date: 14/04/2016 23:55:10 ******/
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
/****** Object:  Table [Producto].[Producto]    Script Date: 14/04/2016 23:55:10 ******/
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
/****** Object:  Table [Producto].[Promocion]    Script Date: 14/04/2016 23:55:10 ******/
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
/****** Object:  Table [Seguridad].[TipoUsuario]    Script Date: 14/04/2016 23:55:10 ******/
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
/****** Object:  Table [Seguridad].[Usuario]    Script Date: 14/04/2016 23:55:10 ******/
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
/****** Object:  Table [Tercero].[Cliente]    Script Date: 14/04/2016 23:55:10 ******/
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
/****** Object:  Table [Tercero].[DatoBasicoTercero]    Script Date: 14/04/2016 23:55:10 ******/
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
	[nombre] [varchar](30) NULL,
	[apellido] [varchar](30) NULL,
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
/****** Object:  Table [Tercero].[Departamento]    Script Date: 14/04/2016 23:55:10 ******/
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
/****** Object:  Table [Tercero].[Municipio]    Script Date: 14/04/2016 23:55:10 ******/
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
/****** Object:  Table [Tercero].[Pais]    Script Date: 14/04/2016 23:55:10 ******/
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
/****** Object:  Table [Tercero].[Proveedor]    Script Date: 14/04/2016 23:55:10 ******/
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
/****** Object:  Table [Tercero].[ProveedorCuenta]    Script Date: 14/04/2016 23:55:10 ******/
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
/****** Object:  Table [Tercero].[ProveedorOficina]    Script Date: 14/04/2016 23:55:10 ******/
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
/****** Object:  Table [Tercero].[TipoDocumento]    Script Date: 14/04/2016 23:55:10 ******/
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
/****** Object:  Table [Venta].[EstadoPedidoVenta]    Script Date: 14/04/2016 23:55:10 ******/
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
/****** Object:  Table [Venta].[PedidoVenta]    Script Date: 14/04/2016 23:55:10 ******/
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
/****** Object:  Table [Venta].[PedidoVentaPosicion]    Script Date: 14/04/2016 23:55:10 ******/
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
/****** Object:  View [Venta].[vConsultaProducto]    Script Date: 14/04/2016 23:55:10 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE VIEW [Venta].[vConsultaProducto]
AS
SELECT        p.idProducto, p.codigo, p.nombre, p.referencia, p.descripcion, p.especificacion, c.descripcion AS descripcionCategoria,
                             (SELECT        TOP (1) url
                               FROM            Producto.ImagenProducto AS img
                               WHERE        (idProducto = p.idProducto)) AS urlImg, m.descripcion AS descripcionMarca, si.idSaldoInventario, si.cantidad, si.valorVenta, si.fechaCreacion AS fechaCreacionSaldoInventario, 
                         CASE WHEN promo.estado = 1 AND CONVERT(VARCHAR, promo.fechaDesde, 103) <= GETDATE() AND CONVERT(VARCHAR, promo.fechaHasta, 103) >= GETDATE() THEN promo.idPromocion ELSE NULL 
                         END AS idPromocion, promo.valorAnterior, promo.valorPromocion, (1 - ROUND(promo.valorPromocion / promo.valorAnterior, 2)) * 100 AS procentajeDescuento, promo.fechaDesde, promo.fechaHasta, 
                         c.idCategoria, m.idMarca
FROM            Producto.Producto AS p INNER JOIN
                         Producto.Categoria AS c ON p.idCategoria = c.idCategoria INNER JOIN
                         Producto.Marca AS m ON p.idMarca = m.idMarca INNER JOIN
                         Inventario.SaldoInventario AS si ON p.idProducto = si.idProducto AND p.idProducto = si.idProducto AND p.idProducto = si.idProducto AND p.idProducto = si.idProducto LEFT OUTER JOIN
                         Producto.Promocion AS promo ON si.idSaldoInventario = promo.idSaldoInventario
WHERE        (si.estado = 1)

GO
/****** Object:  View [Venta].[vConsultaProductoSimple]    Script Date: 14/04/2016 23:55:10 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE VIEW [Venta].[vConsultaProductoSimple]
AS
SELECT        p.idProducto, p.codigo, p.nombre, p.referencia,
                             (SELECT        TOP (1) url
                               FROM            Producto.ImagenProducto AS img
                               WHERE        (idProducto = p.idProducto)) AS urlImg, si.idSaldoInventario, si.cantidad, si.valorVenta, si.fechaCreacion AS fechaCreacionSaldoInventario, CASE WHEN promo.estado = 1 AND CONVERT(VARCHAR, 
                         promo.fechaDesde, 103) <= GETDATE() AND CONVERT(VARCHAR, promo.fechaHasta, 103) >= GETDATE() THEN promo.idPromocion ELSE NULL END AS idPromocion, promo.valorAnterior, promo.valorPromocion, 
                         (1 - ROUND(promo.valorPromocion / promo.valorAnterior, 2)) * 100 AS procentajeDescuento, promo.fechaDesde, promo.fechaHasta,
                             (SELECT        COUNT(*) AS Expr1
                               FROM            Inventario.MovimientoInventario AS m
                               WHERE        (idProducto = si.idProducto) AND (idProveedor = si.idProveedor) AND (entradaSalida = 0)) AS 'numeroVentas', p.idCategoria
FROM            Producto.Producto AS p INNER JOIN
                         Inventario.SaldoInventario AS si ON p.idProducto = si.idProducto AND p.idProducto = si.idProducto AND p.idProducto = si.idProducto AND p.idProducto = si.idProducto LEFT OUTER JOIN
                         Producto.Promocion AS promo ON si.idSaldoInventario = promo.idSaldoInventario
WHERE        (si.estado = 1)

GO
/****** Object:  View [Venta].[vConsultarPromocionCliente]    Script Date: 14/04/2016 23:55:10 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE VIEW [Venta].[vConsultarPromocionCliente]
AS
SELECT        p.idProducto, p.nombre, p.referencia, Inventario.SaldoInventario.cantidad, pro.idPromocion, pro.valorAnterior, pro.valorPromocion, (1 - ROUND(pro.valorPromocion / pro.valorAnterior, 2)) 
                         * 100 AS 'procentajeDescuento',
                             (SELECT        TOP (1) url
                               FROM            Producto.ImagenProducto AS img
                               WHERE        (p.idProducto = idProducto)) AS url
FROM            Producto.Producto AS p INNER JOIN
                         Inventario.SaldoInventario ON p.idProducto = Inventario.SaldoInventario.idProducto INNER JOIN
                         Producto.Promocion AS pro ON Inventario.SaldoInventario.idSaldoInventario = pro.idSaldoInventario
WHERE        (pro.estado = 1) AND (CONVERT(VARCHAR, pro.fechaDesde, 103) <= CONVERT(VARCHAR, GETDATE(), 103)) AND (CONVERT(VARCHAR, pro.fechaHasta, 103) >= CONVERT(VARCHAR, GETDATE(), 103))

GO
SET IDENTITY_INSERT [Compra].[EstadoPedido] ON 

INSERT [Compra].[EstadoPedido] ([idEstadoPedido], [codigo], [descripcion]) VALUES (1, N'01', N'SOLICITADO')
INSERT [Compra].[EstadoPedido] ([idEstadoPedido], [codigo], [descripcion]) VALUES (2, N'02', N'AUTORIZADO')
INSERT [Compra].[EstadoPedido] ([idEstadoPedido], [codigo], [descripcion]) VALUES (3, N'03', N'RECIBIDO')
INSERT [Compra].[EstadoPedido] ([idEstadoPedido], [codigo], [descripcion]) VALUES (4, N'04', N'ANULADO')
INSERT [Compra].[EstadoPedido] ([idEstadoPedido], [codigo], [descripcion]) VALUES (6, N'05', N'CANCELADO')
SET IDENTITY_INSERT [Compra].[EstadoPedido] OFF
SET IDENTITY_INSERT [Compra].[PedidoCompra] ON 

INSERT [Compra].[PedidoCompra] ([idPedidoCompra], [numeroPedido], [idEstadoPedido], [idProveedor], [fechaPedido], [urlDocumentoPago], [idUsuarioCreacion]) VALUES (29, 1, 2, 2, CAST(0x0000A5D00021AA63 AS DateTime), N'./public/imguploads/compra/pedido_compra_1.png', 1)
INSERT [Compra].[PedidoCompra] ([idPedidoCompra], [numeroPedido], [idEstadoPedido], [idProveedor], [fechaPedido], [urlDocumentoPago], [idUsuarioCreacion]) VALUES (36, 2, 2, 2, CAST(0x0000A5D500DD2A29 AS DateTime), N'./public/imguploads/compra/pedido_compra_2.jpg', 1)
INSERT [Compra].[PedidoCompra] ([idPedidoCompra], [numeroPedido], [idEstadoPedido], [idProveedor], [fechaPedido], [urlDocumentoPago], [idUsuarioCreacion]) VALUES (37, 3, 2, 2, CAST(0x0000A5D600FFDE4A AS DateTime), N'./public/imguploads/compra/pedido_compra_3.png', 1)
INSERT [Compra].[PedidoCompra] ([idPedidoCompra], [numeroPedido], [idEstadoPedido], [idProveedor], [fechaPedido], [urlDocumentoPago], [idUsuarioCreacion]) VALUES (38, 4, 2, 2, CAST(0x0000A5D60151FFE4 AS DateTime), N'./public/imguploads/compra/pedido_compra_4.png', 3)
SET IDENTITY_INSERT [Compra].[PedidoCompra] OFF
SET IDENTITY_INSERT [Compra].[PedidoCompraPosicion] ON 

INSERT [Compra].[PedidoCompraPosicion] ([idPedidoCompraPosicion], [idPedidoCompra], [idProducto], [cantidad], [valorCompra], [idUsuarioCreacion]) VALUES (43, 29, 1, 1, CAST(10000.00 AS Decimal(10, 2)), 1)
INSERT [Compra].[PedidoCompraPosicion] ([idPedidoCompraPosicion], [idPedidoCompra], [idProducto], [cantidad], [valorCompra], [idUsuarioCreacion]) VALUES (50, 36, 1, 20, CAST(25000.00 AS Decimal(10, 2)), 1)
INSERT [Compra].[PedidoCompraPosicion] ([idPedidoCompraPosicion], [idPedidoCompra], [idProducto], [cantidad], [valorCompra], [idUsuarioCreacion]) VALUES (51, 37, 1, 23, CAST(33333.00 AS Decimal(10, 2)), 1)
INSERT [Compra].[PedidoCompraPosicion] ([idPedidoCompraPosicion], [idPedidoCompra], [idProducto], [cantidad], [valorCompra], [idUsuarioCreacion]) VALUES (52, 38, 1, 12, CAST(2333.00 AS Decimal(10, 2)), 3)
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
INSERT [Inventario].[MovimientoInventario] ([idMovimientoInventario], [idProducto], [idProveedor], [entradaSalida], [cantidad], [valorMovimiento], [fecha], [idUsuarioCreacion]) VALUES (37, 1, 2, 1, 1, CAST(10000.00 AS Decimal(10, 2)), CAST(0x0000A5D600FDD758 AS DateTime), 1)
INSERT [Inventario].[MovimientoInventario] ([idMovimientoInventario], [idProducto], [idProveedor], [entradaSalida], [cantidad], [valorMovimiento], [fecha], [idUsuarioCreacion]) VALUES (38, 1, 2, 1, 2, CAST(1000.00 AS Decimal(10, 2)), CAST(0x0000A5D6014FF9C4 AS DateTime), 1)
INSERT [Inventario].[MovimientoInventario] ([idMovimientoInventario], [idProducto], [idProveedor], [entradaSalida], [cantidad], [valorMovimiento], [fecha], [idUsuarioCreacion]) VALUES (39, 1, 2, 1, 23, CAST(33333.00 AS Decimal(10, 2)), CAST(0x0000A5D601501FE0 AS DateTime), 3)
INSERT [Inventario].[MovimientoInventario] ([idMovimientoInventario], [idProducto], [idProveedor], [entradaSalida], [cantidad], [valorMovimiento], [fecha], [idUsuarioCreacion]) VALUES (40, 1, 2, 1, 12, CAST(2333.00 AS Decimal(10, 2)), CAST(0x0000A5D601528EBC AS DateTime), 3)
SET IDENTITY_INSERT [Inventario].[MovimientoInventario] OFF
SET IDENTITY_INSERT [Inventario].[SaldoInventario] ON 

INSERT [Inventario].[SaldoInventario] ([idSaldoInventario], [idProducto], [idProveedor], [cantidad], [costoTotal], [valorVenta], [estado], [fechaCreacion], [fechaModificacion], [idUsuarioCreacion], [idUsuarioModificacion]) VALUES (38, 1, 2, 60, CAST(68666.00 AS Decimal(10, 2)), CAST(1000.00 AS Decimal(10, 2)), 1, CAST(0x0000A5D00021C420 AS DateTime), CAST(0x0000A5D601528EBC AS DateTime), 1, 3)
INSERT [Inventario].[SaldoInventario] ([idSaldoInventario], [idProducto], [idProveedor], [cantidad], [costoTotal], [valorVenta], [estado], [fechaCreacion], [fechaModificacion], [idUsuarioCreacion], [idUsuarioModificacion]) VALUES (39, 1, 3, 50, CAST(20000.00 AS Decimal(10, 2)), CAST(25000.00 AS Decimal(10, 2)), 1, CAST(0x0000A5E50013A6DC AS DateTime), CAST(0x0000A5E50013A6DC AS DateTime), 1, 1)
INSERT [Inventario].[SaldoInventario] ([idSaldoInventario], [idProducto], [idProveedor], [cantidad], [costoTotal], [valorVenta], [estado], [fechaCreacion], [fechaModificacion], [idUsuarioCreacion], [idUsuarioModificacion]) VALUES (40, 2, 2, 10, CAST(300000.00 AS Decimal(10, 2)), CAST(325000.00 AS Decimal(10, 2)), 1, CAST(0x0000A5E500319494 AS DateTime), CAST(0x0000A5E90034D118 AS DateTime), 1, 1)
INSERT [Inventario].[SaldoInventario] ([idSaldoInventario], [idProducto], [idProveedor], [cantidad], [costoTotal], [valorVenta], [estado], [fechaCreacion], [fechaModificacion], [idUsuarioCreacion], [idUsuarioModificacion]) VALUES (41, 2, 3, 10, CAST(1000000.00 AS Decimal(10, 2)), CAST(1000001.00 AS Decimal(10, 2)), 1, CAST(0x0000A5E9005648AC AS DateTime), CAST(0x0000A5E9005648AC AS DateTime), 1, 1)
INSERT [Inventario].[SaldoInventario] ([idSaldoInventario], [idProducto], [idProveedor], [cantidad], [costoTotal], [valorVenta], [estado], [fechaCreacion], [fechaModificacion], [idUsuarioCreacion], [idUsuarioModificacion]) VALUES (42, 4, 2, 500, CAST(10000.00 AS Decimal(10, 2)), CAST(90000.00 AS Decimal(10, 2)), 1, CAST(0x0000A5E90068BC80 AS DateTime), CAST(0x0000A5E90068BC80 AS DateTime), 1, 1)
SET IDENTITY_INSERT [Inventario].[SaldoInventario] OFF
SET IDENTITY_INSERT [Producto].[Categoria] ON 

INSERT [Producto].[Categoria] ([idCategoria], [idCategoriaCentral], [codigo], [descripcion]) VALUES (3, NULL, N'02', N'Cpu')
INSERT [Producto].[Categoria] ([idCategoria], [idCategoriaCentral], [codigo], [descripcion]) VALUES (7, NULL, N'01', N'Board')
INSERT [Producto].[Categoria] ([idCategoria], [idCategoriaCentral], [codigo], [descripcion]) VALUES (8, NULL, N'03', N'Caja ATX - Chasis')
SET IDENTITY_INSERT [Producto].[Categoria] OFF
SET IDENTITY_INSERT [Producto].[ImagenProducto] ON 

INSERT [Producto].[ImagenProducto] ([idImagenProducto], [idProducto], [url], [idUsuarioCreacion]) VALUES (1, 1, N'/evo-master/public/imguploads/01_Board_GIGABYTE_Z170M-D3H_DDR3_1.png', 1)
INSERT [Producto].[ImagenProducto] ([idImagenProducto], [idProducto], [url], [idUsuarioCreacion]) VALUES (2, 1, N'/evo-master/public/imguploads/01_Board_GIGABYTE_Z170M-D3H_DDR3_2.png', 1)
INSERT [Producto].[ImagenProducto] ([idImagenProducto], [idProducto], [url], [idUsuarioCreacion]) VALUES (4, 1, N'/evo-master/public/imguploads/01_Board_GIGABYTE_Z170M-D3H_DDR3_4.png', 1)
INSERT [Producto].[ImagenProducto] ([idImagenProducto], [idProducto], [url], [idUsuarioCreacion]) VALUES (5, 1, N'/evo-master/public/imguploads/01_Board_GIGABYTE_Z170M-D3H_DDR3_5.jpg', 1)
INSERT [Producto].[ImagenProducto] ([idImagenProducto], [idProducto], [url], [idUsuarioCreacion]) VALUES (6, 1, N'/evo-master/public/imguploads/01_Board_GIGABYTE_Z170M-D3H_DDR3_5.png', 1)
INSERT [Producto].[ImagenProducto] ([idImagenProducto], [idProducto], [url], [idUsuarioCreacion]) VALUES (10005, 2, N'/evo-master/public/imguploads/Versa_A21_Caja_Thermaltake_Versa_A21_1.png', 1)
INSERT [Producto].[ImagenProducto] ([idImagenProducto], [idProducto], [url], [idUsuarioCreacion]) VALUES (10006, 2, N'/evo-master/public/imguploads/Versa_A21_Caja_Thermaltake_Versa_A21_2.png', 1)
INSERT [Producto].[ImagenProducto] ([idImagenProducto], [idProducto], [url], [idUsuarioCreacion]) VALUES (10007, 2, N'/evo-master/public/imguploads/Versa_A21_Caja_Thermaltake_Versa_A21_3.png', 1)
INSERT [Producto].[ImagenProducto] ([idImagenProducto], [idProducto], [url], [idUsuarioCreacion]) VALUES (10008, 2, N'/evo-master/public/imguploads/Versa_A21_Caja_Thermaltake_Versa_A21_4.png', 1)
INSERT [Producto].[ImagenProducto] ([idImagenProducto], [idProducto], [url], [idUsuarioCreacion]) VALUES (10009, 2, N'/evo-master/public/imguploads/Versa_A21_Caja_Thermaltake_Versa_A21_5.png', 1)
INSERT [Producto].[ImagenProducto] ([idImagenProducto], [idProducto], [url], [idUsuarioCreacion]) VALUES (10011, 4, N'/evo-master/public/imguploads/123_asdasdasdasd_1.jpg', 1)
SET IDENTITY_INSERT [Producto].[ImagenProducto] OFF
SET IDENTITY_INSERT [Producto].[Marca] ON 

INSERT [Producto].[Marca] ([idMarca], [codigo], [descripcion]) VALUES (1, N'01', N'ASUS')
INSERT [Producto].[Marca] ([idMarca], [codigo], [descripcion]) VALUES (2, N'02', N'GigaByte')
INSERT [Producto].[Marca] ([idMarca], [codigo], [descripcion]) VALUES (3, N'03', N'Thermaltake')
SET IDENTITY_INSERT [Producto].[Marca] OFF
SET IDENTITY_INSERT [Producto].[Producto] ON 

INSERT [Producto].[Producto] ([idProducto], [idMarca], [idCategoria], [codigo], [nombre], [referencia], [descripcion], [especificacion], [idUsuarioCreacion], [fechaCreacion]) VALUES (1, 2, 7, N'01', N'Board GIGABYTE Z170M-D3H DDR3', N'GA-Z170M-D3H DDR3 rev1.0', N'Las placas GIGABYTE de la serie 100 compatibles con los últimos procesadores de 6 ª generación Intel ® Core ™, una CPU de escritorio de 14nm, que cuenta con un mejor rendimiento, eficiencia energética y soporte para memoria DDR3 / DDR3L, trayendo características de vanguardia y el máximo rendimiento a su próxima generación de PC. GIGABYTE ofrece un rendimiento de almacenamiento considerablemente más rápida con el soporte para PCIe y la interfaz SATA para dispositivos SSD M.2.

 

Ofrece una resolución y expansión de sonido de alta calidad para crear los efectos de sonido más realista para los jugadores profesionales. EasyTune de GIGABYTE ™ es una interfaz sencilla y fácil de usar que permite a los usuarios ajustar sus configuraciones del sistema o ajuste de sistema y memoria relojes y voltajes en un entorno Windows. Con Smart Quick Boost, un clic es todo lo que se necesita para overclockear automáticamente el sistema, dando un aumento de rendimiento adicional cuando más lo necesita.', N'Soporta Intel ® procesador Core ™ i7 / Intel ® Core i5 ™ / Intel ® Core i3 ™ / Intel ® Pentium ® procesadores / Intel ® Celeron ® procesadores en el paquete LGA1151 Intel ® Express Chipset Z170 4 slots x DDR3 DIMM que admiten hasta 32 GB de memoria del sistema Soporte para DDR3 3200 (OC) / 3000 (OC) / 2800 (OC) / 2666 (OC) / 2400 (OC) / 2133 MHz módulos de memoria Audio Realtek ® codec ALC892 Soporte para la tecnología 2-Way AMD CrossFire', 1, CAST(0x0000A5C10089FF58 AS DateTime))
INSERT [Producto].[Producto] ([idProducto], [idMarca], [idCategoria], [codigo], [nombre], [referencia], [descripcion], [especificacion], [idUsuarioCreacion], [fechaCreacion]) VALUES (2, 3, 8, N'Versa A21', N'Caja Thermaltake Versa A21', N'CA-1A3-00M1WN-00', N'El miembro más reciente de la serie Chaser, hecho para los jugadores con los últimos componentes de juegos, ofreciendo no sólo el estilo y la personalidad, sino una combinación excepcional de refrigeración, rendimiento y capacidad de expansión para los puristas de juego para asegurar que disfrute del juego. La malla de metal negro con rayas azules de fluorescencia en el panel frontal se combinan con un gran panel de la ventana lateral transparente, Permite al usuario crear una solución completa de alta gama con facilidad con 240mm radiador sistema de refrigeración líquida y una tarjeta gráfica extra larga apoyado para proteger hardware del usuario y aumentar el potencial de overclocking de la CPU, la mayoría ofrece importante la eficiencia de enfriamiento excepcional en todo el caso.', N'Modelo	CA-1A3-00M1WN-00 Tipo de caja	Mid Tower Dimensiones Alto/ancho/Fondo	427 x 195 x 497 mm (16.8 x 7.7 x 19.6 inch) Peso neto	5.2 kg/11.5lb Panel lateral	Transparent Window color	Exterior & Interior : Black Material	SECC Sistema de Refrigeración	Front (intake) : 120 x 120 x 25 mm Rear (exhaust) : 120 x 120 x 25 mm Blue LED fan (1000rpm,16dBA) Top (exhaust) : (optional) 120 x 120 x 25 mm fan x 2 Side (intake) : (optional) 120 x 120 x 25 mm Bottom (intake) : (optional) 120 x 120 x 25 mm Bahías	- Accessible : 3 x 5.25’’ , 1 x 3.5’’ (Converted from one 5.25” drive bay) - Hidden : 6 x 3.5’’ , 1 x 2.5’’ Slots de Expansión	7 Placa base	9.6” x 9.6” (Micro ATX), 12” x 9.6” (ATX) Puertos	USB 3.0 x 2. HD Audio x 1 Fuente de Alimentación Standard PS2 PSU (optional) Preparada para RL	Supports 1/2”?3/8”?1/4” water tube Radiator Support	Top : 1 x 120 mm or 2 x 120mm otros	CPU cooler height limitation: 155mm VGA length limitation: 320mm', 1, CAST(0x0000A5E500313B84 AS DateTime))
INSERT [Producto].[Producto] ([idProducto], [idMarca], [idCategoria], [codigo], [nombre], [referencia], [descripcion], [especificacion], [idUsuarioCreacion], [fechaCreacion]) VALUES (4, 1, 7, N'123', N'asdasdasdasd', N'blablabla', N'asdasdsad', N'asdasdasdasds', 1, CAST(0x0000A5E90068A510 AS DateTime))
SET IDENTITY_INSERT [Producto].[Producto] OFF
SET IDENTITY_INSERT [Producto].[Promocion] ON 

INSERT [Producto].[Promocion] ([idPromocion], [idSaldoInventario], [valorAnterior], [valorPromocion], [fechaDesde], [fechaHasta], [estado], [idUsuarioCreacion]) VALUES (2, 38, CAST(1000.00 AS Decimal(10, 2)), CAST(800.00 AS Decimal(10, 2)), CAST(0x0000A5E400000000 AS DateTime), CAST(0x0000A5E500000000 AS DateTime), 0, 1)
INSERT [Producto].[Promocion] ([idPromocion], [idSaldoInventario], [valorAnterior], [valorPromocion], [fechaDesde], [fechaHasta], [estado], [idUsuarioCreacion]) VALUES (3, 39, CAST(25000.00 AS Decimal(10, 2)), CAST(20000.00 AS Decimal(10, 2)), CAST(0x0000A5E400000000 AS DateTime), CAST(0x0000A5F200000000 AS DateTime), 1, 1)
INSERT [Producto].[Promocion] ([idPromocion], [idSaldoInventario], [valorAnterior], [valorPromocion], [fechaDesde], [fechaHasta], [estado], [idUsuarioCreacion]) VALUES (5, 41, CAST(1000001.00 AS Decimal(10, 2)), CAST(900000.00 AS Decimal(10, 2)), CAST(0x0000A5E800000000 AS DateTime), CAST(0x0000A61500000000 AS DateTime), 1, 1)
SET IDENTITY_INSERT [Producto].[Promocion] OFF
SET IDENTITY_INSERT [Seguridad].[TipoUsuario] ON 

INSERT [Seguridad].[TipoUsuario] ([idTipoUsuario], [codigo], [descripcion]) VALUES (1, N'01', N'Administrador')
INSERT [Seguridad].[TipoUsuario] ([idTipoUsuario], [codigo], [descripcion]) VALUES (2, N'02', N'Cliente')
SET IDENTITY_INSERT [Seguridad].[TipoUsuario] OFF
SET IDENTITY_INSERT [Seguridad].[Usuario] ON 

INSERT [Seguridad].[Usuario] ([idUsuario], [idTipoUsuario], [idDatoBasicoTercero], [email], [clave]) VALUES (1, 1, 1, N'bikerlfh@hotmail.com', N'202cb962ac59075b964b07152d234b70')
INSERT [Seguridad].[Usuario] ([idUsuario], [idTipoUsuario], [idDatoBasicoTercero], [email], [clave]) VALUES (3, 1, 1, N'admin', N'202cb962ac59075b964b07152d234b70')
INSERT [Seguridad].[Usuario] ([idUsuario], [idTipoUsuario], [idDatoBasicoTercero], [email], [clave]) VALUES (8, 2, 9, N'tatis@hotmail.com', N'd41d8cd98f00b204e9800998ecf8427e')
INSERT [Seguridad].[Usuario] ([idUsuario], [idTipoUsuario], [idDatoBasicoTercero], [email], [clave]) VALUES (9, 2, 10, N'biker1223@hotmail.com', N'd41d8cd98f00b204e9800998ecf8427e')
SET IDENTITY_INSERT [Seguridad].[Usuario] OFF
SET IDENTITY_INSERT [Tercero].[Cliente] ON 

INSERT [Tercero].[Cliente] ([idCliente], [idDatoBasicoTercero], [idMunicipio], [email], [direccion], [telefono]) VALUES (1, 2, 4, N'cliente.1@hotmail.com', N'13123', N'123')
INSERT [Tercero].[Cliente] ([idCliente], [idDatoBasicoTercero], [idMunicipio], [email], [direccion], [telefono]) VALUES (6, 9, 4, N'tatis@hotmail.com', N'151589', N'15')
INSERT [Tercero].[Cliente] ([idCliente], [idDatoBasicoTercero], [idMunicipio], [email], [direccion], [telefono]) VALUES (7, 10, 4, N'biker1223@hotmail.com', N'410004', N'123213')
SET IDENTITY_INSERT [Tercero].[Cliente] OFF
SET IDENTITY_INSERT [Tercero].[DatoBasicoTercero] ON 

INSERT [Tercero].[DatoBasicoTercero] ([idDatoBasicoTercero], [idTipoDocumento], [nit], [descripcion], [nombre], [apellido], [direccion], [telefono]) VALUES (1, 1, 1075239048, N'LUIS FERNANDO HENRIQUEZ ARCINIEGAS', N'LUIS FERNANDO', N'HENRIQUEZ ARCINIEGAS', N'cll 14a # ', N'123456789')
INSERT [Tercero].[DatoBasicoTercero] ([idDatoBasicoTercero], [idTipoDocumento], [nit], [descripcion], [nombre], [apellido], [direccion], [telefono]) VALUES (2, 1, 1, N'cliente 1', N'cliente 1 ', N'cliente 1', N'asd', N'123123')
INSERT [Tercero].[DatoBasicoTercero] ([idDatoBasicoTercero], [idTipoDocumento], [nit], [descripcion], [nombre], [apellido], [direccion], [telefono]) VALUES (9, 1, 4861216, N'tatiana ramos', N'tatiana', N'ramos', N'151589', N'15')
INSERT [Tercero].[DatoBasicoTercero] ([idDatoBasicoTercero], [idTipoDocumento], [nit], [descripcion], [nombre], [apellido], [direccion], [telefono]) VALUES (10, 1, 123123, N'fercho henriquez', N'fercho', N'henriquez', N'410004', N'123213')
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
INSERT [Tercero].[Proveedor] ([idProveedor], [idDatoBasicoTercero], [email], [webSite], [idUsuarioCreacion]) VALUES (3, 9, N'asdasd@goma.com', N'blavla@asdmas.com', 1)
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

INSERT [Venta].[PedidoVenta] ([idPedidoVenta], [numeroPedido], [idCliente], [idEstadoPedidoVenta], [idViaPago], [fechaPedido], [urlDocumentoPago], [idUsuarioCreacion]) VALUES (13, 1, 1, 1, NULL, CAST(0x0000A5D601472BE6 AS DateTime), N'sfs', 3)
SET IDENTITY_INSERT [Venta].[PedidoVenta] OFF
SET IDENTITY_INSERT [Venta].[PedidoVentaPosicion] ON 

INSERT [Venta].[PedidoVentaPosicion] ([idPedidoVentaPosicion], [idPedidoVenta], [idSaldoInventario], [idProducto], [cantidad], [valorVenta], [idUsuarioCreacion]) VALUES (6, 13, 38, 1, 2, CAST(1000.00 AS Decimal(10, 2)), 3)
SET IDENTITY_INSERT [Venta].[PedidoVentaPosicion] OFF
SET ANSI_PADDING ON

GO
/****** Object:  Index [IX_EstadoPedido]    Script Date: 14/04/2016 23:55:10 ******/
ALTER TABLE [Compra].[EstadoPedido] ADD  CONSTRAINT [IX_EstadoPedido] UNIQUE NONCLUSTERED 
(
	[codigo] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, IGNORE_DUP_KEY = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
GO
SET ANSI_PADDING ON

GO
/****** Object:  Index [IX_TipoCuenta]    Script Date: 14/04/2016 23:55:10 ******/
ALTER TABLE [Compra].[TipoCuenta] ADD  CONSTRAINT [IX_TipoCuenta] UNIQUE NONCLUSTERED 
(
	[codigo] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, IGNORE_DUP_KEY = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
GO
SET ANSI_PADDING ON

GO
/****** Object:  Index [IX_ViaPago]    Script Date: 14/04/2016 23:55:10 ******/
ALTER TABLE [Compra].[ViaPago] ADD  CONSTRAINT [IX_ViaPago] UNIQUE NONCLUSTERED 
(
	[codigo] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, IGNORE_DUP_KEY = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
GO
/****** Object:  Index [IX_SaldoInventario]    Script Date: 14/04/2016 23:55:10 ******/
ALTER TABLE [Inventario].[SaldoInventario] ADD  CONSTRAINT [IX_SaldoInventario] UNIQUE NONCLUSTERED 
(
	[idProducto] ASC,
	[idProveedor] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, IGNORE_DUP_KEY = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
GO
SET ANSI_PADDING ON

GO
/****** Object:  Index [IX_Categoria]    Script Date: 14/04/2016 23:55:10 ******/
ALTER TABLE [Producto].[Categoria] ADD  CONSTRAINT [IX_Categoria] UNIQUE NONCLUSTERED 
(
	[codigo] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, IGNORE_DUP_KEY = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
GO
SET ANSI_PADDING ON

GO
/****** Object:  Index [IX_Marca]    Script Date: 14/04/2016 23:55:10 ******/
ALTER TABLE [Producto].[Marca] ADD  CONSTRAINT [IX_Marca] UNIQUE NONCLUSTERED 
(
	[codigo] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, IGNORE_DUP_KEY = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
GO
SET ANSI_PADDING ON

GO
/****** Object:  Index [IX_TipoUsuario]    Script Date: 14/04/2016 23:55:10 ******/
ALTER TABLE [Seguridad].[TipoUsuario] ADD  CONSTRAINT [IX_TipoUsuario] UNIQUE NONCLUSTERED 
(
	[codigo] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, IGNORE_DUP_KEY = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
GO
SET ANSI_PADDING ON

GO
/****** Object:  Index [IX_Usuario]    Script Date: 14/04/2016 23:55:10 ******/
ALTER TABLE [Seguridad].[Usuario] ADD  CONSTRAINT [IX_Usuario] UNIQUE NONCLUSTERED 
(
	[email] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, IGNORE_DUP_KEY = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
GO
/****** Object:  Index [IX_DatoBasicoTercero]    Script Date: 14/04/2016 23:55:10 ******/
ALTER TABLE [Tercero].[DatoBasicoTercero] ADD  CONSTRAINT [IX_DatoBasicoTercero] UNIQUE NONCLUSTERED 
(
	[nit] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, IGNORE_DUP_KEY = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
GO
SET ANSI_PADDING ON

GO
/****** Object:  Index [IX_Departamento]    Script Date: 14/04/2016 23:55:10 ******/
ALTER TABLE [Tercero].[Departamento] ADD  CONSTRAINT [IX_Departamento] UNIQUE NONCLUSTERED 
(
	[codigo] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, IGNORE_DUP_KEY = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
GO
SET ANSI_PADDING ON

GO
/****** Object:  Index [IX_Municipio]    Script Date: 14/04/2016 23:55:10 ******/
ALTER TABLE [Tercero].[Municipio] ADD  CONSTRAINT [IX_Municipio] UNIQUE NONCLUSTERED 
(
	[codigo] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, IGNORE_DUP_KEY = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
GO
SET ANSI_PADDING ON

GO
/****** Object:  Index [IX_Pais]    Script Date: 14/04/2016 23:55:10 ******/
ALTER TABLE [Tercero].[Pais] ADD  CONSTRAINT [IX_Pais] UNIQUE NONCLUSTERED 
(
	[codigo] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, IGNORE_DUP_KEY = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
GO
SET ANSI_PADDING ON

GO
/****** Object:  Index [IX_TipoDocumento]    Script Date: 14/04/2016 23:55:10 ******/
ALTER TABLE [Tercero].[TipoDocumento] ADD  CONSTRAINT [IX_TipoDocumento] UNIQUE NONCLUSTERED 
(
	[codigo] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, IGNORE_DUP_KEY = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
GO
ALTER TABLE [Inventario].[SaldoInventario] ADD  CONSTRAINT [DF_SaldoInventario_estado]  DEFAULT ((1)) FOR [estado]
GO
ALTER TABLE [Venta].[PedidoVentaPosicion] ADD  CONSTRAINT [DF_PedidoVentaPosicion_idSaldoInventario]  DEFAULT ((0)) FOR [idSaldoInventario]
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
ALTER TABLE [Venta].[PedidoVentaPosicion]  WITH CHECK ADD  CONSTRAINT [FK_PedidoVentaPosicion_SaldoInventario] FOREIGN KEY([idSaldoInventario])
REFERENCES [Inventario].[SaldoInventario] ([idSaldoInventario])
GO
ALTER TABLE [Venta].[PedidoVentaPosicion] CHECK CONSTRAINT [FK_PedidoVentaPosicion_SaldoInventario]
GO
EXEC sys.sp_addextendedproperty @name=N'MS_Description', @value=N'url del documento del pago escaneado' , @level0type=N'SCHEMA',@level0name=N'Compra', @level1type=N'TABLE',@level1name=N'PedidoCompra', @level2type=N'COLUMN',@level2name=N'urlDocumentoPago'
GO
EXEC sys.sp_addextendedproperty @name=N'MS_Description', @value=N'Es el costo total de la compra' , @level0type=N'SCHEMA',@level0name=N'Inventario', @level1type=N'TABLE',@level1name=N'SaldoInventario', @level2type=N'COLUMN',@level2name=N'costoTotal'
GO
EXEC sys.sp_addextendedproperty @name=N'MS_Description', @value=N'es el valor de la venta unitaria, este sa calcula asi; ' , @level0type=N'SCHEMA',@level0name=N'Inventario', @level1type=N'TABLE',@level1name=N'SaldoInventario', @level2type=N'COLUMN',@level2name=N'valorVenta'
GO
EXEC sys.sp_addextendedproperty @name=N'MS_DiagramPane1', @value=N'[0E232FF0-B466-11cf-A24F-00AA00A3EFFF, 1.00]
Begin DesignProperties = 
   Begin PaneConfigurations = 
      Begin PaneConfiguration = 0
         NumPanes = 4
         Configuration = "(H (1[55] 4[14] 2[14] 3) )"
      End
      Begin PaneConfiguration = 1
         NumPanes = 3
         Configuration = "(H (1 [50] 4 [25] 3))"
      End
      Begin PaneConfiguration = 2
         NumPanes = 3
         Configuration = "(H (1 [50] 2 [25] 3))"
      End
      Begin PaneConfiguration = 3
         NumPanes = 3
         Configuration = "(H (4 [30] 2 [40] 3))"
      End
      Begin PaneConfiguration = 4
         NumPanes = 2
         Configuration = "(H (1 [56] 3))"
      End
      Begin PaneConfiguration = 5
         NumPanes = 2
         Configuration = "(H (2 [66] 3))"
      End
      Begin PaneConfiguration = 6
         NumPanes = 2
         Configuration = "(H (4 [50] 3))"
      End
      Begin PaneConfiguration = 7
         NumPanes = 1
         Configuration = "(V (3))"
      End
      Begin PaneConfiguration = 8
         NumPanes = 3
         Configuration = "(H (1[56] 4[18] 2) )"
      End
      Begin PaneConfiguration = 9
         NumPanes = 2
         Configuration = "(H (1 [75] 4))"
      End
      Begin PaneConfiguration = 10
         NumPanes = 2
         Configuration = "(H (1[66] 2) )"
      End
      Begin PaneConfiguration = 11
         NumPanes = 2
         Configuration = "(H (4 [60] 2))"
      End
      Begin PaneConfiguration = 12
         NumPanes = 1
         Configuration = "(H (1) )"
      End
      Begin PaneConfiguration = 13
         NumPanes = 1
         Configuration = "(V (4))"
      End
      Begin PaneConfiguration = 14
         NumPanes = 1
         Configuration = "(V (2))"
      End
      ActivePaneConfig = 0
   End
   Begin DiagramPane = 
      Begin Origin = 
         Top = -768
         Left = 0
      End
      Begin Tables = 
         Begin Table = "p"
            Begin Extent = 
               Top = 0
               Left = 27
               Bottom = 251
               Right = 236
            End
            DisplayFlags = 280
            TopColumn = 0
         End
         Begin Table = "c"
            Begin Extent = 
               Top = 6
               Left = 285
               Bottom = 159
               Right = 494
            End
            DisplayFlags = 280
            TopColumn = 0
         End
         Begin Table = "m"
            Begin Extent = 
               Top = 6
               Left = 532
               Bottom = 119
               Right = 741
            End
            DisplayFlags = 280
            TopColumn = 0
         End
         Begin Table = "si"
            Begin Extent = 
               Top = 171
               Left = 307
               Bottom = 373
               Right = 516
            End
            DisplayFlags = 280
            TopColumn = 0
         End
         Begin Table = "promo"
            Begin Extent = 
               Top = 156
               Left = 610
               Bottom = 367
               Right = 819
            End
            DisplayFlags = 280
            TopColumn = 0
         End
      End
   End
   Begin SQLPane = 
   End
   Begin DataPane = 
      Begin ParameterDefaults = ""
      End
      Begin ColumnWidths = 20
         Width = 284
         Width = 1500
         Width = 1500
         Width = 1500
         Width = 1500
         Width = 1500
         Width = 1500
         Width = 1500
         Width = 1500
         Width = 1500
         Width = 1500
         Width = 1500
         Width = 1500
         Width = 1500
         Width = 1500
         Width' , @level0type=N'SCHEMA',@level0name=N'Venta', @level1type=N'VIEW',@level1name=N'vConsultaProducto'
GO
EXEC sys.sp_addextendedproperty @name=N'MS_DiagramPane2', @value=N' = 1500
         Width = 1500
         Width = 1500
         Width = 1500
         Width = 1500
      End
   End
   Begin CriteriaPane = 
      Begin ColumnWidths = 11
         Column = 1440
         Alias = 900
         Table = 1170
         Output = 720
         Append = 1400
         NewValue = 1170
         SortType = 1350
         SortOrder = 1410
         GroupBy = 1350
         Filter = 1350
         Or = 1350
         Or = 1350
         Or = 1350
      End
   End
End
' , @level0type=N'SCHEMA',@level0name=N'Venta', @level1type=N'VIEW',@level1name=N'vConsultaProducto'
GO
EXEC sys.sp_addextendedproperty @name=N'MS_DiagramPaneCount', @value=2 , @level0type=N'SCHEMA',@level0name=N'Venta', @level1type=N'VIEW',@level1name=N'vConsultaProducto'
GO
EXEC sys.sp_addextendedproperty @name=N'MS_DiagramPane1', @value=N'[0E232FF0-B466-11cf-A24F-00AA00A3EFFF, 1.00]
Begin DesignProperties = 
   Begin PaneConfigurations = 
      Begin PaneConfiguration = 0
         NumPanes = 4
         Configuration = "(H (1[40] 4[20] 2[20] 3) )"
      End
      Begin PaneConfiguration = 1
         NumPanes = 3
         Configuration = "(H (1 [50] 4 [25] 3))"
      End
      Begin PaneConfiguration = 2
         NumPanes = 3
         Configuration = "(H (1 [50] 2 [25] 3))"
      End
      Begin PaneConfiguration = 3
         NumPanes = 3
         Configuration = "(H (4 [30] 2 [40] 3))"
      End
      Begin PaneConfiguration = 4
         NumPanes = 2
         Configuration = "(H (1 [56] 3))"
      End
      Begin PaneConfiguration = 5
         NumPanes = 2
         Configuration = "(H (2 [66] 3))"
      End
      Begin PaneConfiguration = 6
         NumPanes = 2
         Configuration = "(H (4 [50] 3))"
      End
      Begin PaneConfiguration = 7
         NumPanes = 1
         Configuration = "(V (3))"
      End
      Begin PaneConfiguration = 8
         NumPanes = 3
         Configuration = "(H (1[56] 4[18] 2) )"
      End
      Begin PaneConfiguration = 9
         NumPanes = 2
         Configuration = "(H (1 [75] 4))"
      End
      Begin PaneConfiguration = 10
         NumPanes = 2
         Configuration = "(H (1[66] 2) )"
      End
      Begin PaneConfiguration = 11
         NumPanes = 2
         Configuration = "(H (4 [60] 2))"
      End
      Begin PaneConfiguration = 12
         NumPanes = 1
         Configuration = "(H (1) )"
      End
      Begin PaneConfiguration = 13
         NumPanes = 1
         Configuration = "(V (4))"
      End
      Begin PaneConfiguration = 14
         NumPanes = 1
         Configuration = "(V (2))"
      End
      ActivePaneConfig = 0
   End
   Begin DiagramPane = 
      Begin Origin = 
         Top = 0
         Left = 0
      End
      Begin Tables = 
         Begin Table = "p"
            Begin Extent = 
               Top = 6
               Left = 38
               Bottom = 174
               Right = 246
            End
            DisplayFlags = 280
            TopColumn = 0
         End
         Begin Table = "si"
            Begin Extent = 
               Top = 6
               Left = 779
               Bottom = 136
               Right = 988
            End
            DisplayFlags = 280
            TopColumn = 0
         End
         Begin Table = "promo"
            Begin Extent = 
               Top = 120
               Left = 532
               Bottom = 250
               Right = 741
            End
            DisplayFlags = 280
            TopColumn = 0
         End
      End
   End
   Begin SQLPane = 
   End
   Begin DataPane = 
      Begin ParameterDefaults = ""
      End
      Begin ColumnWidths = 20
         Width = 284
         Width = 1500
         Width = 1500
         Width = 1500
         Width = 1500
         Width = 2130
         Width = 1500
         Width = 1500
         Width = 1500
         Width = 2055
         Width = 1500
         Width = 1500
         Width = 1500
         Width = 1500
         Width = 1500
         Width = 1500
         Width = 1500
         Width = 1500
         Width = 1500
         Width = 1500
      End
   End
   Begin CriteriaPane = 
      Begin ColumnWidths = 11
         Column = 1440
         Alias = 900
         Table = 1170
         Output = 720
         Append = 1400
         NewValue = 1170
         SortType = 1350
         SortOrder = 1410
         GroupBy = 1350
         Filter = 1350
         Or = 1350
         Or = 1350
         Or = 1350
      End
   End
End
' , @level0type=N'SCHEMA',@level0name=N'Venta', @level1type=N'VIEW',@level1name=N'vConsultaProductoSimple'
GO
EXEC sys.sp_addextendedproperty @name=N'MS_DiagramPaneCount', @value=1 , @level0type=N'SCHEMA',@level0name=N'Venta', @level1type=N'VIEW',@level1name=N'vConsultaProductoSimple'
GO
EXEC sys.sp_addextendedproperty @name=N'MS_DiagramPane1', @value=N'[0E232FF0-B466-11cf-A24F-00AA00A3EFFF, 1.00]
Begin DesignProperties = 
   Begin PaneConfigurations = 
      Begin PaneConfiguration = 0
         NumPanes = 4
         Configuration = "(H (1[40] 4[20] 2[20] 3) )"
      End
      Begin PaneConfiguration = 1
         NumPanes = 3
         Configuration = "(H (1 [50] 4 [25] 3))"
      End
      Begin PaneConfiguration = 2
         NumPanes = 3
         Configuration = "(H (1 [50] 2 [25] 3))"
      End
      Begin PaneConfiguration = 3
         NumPanes = 3
         Configuration = "(H (4 [30] 2 [40] 3))"
      End
      Begin PaneConfiguration = 4
         NumPanes = 2
         Configuration = "(H (1 [56] 3))"
      End
      Begin PaneConfiguration = 5
         NumPanes = 2
         Configuration = "(H (2 [66] 3))"
      End
      Begin PaneConfiguration = 6
         NumPanes = 2
         Configuration = "(H (4 [50] 3))"
      End
      Begin PaneConfiguration = 7
         NumPanes = 1
         Configuration = "(V (3))"
      End
      Begin PaneConfiguration = 8
         NumPanes = 3
         Configuration = "(H (1[56] 4[18] 2) )"
      End
      Begin PaneConfiguration = 9
         NumPanes = 2
         Configuration = "(H (1 [75] 4))"
      End
      Begin PaneConfiguration = 10
         NumPanes = 2
         Configuration = "(H (1[66] 2) )"
      End
      Begin PaneConfiguration = 11
         NumPanes = 2
         Configuration = "(H (4 [60] 2))"
      End
      Begin PaneConfiguration = 12
         NumPanes = 1
         Configuration = "(H (1) )"
      End
      Begin PaneConfiguration = 13
         NumPanes = 1
         Configuration = "(V (4))"
      End
      Begin PaneConfiguration = 14
         NumPanes = 1
         Configuration = "(V (2))"
      End
      ActivePaneConfig = 0
   End
   Begin DiagramPane = 
      Begin Origin = 
         Top = 0
         Left = 0
      End
      Begin Tables = 
         Begin Table = "p"
            Begin Extent = 
               Top = 6
               Left = 38
               Bottom = 251
               Right = 247
            End
            DisplayFlags = 280
            TopColumn = 0
         End
         Begin Table = "SaldoInventario (Inventario)"
            Begin Extent = 
               Top = 6
               Left = 285
               Bottom = 245
               Right = 494
            End
            DisplayFlags = 280
            TopColumn = 0
         End
         Begin Table = "pro"
            Begin Extent = 
               Top = 6
               Left = 532
               Bottom = 230
               Right = 741
            End
            DisplayFlags = 280
            TopColumn = 0
         End
      End
   End
   Begin SQLPane = 
   End
   Begin DataPane = 
      Begin ParameterDefaults = ""
      End
      Begin ColumnWidths = 10
         Width = 284
         Width = 1245
         Width = 3030
         Width = 2730
         Width = 1500
         Width = 1500
         Width = 1500
         Width = 1950
         Width = 3330
         Width = 1500
      End
   End
   Begin CriteriaPane = 
      Begin ColumnWidths = 11
         Column = 1440
         Alias = 900
         Table = 1170
         Output = 720
         Append = 1400
         NewValue = 1170
         SortType = 1350
         SortOrder = 1410
         GroupBy = 1350
         Filter = 1350
         Or = 1350
         Or = 1350
         Or = 1350
      End
   End
End
' , @level0type=N'SCHEMA',@level0name=N'Venta', @level1type=N'VIEW',@level1name=N'vConsultarPromocionCliente'
GO
EXEC sys.sp_addextendedproperty @name=N'MS_DiagramPaneCount', @value=1 , @level0type=N'SCHEMA',@level0name=N'Venta', @level1type=N'VIEW',@level1name=N'vConsultarPromocionCliente'
GO
USE [master]
GO
ALTER DATABASE [evo] SET  READ_WRITE 
GO

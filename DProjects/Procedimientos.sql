USE [evo]
GO
/****** Object:  StoredProcedure [Compra].[AutorizarPedidoCompra]    Script Date: 10/04/2016 21:09:59 ******/
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
/****** Object:  StoredProcedure [Compra].[ConsultaAvanzadaPedidoCompra]    Script Date: 10/04/2016 21:09:59 ******/
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
/****** Object:  StoredProcedure [Compra].[GuardarPedidoCompra]    Script Date: 10/04/2016 21:09:59 ******/
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
/****** Object:  StoredProcedure [Compra].[GuardarPedidoCompraPosicion]    Script Date: 10/04/2016 21:09:59 ******/
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
/****** Object:  StoredProcedure [Inventario].[ConsultaAvanzadaSaldoInventario]    Script Date: 10/04/2016 21:09:59 ******/
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
		si.cantidad
		
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
/****** Object:  StoredProcedure [Inventario].[GuardarMovimientoInventario]    Script Date: 10/04/2016 21:09:59 ******/
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
/****** Object:  StoredProcedure [Producto].[ConsultaAvanzadaProducto]    Script Date: 10/04/2016 21:09:59 ******/
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
/****** Object:  StoredProcedure [Producto].[ConsultaAvanzadaPromocion]    Script Date: 10/04/2016 21:09:59 ******/
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
/****** Object:  StoredProcedure [Seguridad].[RegistrarCliente]    Script Date: 10/04/2016 21:09:59 ******/
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
/****** Object:  StoredProcedure [Tercero].[ConsultaAvanzadaCliente]    Script Date: 10/04/2016 21:09:59 ******/
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
/****** Object:  StoredProcedure [Tercero].[ConsultaAvanzadaProveedor]    Script Date: 10/04/2016 21:09:59 ******/
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
/****** Object:  StoredProcedure [Tercero].[ConsultaAvanzadaTercero]    Script Date: 10/04/2016 21:09:59 ******/
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
/****** Object:  StoredProcedure [Venta].[AutorizarPedidoVenta]    Script Date: 10/04/2016 21:09:59 ******/
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
/****** Object:  StoredProcedure [Venta].[ConsultaAvanzadaPedidoVenta]    Script Date: 10/04/2016 21:09:59 ******/
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
/****** Object:  StoredProcedure [Venta].[GuardarPedidoVenta]    Script Date: 10/04/2016 21:09:59 ******/
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
/****** Object:  StoredProcedure [Venta].[GuardarPedidoVentaPosicion]    Script Date: 10/04/2016 21:09:59 ******/
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
/****** Object:  View [Venta].[vConsultaProducto]    Script Date: 10/04/2016 21:09:59 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE VIEW [Venta].[vConsultaProducto]
AS
SELECT        p.idProducto, p.codigo, p.nombre, p.referencia, p.descripcion, p.especificacion, c.codigo + ' - ' + c.descripcion AS descripcionCategoria,
                             (SELECT        TOP (1) url
                               FROM            Producto.ImagenProducto AS img
                               WHERE        (idProducto = p.idProducto)) AS 'urlImg', m.codigo + ' - ' + m.descripcion AS descripcionMarca, si.idSaldoInventario, si.cantidad, si.valorVenta, CASE WHEN promo.estado = 1 AND 
                         CONVERT(VARCHAR, promo.fechaDesde, 103) <= GETDATE() AND CONVERT(VARCHAR, promo.fechaHasta, 103) >= GETDATE() THEN promo.idPromocion ELSE NULL END AS idPromocion, promo.valorAnterior, 
                         promo.valorPromocion, (1 - ROUND(promo.valorPromocion / promo.valorAnterior, 2)) * 100 AS procentajeDescuento, promo.fechaDesde, promo.fechaHasta, c.idCategoria, m.idMarca
FROM            Producto.Producto AS p INNER JOIN
                         Producto.Categoria AS c ON p.idCategoria = c.idCategoria INNER JOIN
                         Producto.Marca AS m ON p.idMarca = m.idMarca INNER JOIN
                         Inventario.SaldoInventario AS si ON p.idProducto = si.idProducto AND p.idProducto = si.idProducto AND p.idProducto = si.idProducto AND p.idProducto = si.idProducto LEFT OUTER JOIN
                         Producto.Promocion AS promo ON si.idSaldoInventario = promo.idSaldoInventario

GO
/****** Object:  View [Venta].[vConsultarPromocionCliente]    Script Date: 10/04/2016 21:09:59 ******/
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
EXEC sys.sp_addextendedproperty @name=N'MS_DiagramPane1', @value=N'[0E232FF0-B466-11cf-A24F-00AA00A3EFFF, 1.00]
Begin DesignProperties = 
   Begin PaneConfigurations = 
      Begin PaneConfiguration = 0
         NumPanes = 4
         Configuration = "(H (1[53] 4[15] 2[14] 3) )"
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
         Width = ' , @level0type=N'SCHEMA',@level0name=N'Venta', @level1type=N'VIEW',@level1name=N'vConsultaProducto'
GO
EXEC sys.sp_addextendedproperty @name=N'MS_DiagramPane2', @value=N'1500
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

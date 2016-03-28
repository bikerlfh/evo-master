USE [evo]
GO
/****** Object:  StoredProcedure [Compra].[AutorizarPedidoCompra]    Script Date: 27/03/2016 14:54:34 ******/
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
/****** Object:  StoredProcedure [Compra].[ConsultaAvanzadaPedidoCompra]    Script Date: 27/03/2016 14:54:34 ******/
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
/****** Object:  StoredProcedure [Compra].[GuardarPedidoCompra]    Script Date: 27/03/2016 14:54:34 ******/
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
/****** Object:  StoredProcedure [Compra].[GuardarPedidoCompraPosicion]    Script Date: 27/03/2016 14:54:34 ******/
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
/****** Object:  StoredProcedure [Inventario].[ConsultaAvanzadaSaldoInventario]    Script Date: 27/03/2016 14:54:34 ******/
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
/****** Object:  StoredProcedure [Inventario].[GuardarMovimientoInventario]    Script Date: 27/03/2016 14:54:34 ******/
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
/****** Object:  StoredProcedure [Producto].[ConsultaAvanzadaProducto]    Script Date: 27/03/2016 14:54:34 ******/
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
/****** Object:  StoredProcedure [Tercero].[ConsultaAvanzadaCliente]    Script Date: 27/03/2016 14:54:34 ******/
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
			UPPER(dbt.primerNombre) LIKE '%' + UPPER(@descripcion) + '%' OR
			UPPER(dbt.segundoNombre) LIKE '%' + UPPER(@descripcion) + '%' OR
			UPPER(dbt.primerApellido) LIKE '%' + UPPER(@descripcion) + '%' OR
			UPPER(dbt.segundoApellido) LIKE '%' + UPPER(@descripcion) + '%' 
		)
	 
END


GO
/****** Object:  StoredProcedure [Tercero].[ConsultaAvanzadaProveedor]    Script Date: 27/03/2016 14:54:34 ******/
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
/****** Object:  StoredProcedure [Tercero].[ConsultaAvanzadaTercero]    Script Date: 27/03/2016 14:54:34 ******/
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
/****** Object:  StoredProcedure [Venta].[GuardarPedidoVenta]    Script Date: 27/03/2016 14:54:34 ******/
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
/****** Object:  StoredProcedure [Venta].[GuardarPedidoVentaPosicion]    Script Date: 27/03/2016 14:54:34 ******/
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


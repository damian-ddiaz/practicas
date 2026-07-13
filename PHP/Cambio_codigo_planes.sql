-- 1. Definir variables para facilitar el cambio
SET @empresa = 'tecnoven';
SET @sucursal = 'cabimas2';
SET @codigo_viejo = '00004';
SET @codigo_nuevo = 'NUEVO_CODIGO'; -- <--- CAMBIA ESTO

START TRANSACTION;

-- 2. Actualizar la tabla configuracion_factura_productos
-- Se hace un JOIN desde productos -> configuracion -> servicio_cliente para filtrar por empresa/sucursal
UPDATE configuracion_factura_productos cfp
JOIN configuracion_factura cf ON cfp.id_configuracion_factura = cf.id
JOIN servicio_cliente sc ON cf.id_servicio = sc.id_servicio_cliente
SET cfp.codigo_producto = @codigo_nuevo
WHERE sc.empresa = @empresa 
  AND sc.sucursal = @sucursal 
  AND sc.codigo_producto = @codigo_viejo;

-- 3. Actualizar la tabla servicio_cliente
UPDATE servicio_cliente
SET codigo_producto = @codigo_nuevo
WHERE empresa = @empresa 
  AND sucursal = @sucursal 
  AND codigo_producto = @codigo_viejo;

-- 4. Verificar resultados (Opcional, puedes comentar estas líneas)
SELECT 'Filas actualizadas en productos:' as Info, ROW_COUNT() as Total;

-- Si todo está correcto, confirmar los cambios
COMMIT;
-- ROLLBACK; -- Usa ROLLBACK en lugar de COMMIT si quieres probar sin guardar
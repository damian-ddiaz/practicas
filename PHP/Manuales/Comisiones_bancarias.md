# Manual de Usuario: Configuración de Comisiones Bancarias
## Sistema Administrativo ICAROSoft

---

### Introducción
Este manual describe el procedimiento estándar para configurar correctamente las comisiones bancarias dentro del Sistema Administrativo ICAROSoft. El objetivo primordial es garantizar que las comisiones generadas por las entidades financieras sean registradas automáticamente y asociadas de forma inequívoca a su correspondiente proveedor, producto y cuenta contable.

### Objetivo
Estandarizar la configuración de comisiones para asegurar la correcta contabilización de los gastos operativos y facilitar la transparencia en la administración financiera de la empresa.

### Requisitos Previos
Para completar este proceso, el usuario debe poseer:
* **Acceso a los módulos:** Administrativo, Inventario, Contabilidad y Configuración.
* **Permisos de edición:** Capacidad para crear proveedores, productos y modificar parámetros globales en el módulo de configuración.

### Flujo General de Configuración
El éxito de la configuración depende del siguiente orden lógico:
1. **Proveedor:** Identificación del banco como prestador de servicio.
2. **Producto:** Creación del ítem de gasto en inventario.
3. **Contabilidad:** Enlace del ítem con el Plan de Cuentas.
4. **Banco:** Vinculación de la cuenta de banco con el proveedor.
5. **Formas de Pago:** Activación de la lógica de cálculo automático.

---

### Paso 1: Creación del Proveedor
* **Ruta:** Proyecto Administrativo $\rightarrow$ Ficha de Proveedores
* **Descripción:** Se debe registrar al banco como un proveedor para permitir la adjudicación legal de los gastos.
  * **RIF:** J-27886306
  * **Nombre:** BANESCO COMISIONES
  * **Estatus:** Activo

### Paso 2: Creación del Producto
* **Ruta:** Proyecto Inventario $\rightarrow$ Ficha de Inventario
* **Descripción:** Es necesario crear un ítem de servicio que represente el gasto de la comisión.
  * **Código:** G60202-0002
  * **Nombre:** COMISIONES BANCARIAS
  * **Tipo de Producto:** Servicio Contable
  * **Clasificación:** Grupo: Servicio Contable / Subgrupo: GASTOS

### Paso 3: Configuración Contable
* **Ruta:** Proyecto Contabilidad $\rightarrow$ Configuración $\rightarrow$ Cuentas a Productos
* **Descripción:** Seleccione el producto `COMISIONES BANCARIAS` y asigne la cuenta contable de gastos (Ej. Gastos Bancarios) para que el asiento contable se genere automáticamente al procesar el movimiento.

### Paso 4: Configuración del Banco
* **Ruta:** Proyecto Configuración $\rightarrow$ Cuentas y Bancos
* **Descripción:** Vincule la cuenta bancaria física con el proveedor creado en el Paso 1.
  * **Ejemplo:** Seleccionar Banco Banesco (`BN3094`) y asociarlo a `"BANESCO COMISIONES"`.

### Paso 5: Configuración de Tipos y Formas de Pago (CRÍTICO)
* **Ruta:** Proyecto Configuración $\rightarrow$ Formas de Pago
* **Descripción:** Este es el paso final que activa la automatización. En la ficha de la Forma de Pago, complete obligatoriamente los campos señalados con flechas rojas en el sistema:
  1. **Generar Comisión Bancaria Gastos:** Active este switch (debe mostrarse en azul/Activo). Esto habilita el procesamiento automático.
  2. **Porcentaje Comisión Bancaria:** Ingrese el valor porcentual (Ejemplo: `1,50`).
  3. **Producto:** Seleccione el código `G60202-0002` (COMISIONES BANCARIAS).
  4. **Fecha Inicio Comisión:** Defina la fecha de vigencia del cobro (Ej: `01/10/2025`).

> **⚠️ NOTA IMPORTANTE:** Sin la configuración de estos 4 campos, el sistema omitirá la generación de asientos de gasto.

---

### Resultado Esperado
Al finalizar la configuración, ICAROSoft registrará automáticamente las comisiones al realizar movimientos bancarios, generando el asiento contable y la afectación al libro mayor de forma transparente y sin intervención manual.

### Buenas Prácticas y Errores Frecuentes
* **Nombres Consistentes:** Mantenga la misma nomenclatura en proveedores y servicios para facilitar auditorías.
* **Estatus Activo:** Un proveedor o producto "Inactivo" romperá la cadena de automatización.
* **Validación de Fechas:** Si la comisión no se genera, valide que la Fecha Inicio Comisión sea igual o anterior a la fecha del movimiento bancario actual.
# Sistema de Pesaje de Café - API REST

## Descripción
API REST para la gestión de pesaje de café que permite el registro, control y seguimiento de parcialidades de café entre agricultores y beneficios.

## Roles del Sistema
- **ROLE_AGRICULTOR**: Gestiona solicitudes y envíos de pesaje
- **ROLE_BENEFICIO**: Administra cuentas y validación de pesajes
- **ROLE_PESOCABAL**: Ejecuta el proceso de pesaje físico

## Flujo Principal del Proceso

### 1. Registro y Solicitud Inicial
- Registro de agricultor
- Creación de solicitud de pesaje
  - Cantidad de granos
  - Unidad de medida
  - Tolerancia permitida
  - Precio por unidad
  - Número de parcialidades
  - Datos de transporte

### 2. Evaluación y Creación de Cuenta
- Revisión de solicitud por Beneficio
- Creación de cuenta (estado: CUENTA_CREADA)
- Generación de registro de pesaje

### 3. Configuración de Parcialidades
- Configuración por parcialidad:
  - Peso estimado
  - Datos de transporte
  - Información del transportista
  - Fecha programada
- Validación de datos de transporte

### 4. Proceso de Pesaje
- Recepción de parcialidades
- Validación de datos
- Pesaje físico
- Generación de boletas
- Actualización de estados:
  - CUENTA_ABIERTA
  - PESAJE_INICIADO

### 5. Cierre y Validación
- Finalización del pesaje
- Verificación de tolerancias
- Estados finales:
  - PESAJE_FINALIZADO
  - CUENTA_CERRADA
  - CUENTA_CONFIRMADA

## Estados del Sistema

### Estados de Cuenta
- CUENTA_CREADA
- CUENTA_ABIERTA
- CUENTA_CERRADA
- CUENTA_CONFIRMADA

### Estados de Pesaje
- PESAJE_INICIADO
- PESAJE_FINALIZADO

## Validaciones Importantes
- Verificación de transportes activos
- Control de tolerancias (±5%)
- Validación de datos de transporte
- Verificación de documentación

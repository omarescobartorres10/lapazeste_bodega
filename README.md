# SIS Bodega — Sistema de Gestión de Bodega

**SIS Bodega** es una aplicación web para la gestión integral de inventario y operaciones de bodega, desarrollada con **Laravel 12** y **Livewire 3**.

Permite administrar productos, entradas, salidas, préstamos, bajas, traslados y solicitudes de movimiento de inventario entre departamentos y bodegas.

---

## 📋 Tabla de contenidos

- [Características](#características)
- [Tecnologías](#tecnologías)
- [Requisitos](#requisitos)
- [Instalación](#instalación)
- [Módulos del sistema](#módulos-del-sistema)
- [Roles de usuario](#roles-de-usuario)
- [Exportación de datos](#exportación-de-datos)
- [Licencia](#licencia)

---

## Características

- **Gestión de inventario**: Registro y control de productos con categorías, tipos y unidades de medida.
- **Entradas y salidas**: Registro de movimientos de ingreso y egreso de productos con historial y generación de PDF.
- **Préstamos**: Gestión de préstamos de productos con opción de devolución total o parcial.
- **Bajas**: Registro de bajas de productos del inventario.
- **Traslados**: Movimiento de productos entre bodegas con historial y detalle.
- **Solicitudes públicas**: Flujo de solicitudes externas donde los solicitantes pueden solicitar préstamos, salidas o traslados.
- **Panel de administración**: Dashboard con resumen de operaciones y gestión de usuarios.
- **Exportación**: Generación de reportes en PDF y Excel.
- **Gestión de bodegas y departamentos**: Administración de múltiples bodegas y departamentos.
- **Autenticación**: Sistema de login para administradores y solicitantes.

---

## Tecnologías

| Tecnología | Versión |
|---|---|
| PHP | ^8.2 |
| Laravel | ^12.0 |
| Livewire | ^3.6 |
| Laravel DomPDF | ^3.1 |
| Maatwebsite Excel | ^3.1 |

---

## Requisitos

- **PHP** 8.2 o superior
- **Composer** para la gestión de dependencias
- **MySQL** o **MariaDB** como base de datos
- **XAMPP** (opcional, para entorno local en Windows)
- **Node.js** y **npm** (para compilación de assets Vite)

---

## Instalación

### 1. Clonar el repositorio

```bash
git clone <url-del-repositorio>
cd sis_bodega
```

### 2. Instalar dependencias

```bash
composer install
```

### 3. Configurar el entorno

```bash
cp .env.example .env
php artisan key:generate
```

Editar el archivo `.env` con los datos de tu base de datos:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sis_bodega
DB_USERNAME=tu_usuario
DB_PASSWORD=tu_contraseña
```

### 4. Ejecutar migraciones

```bash
php artisan migrate
```

### 5. Iniciar el servidor

```bash
php artisan serve
```

La aplicación estará disponible en `http://localhost:8000`.

---

## Módulos del sistema

### Panel de administración
- Dashboard con resumen de operaciones.

### Productos
- CRUD completo de productos con activación/desactivación.
- Búsqueda por bodega y departamento.
- Exportación a Excel.

### Entradas
- Registro de entradas de productos con carga de facturas.
- Historial de entradas con detalle y exportación a PDF.

### Salidas
- Registro de salidas de productos.
- Historial de salidas con detalle y exportación a PDF.
- Cancelación de salidas.

### Préstamos
- Registro de préstamos de productos.
- Devolución total o parcial.
- Historial con detalle y exportación a PDF.
- Cancelación de préstamos.

### Bajas
- Registro de bajas de productos.
- Historial con detalle y exportación a PDF.
- Cancelación de bajas.

### Traslados
- Movimiento de productos entre bodegas.
- Historial con detalle y exportación a PDF.

### Solicitudes públicas
- Flujo de solicitudes externas para préstamos, salidas y traslados.
- Autenticación de solicitantes con código.
- Aprobación/rechazo de solicitudes por parte del administrador.
- Consulta pública de solicitudes por código.
- Generación de PDF de solicitudes.

### Categorías
- CRUD de categorías de productos con activación/desactivación.

### Departamentos
- CRUD completo de departamentos.

### Bodegas
- Gestión de bodegas con activación/desactivación.

### Tipos de producto
- Gestión de tipos de producto con activación/desactivación.

### Unidades de medida
- CRUD de unidades de medida con activación/desactivación.

### Usuarios
- Gestión de usuarios del sistema con activación/desactivación.
- Validación de códigos de acceso.

---

## Roles de usuario

| Rol | Acceso |
|---|---|
| **Administrador** | Acceso completo a todos los módulos del panel de administración. |
| **Solicitante** | Acceso al flujo de solicitudes públicas para solicitar préstamos, salidas o traslados. |

---

## Exportación de datos

El sistema soporta la exportación de datos en los siguientes formatos:

- **PDF**: Entradas, salidas, préstamos, bajas, traslados, solicitudes y lista de solicitantes.
- **Excel**: Lista de productos y lista de solicitantes.

---

## Estructura del proyecto

```
sis_bodega/
├── app/
│   ├── Http/Controllers/     # Controladores del sistema
│   ├── Models/               # Modelos Eloquent
│   └── Exports/              # Clases de exportación
├── routes/
│   └── web.php               # Rutas web
├── resources/
│   └── views/                # Vistas Blade
├── database/
│   ├── migrations/           # Migraciones de base de datos
│   └── seeders/              # Seeders
├── config/                   # Archivos de configuración
└── public/                   # Archivos públicos
```

---

## Licencia

Este proyecto es software propietario. Todos los derechos reservados.

# Sistema de Gestión de Gimnasio - Versión 3.0

Este sistema permite la gestión completa de gimnasios con segmentación de datos por gimnasio. Cada gimnasio puede gestionar sus propios miembros, rutinas y membresías de forma independiente.

## Características principales

- **Segmentación completa por gimnasio**: Cada gimnasio solo puede ver y gestionar sus propios miembros, rutinas y membresías.
- **Panel para gimnasios**: Gestión de miembros, rutinas, membresías y estado de ocupación.
- **Panel para miembros**: Acceso a rutinas asignadas, información del gimnasio y detalles de membresía.
- **Sistema de autenticación**: Acceso diferenciado para gimnasios y miembros.
- **Diseño responsive**: Compatible con dispositivos móviles y de escritorio.

## Requisitos del sistema

- PHP 7.4 o superior
- MySQL 5.7 o superior
- XAMPP (recomendado para facilitar la instalación)

## Instrucciones de instalación

1. **Preparar el entorno**:
   - Instalar XAMPP (si no está instalado)
   - Iniciar los servicios de Apache y MySQL

2. **Configurar la base de datos**:
   - Crear una base de datos llamada `gimnasio_db` en phpMyAdmin
   - Usuario: `root` (por defecto en XAMPP)
   - Contraseña: (vacía por defecto en XAMPP)

3. **Instalar la aplicación**:
   - Descomprimir el archivo ZIP en la carpeta `htdocs` de XAMPP
   - Navegar a `http://localhost/gimnasio_php_v3/setup.php` para inicializar la base de datos
   - Seguir las instrucciones en pantalla para completar la configuración

## Acceso al sistema

### Acceso como gimnasio:
- **Usuario**: GymFit
- **Contraseña**: password

- **Usuario**: PowerGym
- **Contraseña**: password

### Acceso como miembro:
- **Usuario**: Juan Pérez (miembro de GymFit)
- **Contraseña**: password

- **Usuario**: Carlos Rodríguez (miembro de PowerGym)
- **Contraseña**: password

## Estructura de archivos

```
gimnasio_php_v3/
├── config.php            # Configuración de la base de datos
├── index.php             # Página de inicio de sesión
├── setup.php             # Script de configuración inicial
├── logout.php            # Script para cerrar sesión
├── gimnasio/             # Archivos para el panel de gimnasio
│   ├── dashboard.php
│   ├── miembros.php
│   ├── rutinas.php
│   ├── membresias.php
│   └── ...
└── miembro/              # Archivos para el panel de miembro
    ├── dashboard.php
    ├── rutinas.php
    ├── membresia.php
    └── ...
```

## Funcionalidades por tipo de usuario

### Gimnasio:
- Ver dashboard con estadísticas
- Gestionar miembros (agregar, editar, eliminar)
- Gestionar rutinas (agregar, editar, eliminar)
- Gestionar membresías (agregar, editar, eliminar)
- Asignar rutinas a miembros
- Actualizar estado de ocupación del gimnasio

### Miembro:
- Ver dashboard personal
- Ver rutinas asignadas
- Ver información del gimnasio
- Ver detalles de su membresía
- Ver su perfil personal

## Notas importantes

- Cada gimnasio solo puede ver y gestionar sus propios datos
- Los miembros solo pueden ver información relacionada con su gimnasio
- Las contraseñas están hasheadas por seguridad
- El sistema incluye datos de ejemplo para facilitar las pruebas

## Soporte

Para cualquier consulta o soporte, contacte al desarrollador.

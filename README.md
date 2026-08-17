# Sistema de Gestión de Inventario para Dispensario Médico

Aplicación web desarrollada para facilitar el control y la administración del inventario de un dispensario médico. El sistema permite registrar productos, gestionar existencias, organizar categorías y recibir avisos cuando un artículo necesita reposición.

El proyecto utiliza una arquitectura **Modelo–Vista–Controlador (MVC)**, lo que permite separar la lógica de negocio, la interfaz y el acceso a los datos para facilitar su mantenimiento y ampliación.

## Funcionalidades principales

- Autenticación de usuarios.
- Creación y administración de cuentas de usuario.
- Control de acceso mediante niveles de usuario y permisos.
- Registro, consulta, edición y eliminación de productos.
- Registro y actualización del inventario disponible.
- Organización de productos por categorías.
- Avisos automáticos de existencias bajas.
- Identificación de productos que necesitan reposición.
- Organización del sistema mediante páginas y módulos independientes.

## Módulos

### Productos

Permite registrar y administrar los productos utilizados por el dispensario, junto con la información necesaria para identificarlos y controlar su disponibilidad.

### Inventario

Permite agregar existencias y mantener actualizado el inventario de los productos registrados.

### Categorías

Facilita la clasificación de los productos para mejorar su organización, búsqueda y administración.

### Alertas de reposición

Genera avisos automáticos cuando las existencias de un producto alcanzan un nivel bajo, ayudando a detectar oportunamente qué artículos deben reponerse.

### Usuarios y permisos

Incluye autenticación y creación de usuarios. El acceso a las páginas y funciones del sistema se controla de acuerdo con el nivel y los permisos asignados a cada usuario.

## Tecnologías utilizadas

- **PHP:** lógica del servidor y funcionamiento del patrón MVC.
- **MySQL:** almacenamiento y administración de los datos.
- **JavaScript:** interacción y comportamiento dinámico de la interfaz.
- **HTML5:** estructura de las páginas.
- **CSS3:** estilos y presentación visual.

## Arquitectura

El sistema está basado en el patrón MVC:

- **Modelo:** administra los datos y las operaciones con la base de datos.
- **Vista:** contiene las páginas y componentes de la interfaz.
- **Controlador:** procesa las solicitudes, coordina la lógica del sistema y conecta los modelos con las vistas.

Esta separación permite mantener el código organizado y desarrollar nuevos módulos con mayor facilidad.

## Requisitos

Para ejecutar el proyecto localmente se necesita:

- PHP.
- MySQL o MariaDB.
- Un servidor web como Apache.
- Un entorno local como XAMPP, WAMP o equivalente.
- Un navegador web moderno.

## Instalación local

1. Clona el repositorio:

   ```bash
   git clone https://github.com/FTimo77/Dispensario_Med.git
   ```

2. Copia o mueve la carpeta del proyecto al directorio público de tu servidor. En XAMPP, normalmente corresponde a:

   ```text
   C:\xampp\htdocs\
   ```

3. Inicia los servicios **Apache** y **MySQL**.

4. Crea una base de datos llamada `dispensario`.

5. Importa en esa base de datos el archivo `.sql` incluido en el proyecto, utilizando phpMyAdmin o el cliente de MySQL.

6. Revisa el archivo de conexión a la base de datos y configura los datos correspondientes a tu entorno:

   - Servidor.
   - Nombre de la base de datos.
   - Usuario.
   - Contraseña.

7. Abre el sistema desde el navegador. Por ejemplo:

   ```text
   http://localhost/Dispensario_Med/
   ```

> La ubicación del archivo de conexión y la URL pueden variar según la configuración y estructura local del proyecto.

## Uso general

1. Accede al sistema con una cuenta registrada.
2. Utiliza una cuenta con permisos suficientes para crear usuarios y asignar sus niveles de acceso.
3. Registra las categorías necesarias.
4. Agrega los productos y sus datos correspondientes.
5. Registra o actualiza las existencias desde el módulo de inventario.
6. Consulta periódicamente los avisos de existencias bajas para planificar la reposición.

## Seguridad y acceso

El sistema restringe las páginas y operaciones disponibles según el nivel de cada usuario. Para mantener la seguridad de la aplicación se recomienda:

- No compartir credenciales de acceso.
- Asignar únicamente los permisos necesarios a cada usuario.
- Utilizar contraseñas seguras.
- Mantener respaldos periódicos de la base de datos.

## Objetivo del proyecto

Centralizar y automatizar el control del inventario del dispensario médico, reduciendo el tiempo necesario para administrar productos y facilitando la detección de artículos que requieren reposición.

## Autor

**Timothy Maldonado**

## Licencia

Este proyecto no tiene actualmente una licencia pública especificada. Todos los derechos permanecen reservados a su autor.

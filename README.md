# Control Finance - Sistema de Control Financiero

Sistema de gestión y control de productos financieros personales construido con Laravel 10, Livewire 3, y una arquitectura completamente dockerizada.

## 🎯 Estado del Proyecto: ~75% Completado

**✅ BACKEND 100% FUNCIONAL**
**✅ INFRAESTRUCTURA 100% LISTA**
**⚠️ FRONTEND: Componentes creados, pendiente implementación**

Ver [IMPLEMENTATION_STATUS.md](./IMPLEMENTATION_STATUS.md) para detalles completos.

## Características Principales

- **Autenticación Múltiple**: Login con Google OAuth y registro manual
- **Sistema de Roles**: Administrador y Usuario Activo con permisos granulares (Spatie Permission)
- **Aprobación de Usuarios**: Sistema de aprobación manual de nuevos usuarios
- **Consulta Pública**: Módulo sin autenticación usando DNI + OTP
- **Gestión de Productos Financieros**: Tarjetas de crédito/débito, billeteras digitales, líneas de crédito
- **Registro de Transacciones**: Gestión manual de movimientos financieros
- **Sistema de Cuotas**: Control de pagos y amortizaciones
- **Protección Anti-Spam**: Rate limiting, honeypot, verificación de email
- **Dashboards Personalizados**: Vistas específicas por rol con métricas relevantes

## Stack Tecnológico

- **Framework**: Laravel 10.x
- **Frontend Reactivo**: Livewire 3.6.4
- **PHP**: 8.1-FPM
- **Base de Datos**: MySQL 8.0
- **Cache y Queues**: Redis 7
- **Web Server**: NGINX Alpine
- **Containerización**: Docker & Docker Compose
- **Permisos**: Spatie Laravel Permission 6.22.0
- **OAuth**: Laravel Socialite 5.23.1
- **Anti-Spam**: Spatie Laravel Honeypot 4.6.1

## Arquitectura Docker

El proyecto utiliza una arquitectura de microservicios con los siguientes contenedores:

```
┌─────────────────────────────────────────────────┐
│  NGINX (Puerto 8080 → 80)                       │
│  - Servidor web                                 │
│  - Proxy reverso a PHP-FPM                      │
└────────────┬────────────────────────────────────┘
             │
┌────────────▼────────────────────────────────────┐
│  APP (PHP 8.1-FPM)                              │
│  - Aplicación Laravel                           │
│  - Puerto 9000 (FastCGI)                        │
└─────┬──────────────────────┬────────────────────┘
      │                      │
┌─────▼──────┐      ┌───────▼────────┐
│   MySQL    │      │     Redis      │
│  Puerto    │      │   Puerto 6379  │
│   3306     │      │   - Cache      │
│            │      │   - Sessions   │
│            │      │   - Queues     │
└────────────┘      └────────────────┘

┌─────────────────┐  ┌──────────────────┐
│  Queue Worker   │  │   Scheduler      │
│  - Jobs async   │  │   - Cron jobs    │
└─────────────────┘  └──────────────────┘
```

## Requisitos Previos

- Docker Desktop instalado
- Docker Compose instalado
- Make (opcional, pero recomendado para Windows: usar Git Bash o instalar make)
- Mínimo 4GB RAM disponible para Docker

## Instalación y Configuración

### 1. Clonar el Repositorio

```bash
git clone <repository-url>
cd control-finance-app
```

### 2. Configurar Variables de Entorno

El archivo `.env` ya está configurado para Docker. Verifica y ajusta si es necesario:

```env
APP_NAME="Control Finance"
APP_URL=http://localhost:8080

DB_HOST=mysql
DB_DATABASE=control_finance
DB_USERNAME=control_finance_user
DB_PASSWORD=secret

REDIS_HOST=redis
CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis
```

### 3. Levantar el Proyecto (Opción Rápida)

```bash
make setup
```

Este comando ejecutará automáticamente:
- Construcción de imágenes Docker
- Inicio de contenedores
- Instalación de dependencias Composer
- Instalación de dependencias NPM
- Generación de APP_KEY
- Ejecución de migraciones
- Ejecución de seeders

### 4. Acceder a la Aplicación

La aplicación estará disponible en: **http://localhost:8080**

## Comandos Make Disponibles

### Docker

```bash
make up          # Inicia todos los contenedores
make down        # Detiene todos los contenedores
make restart     # Reinicia todos los contenedores
make build       # Reconstruye las imágenes Docker (sin cache)
make logs        # Muestra logs en tiempo real de todos los servicios
```

### Aplicación

```bash
make shell       # Abre una shell bash dentro del contenedor de la app
make composer    # Instala/actualiza dependencias de Composer
make npm         # Instala/actualiza dependencias de NPM
make artisan     # Ejecuta un comando artisan (ej: make artisan cmd=migrate)
```

### Base de Datos

```bash
make migrate     # Ejecuta las migraciones pendientes
make fresh       # Refresca la BD (DROP + CREATE todas las tablas)
make seed        # Ejecuta los seeders
```

### Testing

```bash
make test        # Ejecuta la suite de tests PHPUnit
```

### Utilidades

```bash
make clear       # Limpia todos los caches de Laravel (config, route, view, cache)
```

## Acceso Directo sin Make

Si no tienes `make` instalado, puedes usar directamente `docker-compose`:

```bash
# Iniciar contenedores
docker-compose up -d

# Ejecutar comandos artisan
docker-compose exec app php artisan migrate

# Acceder a la shell del contenedor
docker-compose exec app bash

# Instalar dependencias
docker-compose exec app composer install

# Ver logs
docker-compose logs -f

# Detener contenedores
docker-compose down
```

## Puertos Expuestos

| Servicio | Puerto Host | Puerto Container | Descripción |
|----------|-------------|------------------|-------------|
| NGINX    | 8080        | 80               | HTTP Web Server |
| MySQL    | **3307**    | 3306             | Base de Datos |
| Redis    | **6380**    | 6379             | Cache & Queues |
| PHP-FPM  | -           | 9000             | FastCGI (interno) |

> **Nota:** Los puertos de MySQL y Redis fueron cambiados para evitar conflictos con instalaciones locales.
> Ver [SOLUCION_PUERTOS.md](./SOLUCION_PUERTOS.md) para más detalles.

## Volúmenes Persistentes

Los siguientes datos persisten incluso si detienes los contenedores:

- `mysql-data`: Base de datos MySQL
- `redis-data`: Datos de Redis

Para eliminar completamente los datos:
```bash
docker-compose down -v
```

## Estructura del Proyecto

```
control-finance-app/
├── app/                    # Código de la aplicación
│   ├── Http/
│   │   ├── Controllers/    # Controladores
│   │   ├── Livewire/       # Componentes Livewire
│   │   └── Middleware/     # Middleware personalizado
│   ├── Models/             # Modelos Eloquent
│   └── Services/           # Lógica de negocio
├── database/
│   ├── migrations/         # Migraciones de BD
│   ├── seeders/            # Seeders de datos
│   └── factories/          # Factories para testing
├── resources/
│   ├── views/              # Vistas Blade y Livewire
│   └── js/                 # Assets JavaScript
├── routes/
│   ├── web.php             # Rutas web
│   └── api.php             # Rutas API
├── docker/
│   └── nginx/
│       └── default.conf    # Configuración NGINX
├── docker-compose.yml      # Orquestación de contenedores
├── Dockerfile              # Imagen PHP-FPM personalizada
├── Makefile                # Comandos de automatización
└── .env                    # Variables de entorno
```

## Desarrollo

### Ejecutar Migraciones

```bash
make migrate
# o
docker-compose exec app php artisan migrate
```

### Crear una Nueva Migración

```bash
make artisan cmd="make:migration create_example_table"
```

### Crear un Componente Livewire

```bash
make artisan cmd="make:livewire ExampleComponent"
```

### Limpiar Cache Durante Desarrollo

```bash
make clear
```

### Ejecutar Queue Worker Manualmente

El queue worker ya corre automáticamente en su contenedor, pero si necesitas ejecutarlo manualmente:

```bash
docker-compose exec app php artisan queue:work --tries=3
```

## Troubleshooting

### Los contenedores no inician

```bash
# Verificar logs
make logs

# Reconstruir imágenes
make build

# Reiniciar todo
make restart
```

### Permisos en storage/

Si encuentras errores de permisos:

```bash
docker-compose exec app chmod -R 775 storage bootstrap/cache
docker-compose exec app chown -R www-data:www-data storage bootstrap/cache
```

### Error de conexión a MySQL

Asegúrate de que el contenedor MySQL esté completamente iniciado antes de ejecutar migraciones:

```bash
# Verificar estado
docker-compose ps

# Ver logs de MySQL
docker-compose logs mysql
```

### Puerto 8080 ya en uso

Si el puerto 8080 está ocupado, modifica `docker-compose.yml`:

```yaml
nginx:
  ports:
    - "8081:80"  # Cambia 8080 por otro puerto
```

Y actualiza `.env`:
```env
APP_URL=http://localhost:8081
```

## Seguridad

### Variables Sensibles

NUNCA subas el archivo `.env` al repositorio. El archivo `.env.example` debe contener plantillas sin valores reales.

### Cambiar Contraseñas en Producción

Antes de desplegar en producción, cambia las siguientes variables en `.env`:

```env
DB_PASSWORD=<contraseña-segura>
DB_ROOT_PASSWORD=<contraseña-root-segura>
APP_KEY=<generar-con-php-artisan-key:generate>
```

## Contribuir

1. Crea un branch para tu feature (`git checkout -b feature/AmazingFeature`)
2. Commit tus cambios (`git commit -m 'Add some AmazingFeature'`)
3. Push al branch (`git push origin feature/AmazingFeature`)
4. Abre un Pull Request

## Licencia

Este proyecto es privado y confidencial.

## Soporte

Para reportar problemas o solicitar features, abre un issue en el repositorio.

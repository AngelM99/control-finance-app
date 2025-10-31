# 🔧 Solución: Conflicto de Puertos Docker

## ❌ Problema Original

```
Error response from daemon: ports are not available:
exposing port TCP 0.0.0.0:3306 -> 127.0.0.1:0:
listen tcp 0.0.0.0:3306: bind: Only one usage of each socket address
(protocol/network address/port) is normally permitted.
```

**Causa:** MySQL local ya estaba usando el puerto 3306

## ✅ Solución Aplicada

### 1. Cambio de Puertos

**MySQL:** 3306 → **3307**
```yaml
ports:
  - "3307:3306"  # Puerto externo:Puerto interno
```

**Redis:** 6379 → **6380**
```yaml
ports:
  - "6380:6379"  # Puerto externo:Puerto interno
```

### 2. Orden de Migraciones Corregido

Se reordenaron las migraciones para respetar las dependencias de foreign keys:

```
✅ 2025_10_28_201154_create_financial_products_table.php  (primero)
✅ 2025_10_28_201155_create_installments_table.php        (segundo)
✅ 2025_10_28_201156_create_transactions_table.php        (tercero)
✅ 2025_10_28_201158_create_otp_tokens_table.php          (último)
```

### 3. Limpieza de Docker Compose

Se removió la línea obsoleta:
```yaml
version: '3.8'  # ❌ REMOVIDO
```

## 📊 Estado Actual

### Contenedores Activos ✅

| Servicio   | Puerto Externo | Puerto Interno | Estado |
|------------|----------------|----------------|--------|
| NGINX      | 8080          | 80             | ✅ UP  |
| MySQL      | 3307          | 3306           | ✅ UP  |
| Redis      | 6380          | 6379           | ✅ UP  |
| PHP-FPM    | -             | 9000           | ✅ UP  |
| Queue      | -             | -              | ✅ UP  |
| Scheduler  | -             | -              | ✅ UP  |

### Base de Datos ✅

```
✅ 10 migraciones ejecutadas correctamente
✅ Seeders ejecutados
✅ Usuarios de prueba creados:
   - admin@controlfinance.com / admin123
   - usuario@controlfinance.com / usuario123
```

## 🚀 Acceso al Proyecto

### Aplicación Web
```
http://localhost:8080
```

### Conexión a MySQL (desde cliente externo)
```
Host: localhost
Port: 3307      ← NUEVO PUERTO
User: control_finance_user
Password: secret
Database: control_finance
```

### Conexión a Redis (desde cliente externo)
```
Host: localhost
Port: 6380      ← NUEVO PUERTO
```

## 🔄 Comandos Útiles

### Reiniciar Contenedores
```bash
docker-compose down
docker-compose up -d
```

### Ver Logs
```bash
docker-compose logs -f
docker-compose logs -f app      # Solo app
docker-compose logs -f mysql    # Solo MySQL
```

### Ejecutar Comandos Artisan
```bash
docker-compose exec app php artisan migrate
docker-compose exec app php artisan db:seed
docker-compose exec app php artisan tinker
```

### Acceder a MySQL CLI
```bash
docker-compose exec mysql mysql -u control_finance_user -psecret control_finance
```

### Acceder a Redis CLI
```bash
docker-compose exec redis redis-cli
```

## ⚙️ Configuración Interna

**IMPORTANTE:** Los servicios dentro de Docker se comunican usando los puertos internos:

```env
# .env - NO cambiar estos valores
DB_HOST=mysql          # Nombre del servicio
DB_PORT=3306           # Puerto INTERNO (no 3307)
REDIS_HOST=redis       # Nombre del servicio
REDIS_PORT=6379        # Puerto INTERNO (no 6380)
```

Los puertos externos (3307, 6380) son **solo para acceso desde tu máquina host**.

## 🐛 Troubleshooting

### Si aún hay conflictos de puerto 8080

Editar `docker-compose.yml`:
```yaml
nginx:
  ports:
    - "8081:80"  # Cambiar 8080 por otro puerto
```

Y actualizar `.env`:
```env
APP_URL=http://localhost:8081
```

### Si MySQL no inicia

```bash
# Ver logs
docker-compose logs mysql

# Reiniciar solo MySQL
docker-compose restart mysql
```

### Si Redis no inicia

```bash
# Ver logs
docker-compose logs redis

# Reiniciar solo Redis
docker-compose restart redis
```

## ✅ Verificación Final

### 1. Verificar contenedores
```bash
docker-compose ps
```
**Resultado esperado:** Todos los servicios en estado "Up"

### 2. Verificar base de datos
```bash
docker-compose exec app php artisan migrate:status
```
**Resultado esperado:** Todas las migraciones con estado "Ran"

### 3. Verificar aplicación
```bash
curl http://localhost:8080
```
**Resultado esperado:** HTML de la página de login/welcome

## 📝 Notas Importantes

1. **Los puertos externos cambiaron:**
   - MySQL: 3306 → **3307**
   - Redis: 6379 → **6380**

2. **Dentro de Docker, los puertos siguen siendo los originales** (3306, 6379)

3. **No es necesario modificar el código de la aplicación** - Laravel se comunica internamente con los puertos correctos

4. **Si tienes MySQL/Redis local**, ahora no habrá conflictos

5. **La aplicación sigue en el puerto 8080** como se especificó originalmente

---

**✅ Proyecto completamente funcional y listo para usar**

.PHONY: help up down restart build logs shell composer artisan migrate fresh seed test clear

# Variables
DOCKER_COMPOSE = docker-compose
EXEC_APP = $(DOCKER_COMPOSE) exec app
EXEC_QUEUE = $(DOCKER_COMPOSE) exec queue

# Colores para output
GREEN  := \033[0;32m
YELLOW := \033[0;33m
NC     := \033[0m

## help: Muestra este mensaje de ayuda
help:
	@echo "$(GREEN)Control Finance - Comandos disponibles:$(NC)"
	@echo ""
	@echo "$(YELLOW)Docker:$(NC)"
	@echo "  make up         - Inicia todos los contenedores"
	@echo "  make down       - Detiene todos los contenedores"
	@echo "  make restart    - Reinicia todos los contenedores"
	@echo "  make build      - Reconstruye las imágenes Docker"
	@echo "  make logs       - Muestra logs de todos los servicios"
	@echo ""
	@echo "$(YELLOW)Aplicación:$(NC)"
	@echo "  make shell      - Abre shell en el contenedor de la app"
	@echo "  make composer   - Instala dependencias de Composer"
	@echo "  make npm        - Instala dependencias de NPM"
	@echo "  make artisan    - Ejecuta un comando artisan (ej: make artisan cmd=migrate)"
	@echo ""
	@echo "$(YELLOW)Base de Datos:$(NC)"
	@echo "  make migrate    - Ejecuta las migraciones"
	@echo "  make fresh      - Refresca la base de datos (DROP + CREATE)"
	@echo "  make seed       - Ejecuta los seeders"
	@echo ""
	@echo "$(YELLOW)Testing:$(NC)"
	@echo "  make test       - Ejecuta los tests"
	@echo ""
	@echo "$(YELLOW)Utilidades:$(NC)"
	@echo "  make clear      - Limpia cache de Laravel"
	@echo ""

## up: Inicia todos los contenedores
up:
	@echo "$(GREEN)Iniciando contenedores...$(NC)"
	$(DOCKER_COMPOSE) up -d
	@echo "$(GREEN)Aplicación disponible en http://localhost:8080$(NC)"

## down: Detiene todos los contenedores
down:
	@echo "$(YELLOW)Deteniendo contenedores...$(NC)"
	$(DOCKER_COMPOSE) down

## restart: Reinicia todos los contenedores
restart: down up

## build: Reconstruye las imágenes Docker
build:
	@echo "$(GREEN)Reconstruyendo imágenes...$(NC)"
	$(DOCKER_COMPOSE) build --no-cache

## logs: Muestra logs de todos los servicios
logs:
	$(DOCKER_COMPOSE) logs -f

## shell: Abre shell en el contenedor de la app
shell:
	$(EXEC_APP) bash

## composer: Instala dependencias de Composer
composer:
	@echo "$(GREEN)Instalando dependencias de Composer...$(NC)"
	$(EXEC_APP) composer install

## npm: Instala dependencias de NPM
npm:
	@echo "$(GREEN)Instalando dependencias de NPM...$(NC)"
	$(EXEC_APP) npm install

## artisan: Ejecuta un comando artisan
artisan:
	$(EXEC_APP) php artisan $(cmd)

## migrate: Ejecuta las migraciones
migrate:
	@echo "$(GREEN)Ejecutando migraciones...$(NC)"
	$(EXEC_APP) php artisan migrate

## fresh: Refresca la base de datos
fresh:
	@echo "$(YELLOW)Refrescando base de datos...$(NC)"
	$(EXEC_APP) php artisan migrate:fresh

## seed: Ejecuta los seeders
seed:
	@echo "$(GREEN)Ejecutando seeders...$(NC)"
	$(EXEC_APP) php artisan db:seed

## test: Ejecuta los tests
test:
	@echo "$(GREEN)Ejecutando tests...$(NC)"
	$(EXEC_APP) php artisan test

## clear: Limpia cache de Laravel
clear:
	@echo "$(GREEN)Limpiando cache...$(NC)"
	$(EXEC_APP) php artisan config:clear
	$(EXEC_APP) php artisan cache:clear
	$(EXEC_APP) php artisan route:clear
	$(EXEC_APP) php artisan view:clear
	@echo "$(GREEN)Cache limpiado exitosamente$(NC)"

## setup: Configuración inicial del proyecto
setup: up composer npm
	@echo "$(GREEN)Generando key de aplicación...$(NC)"
	$(EXEC_APP) php artisan key:generate
	@echo "$(GREEN)Ejecutando migraciones...$(NC)"
	$(EXEC_APP) php artisan migrate
	@echo "$(GREEN)Ejecutando seeders...$(NC)"
	$(EXEC_APP) php artisan db:seed
	@echo "$(GREEN)Setup completado! Aplicación disponible en http://localhost:8080$(NC)"

.PHONY: first-install up down restart build logs precommit-api precommit-nextjs precommit

NETWORK_NAME=app-network

# ---------------------------
# Start backend
# ---------------------------
up-backend:
	docker compose -f docker-compose.yml up -d

# Stop backend
down-backend:
	docker compose -f docker-compose.yml down

# Start frontend
up-frontend:
	docker compose -f ../anybusdk/docker-compose.yml up -d

# Stop frontend
down-frontend:
	docker compose -f ../anybusdk/docker-compose.yml down

# Start both
up: up-backend up-frontend

# Stop both
down: down-backend down-frontend

# Rebuild both
build:
	docker compose -f docker-compose.yml build
	docker compose -f ../anybusdk/docker-compose.yml build

# Tail logs from backend
logs-backend:
	docker compose -f docker-compose.yml logs -f

# Tail logs from frontend
logs-frontend:
	docker compose -f ../anybusdk/docker-compose.yml logs -f

# Tail logs from both concurrently
logs:
	docker compose -f docker-compose.yml logs -f &
	docker compose -f ../anybusdk/docker-compose.yml logs -f &
	wait

# Restart all
restart: down up

# ---------------------------
# First-time install
# ---------------------------
first-install:
	@echo "Checking if Docker network $(NETWORK_NAME) exists..."
	@if ! docker network ls --format '{{.Name}}' | grep -q "^$(NETWORK_NAME)$$"; then \
		echo "Creating network $(NETWORK_NAME)..."; \
		docker network create $(NETWORK_NAME); \
	fi

	@echo "Starting backend..."
	docker compose -f docker-compose.yml up -d --build

	@echo "Waiting for PostgreSQL to be ready..."
	until docker exec postgres pg_isready -U postgres > /dev/null 2>&1; do \
		sleep 2; \
	done

	@echo "Setting up Laravel environment..."
	@if [ ! -f .env ]; then \
		cp .env.example .env; \
	fi
	@if ! docker exec laravel php artisan key:generate --show | grep -q .; then \
		docker exec laravel php artisan key:generate; \
	fi

	@echo "Starting frontend..."
	docker compose -f ../anybusdk/docker-compose.yml up -d --build

# ---------------------------
# Pre-commit hooks
# ---------------------------
# Run pre-commit hooks on staged files for API (Laravel)
precommit-api:
	@echo "Running pre-commit hooks for API (Laravel)..."
	@pre-commit run

# Run pre-commit hooks on staged files for Next.js
precommit-nextjs:
	@echo "Running pre-commit hooks for Next.js..."
	@cd ../anybusdk && pre-commit run

# Run pre-commit hooks on staged files for both repos
precommit: precommit-api precommit-nextjs
	@echo "Pre-commit hooks completed for both repositories."

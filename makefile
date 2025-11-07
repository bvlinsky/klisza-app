up:
	docker compose up -d

down:
	docker compose down

restart:
	docker compose restart

bash:
	docker compose exec admin bash

update:
	docker compose exec admin composer update
	docker compose exec guest bun update

test:
	docker compose exec admin php artisan test
	docker compose exec guest bun test

migrate:
	docker compose exec admin php artisan migrate

types:
	docker compose exec admin php artisan scramble:export
	bunx openapi-typescript ./admin/api.json -o ./guest/app/schema.d.ts
	rm ./admin/api.json

format:
	docker compose exec admin vendor/bin/pint

pre-commit:
	make update
	make types
	make format
	make test
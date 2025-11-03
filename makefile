up:
	docker compose up -d

down:
	docker compose down

bash:
	docker compose exec admin bash

update:
	docker compose exec admin composer update
	docker compose exec guest bun update

migrate:
	docker compose exec admin php artisan migrate

types:
	php ./admin/artisan scramble:export
	bunx openapi-typescript api.json -o ./guest/app/schema.d.ts
	rm api.json

format:
	./admin/vendor/bin/pint

pre-commit:
	make types
	make format
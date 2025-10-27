types:
	php ./admin/artisan scramble:export
	bunx openapi-typescript api.json -o ./guest/app/schema.d.ts
	rm api.json

format:
	./admin/vendor/bin/pint

pre-commit:
	make types
	make format
# Speeda

Speeda (speeda.ca) is a directory of service providers in Canada, in English,
Arabic and French. It is a Laravel 12 / PHP 8.4 / MySQL application with
server-rendered Blade pages.

```bash
composer install && npm install
cp .env.example .env && php artisan key:generate   # then set DB_* in .env
php artisan migrate && php artisan db:seed && php artisan storage:link
composer dev                                       # server + queue + Vite
```

## Documentation

- [docs/README.md](docs/README.md): setup, running, tests, useful commands
- [docs/ARCHITECTURE.md](docs/ARCHITECTURE.md): domains, request pipeline, key flows
- [docs/DEPLOYMENT.md](docs/DEPLOYMENT.md): production layout, deploying, rollback
- [docs/OPERATIONS.md](docs/OPERATIONS.md): queue, scheduler, backups, trusted proxies, logs
- [docs/CHANGELOG.md](docs/CHANGELOG.md): what changed and when

`_Project_Docs/archive/` holds older reports and audits. They are kept for
history and may not match the current code.

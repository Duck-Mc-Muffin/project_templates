# Alpine PHP + Nginx

This project template uses Alpine images for [PHP-fpm (FastCGI)](https://hub.docker.com/_/php/tags?name=fpm-alpine) and [a Nginx slim variant](https://hub.docker.com/_/php/tags?name=fpm-alpine) (based on [this guide](https://betterstack.com/community/guides/scaling-php/php-docker-images/)).
No `composer` or any extensions are installed yet.

All instances of `myproject` need to be replaced with an appropriate working title. Use `grep` to confirm:
```bash
grep --recursive myproject .
```

## Project Structure

Nginx is configured to search for files in the `public` directory. However, the whole parent directory is mounted into the Nginx and PHP container.

The directory is `/var/www/html` in both containers.

## Initialization

> SIDE NOTE:
> I like to keep Docker related files inside a `docker/` subdirectory, wich is why configuration files and commands look weird.

To start the project use the following command:
```bash
docker compose --project-directory docker up -d
```

## Production Deployment

The `-PROD` suffix must be removed from `docker/compose.override.yaml-PROD`!
This will ensure that SSL/TLS certificates will be installed in the Nginx container and the production nginx-configuration file will be mounted.

IMPORTANT: This template was not tested in production yet!

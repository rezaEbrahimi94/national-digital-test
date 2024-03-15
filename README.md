# Quick Setup Guide for Laravel 11 with PHP 8.2

## Prerequisites
- PHP 8.2
- Composer 2.x
- Laravel 11.x

## Setup Steps

1. **Clone the repo**:
   `git clone <repository-url> && cd <project-directory>`

2. **Install PHP dependencies**:
   `composer install`

3. **Copy .env file**:
   `cp .env.example .env`

4. **Generate app key**:
   `php artisan key:generate`


6. **Start the server** (for development):
   `php artisan serve`

## Maintenance

- **Clear Cache**: `php artisan optimize:clear`
- **Update Dependencies**: `composer update && npm update`

## Production

- Configure your web server's document root to point to the project's `public` directory.
- Ensure `.env` is production-ready and secure.


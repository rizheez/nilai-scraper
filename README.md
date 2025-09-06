# Nilai Scraper

A Laravel-based web scraping application for academic data from SIAKAD systems.

## Features

-   **SIAKAD Integration**: Direct connection to SIAKAD academic systems
-   **Queue-Based Scraping**: Background job processing for large datasets
-   **Real-time Progress Tracking**: Monitor scraping progress with live updates
-   **Batch Processing**: Scrape multiple data types simultaneously
-   **Data Management**: View, search, and export scraped data
-   **Responsive Interface**: Modern web interface with Bootstrap

## Queue Implementation

### Background Processing

The application uses Laravel's queue system to handle scraping operations in the background, preventing timeouts and improving user experience.

### Starting Queue Workers

```bash
# Start the queue worker with optimal settings
php artisan scraper:start-queue-worker

# Or use Laravel's built-in command
php artisan queue:work --timeout=3600 --memory=512
```

### Available Scraping Jobs

1. **ScrapeNilaiJob** - Scrapes grade/nilai data
2. **ScrapeMahasiswaJob** - Scrapes student data
3. **ProcessScrapingBatchJob** - Coordinates multiple scraping types

### Real-time Progress

-   Progress tracking via cache-based system
-   Live updates in web interface
-   Detailed job status and error reporting

For detailed queue implementation documentation, see [QUEUE_IMPLEMENTATION.md](QUEUE_IMPLEMENTATION.md).

## Setup

1. **Install Dependencies**

```bash
composer install
npm install
```

2. **Environment Configuration**

```bash
cp .env.example .env
php artisan key:generate
```

3. **Database Setup**

```bash
php artisan migrate
php artisan db:seed
```

4. **Build Assets**

```bash
npm run build
# or for development
npm run dev
```

5. **Start Queue Worker** (Required for scraping)

```bash
php artisan scraper:start-queue-worker
```

6. **Start Development Server**

```bash
php artisan serve
```

## Usage

1. Navigate to the scraping page
2. Login with your SIAKAD credentials
3. Select jurusan and semester
4. Choose scraping type (Nilai, Mahasiswa, or Batch)
5. Monitor real-time progress
6. View results in dashboard

---

<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

-   [Simple, fast routing engine](https://laravel.com/docs/routing).
-   [Powerful dependency injection container](https://laravel.com/docs/container).
-   Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
-   Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
-   Database agnostic [schema migrations](https://laravel.com/docs/migrations).
-   [Robust background job processing](https://laravel.com/docs/queues).
-   [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

You may also try the [Laravel Bootcamp](https://bootcamp.laravel.com), where you will be guided through building a modern Laravel application from scratch.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

-   **[Vehikl](https://vehikl.com)**
-   **[Tighten Co.](https://tighten.co)**
-   **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
-   **[64 Robots](https://64robots.com)**
-   **[Curotec](https://www.curotec.com/services/technologies/laravel)**
-   **[DevSquad](https://devsquad.com/hire-laravel-developers)**
-   **[Redberry](https://redberry.international/laravel-development)**
-   **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

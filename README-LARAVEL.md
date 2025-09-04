# Nilai Scraper - Laravel Migration

This is a Laravel migration of the Go-based academic grade scraper system. The application can scrape student data and grades from SIAKAD (Academic Information System) and provides a web-based user interface for managing the data.

## Features

### 🔧 Core Functionality

-   **SIAKAD Integration**: Login and session management with SIAKAD system
-   **Data Scraping**: Automated scraping of student data, course information, and grades
-   **Database Storage**: Structured storage of all scraped data in database
-   **Export Functionality**: Export data to JSON and CSV formats

### 📊 Data Management

-   **Student Management**: View, search, and filter student data
-   **Course Management**: Manage mata kuliah (course) information
-   **Grade Management**: Track and analyze student grades
-   **Department Management**: Organize data by academic departments (jurusan)

### 🎨 User Interface

-   **Responsive Dashboard**: Modern Bootstrap-based UI
-   **Real-time Scraping**: Monitor scraping progress in real-time
-   **Data Visualization**: Statistics and charts for better insights
-   **Export Tools**: Easy data export with various formats

## Installation

### Prerequisites

-   PHP 8.2 or higher
-   Composer
-   SQLite (default) or MySQL/PostgreSQL
-   Node.js and NPM (for frontend assets)

### Setup Instructions

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

3. **Configure Database**

    - For SQLite (default): The database file will be created automatically
    - For MySQL/PostgreSQL: Update the DB\_\* variables in .env

4. **Configure SIAKAD Connection**
   Edit the `.env` file and set your SIAKAD credentials:

    ```env
    SIAKAD_BASE_URL=https://your-siakad-url.com
    SIAKAD_USERNAME=your_username
    SIAKAD_PASSWORD=your_password
    ```

5. **Run Database Migrations**

    ```bash
    php artisan migrate
    php artisan db:seed
    ```

6. **Build Frontend Assets**

    ```bash
    npm run build
    ```

7. **Start the Application**

    ```bash
    php artisan serve
    ```

    Visit `http://localhost:8000` to access the application.

## Usage

### 1. Dashboard

The main dashboard provides an overview of all data including:

-   Total statistics (students, courses, grades)
-   Recent activities
-   Department statistics
-   Quick action buttons

### 2. Scraping Data

1. Navigate to "Scraping Data" in the sidebar
2. Login with your SIAKAD credentials
3. Select department (jurusan) and semester
4. Choose scraping type:
    - **Scrape Nilai**: Extract grade data
    - **Scrape Mahasiswa**: Extract student data
    - **Scrape Keduanya**: Extract both
5. Monitor the scraping progress

### 3. Data Management

-   **Students**: View, search, and filter student records
-   **Courses**: Manage mata kuliah information
-   **Grades**: Analyze grade data with various filters

### 4. Data Export

Export any dataset to:

-   **JSON**: Structured data format
-   **CSV**: Spreadsheet-compatible format

## Migration from Go Version

This Laravel version replaces the original Go application with the following improvements:

### What's New

-   **Web-based UI**: No more command-line interface
-   **Database Storage**: Persistent data storage instead of file-based
-   **Real-time Monitoring**: Live progress tracking during scraping
-   **Better Data Management**: Advanced filtering and search capabilities
-   **Export Options**: Multiple export formats
-   **Responsive Design**: Works on desktop and mobile devices

### Data Structure

The Laravel version maintains the same data structure as the Go version:

-   **Jurusan** (Departments)
-   **Semester** (Academic terms)
-   **MataKuliah** (Courses)
-   **Mahasiswa** (Students)
-   **Nilai** (Grades)
-   **Bobot** (Grade weights)

### Configuration Migration

If you were using the Go version, update your configuration:

-   Move `BASE_URL` → `SIAKAD_BASE_URL`
-   Move `USER_SIAKAD` → `SIAKAD_USERNAME`
-   Move `PASSWORD_SIAKAD` → `SIAKAD_PASSWORD`

## API Endpoints

The application provides several API endpoints for programmatic access:

-   `POST /scraping/login` - SIAKAD authentication
-   `GET /scraping/semesters` - Get available semesters
-   `POST /scraping/scrape-nilai` - Scrape grade data
-   `POST /scraping/scrape-mahasiswa` - Scrape student data
-   `GET /export/{type}/{format}` - Export data

## Database Schema

The application uses the following main tables:

-   `jurusan` - Academic departments
-   `semester` - Academic terms
-   `mata_kuliah` - Course information
-   `mahasiswa` - Student records
-   `nilai` - Grade records
-   `bobot` - Grade weight configurations

## Security Considerations

-   SIAKAD credentials are stored in session, not in database
-   CSRF protection on all forms
-   Input validation and sanitization
-   Secure HTTP client for SIAKAD communication

## Troubleshooting

### Common Issues

1. **Login Failed**: Check SIAKAD credentials and URL
2. **Database Errors**: Ensure proper database configuration
3. **Scraping Timeout**: Increase timeout in scraper service
4. **Permission Errors**: Check file/directory permissions

### Logs

Check Laravel logs for detailed error information:

```bash
tail -f storage/logs/laravel.log
```

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Run tests: `php artisan test`
5. Submit a pull request

## License

This project is open-source software licensed under the MIT license.

## Support

For questions or issues, please create an issue in the repository or contact the development team.

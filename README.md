**Product Manager**
A professional Laravel application for managing product inventory with full CRUD operations, soft deletes, and bulk management capabilities.
**Overview**
This application provides a complete product management system with the following features:

Product creation with automatic total value calculation (quantity × price)
Real-time AJAX interface with Bootstrap styling
Soft delete functionality with restore capabilities
Bulk operations for managing multiple products
Data export in JSON and XML formats
Comprehensive validation and error handling
Professional architecture using Repository and Service patterns

**Installation & Setup
Prerequisites**

-PHP 8.2 or higher
-Composer
-MySQL/MariaDB
-Node.js and NPM

**Installation Steps**

Clone the repository

-git clone <repository-url>
-cd product-manager

**Install dependencies**

-composer install
-npm install

**Environment setup**

-cp .env.example .env
-php artisan key:generate

**Configure database in .env file**

-envDB_CONNECTION=mysql
-DB_HOST=127.0.0.1
-DB_PORT=3306
-DB_DATABASE=product_manager
-DB_USERNAME=your_username
-DB_PASSWORD=your_password

**Run migrations and seeders**

-php artisan migrate --seed

-Build assets

-npm run build

**Start the development server**

-php artisan serve
-The application will be available at http://localhost:8000
**Usage**

-Add Products: Use the form at the top to add new products
-View Products: All products are displayed in a table ordered by creation date
-Edit Products: Click the edit button on any product row
-Delete Products: Soft delete products individually or in bulk
-Restore Products: Switch to the "Deleted" tab to restore removed products
-Export Data: Export product data in JSON or XML format

**Testing**
-Run the test suite:
-php artisan test
-For coverage report:
-php artisan test --coverage
**Technology Stack**

-Backend: Laravel 11, PHP 8.2
-Frontend: Bootstrap 5, jQuery
-Database: MySQL with optimized indexes
-Testing: PHPUnit with comprehensive test coverage
-Architecture: Repository pattern, Service layer, Dependency injection

Author
Bonface Musila Maingi

Last updated: June 2025

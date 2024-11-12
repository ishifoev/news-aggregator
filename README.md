# News Aggregator API - Laravel Application

This is a Laravel-based News Aggregator API that fetches and aggregates news articles from multiple sources using Docker for development and deployment.

## Prerequisites

- **[Docker](https://www.docker.com/get-started)**: Ensure you have Docker installed on your system. Docker provides the platform to build, run, and manage containerized applications.
- **[Docker Compose](https://docs.docker.com/compose/install/)**: Docker Compose is used to manage multi-container applications. Make sure it's installed and available on your system.


## Setup Instructions

1. **Clone the Repository:**
   ```
   git clone https://github.com/ishifoev/news-aggregator.git
   cd news-aggregator
   cp .env.example .env
   ```


Update the `.env` file with your database and API credentials as needed:

````
DB_CONNECTION=mysql
DB_HOST=db 
DB_PORT=3306
DB_DATABASE=news_aggregator
DB_USERNAME=root
DB_PASSWORD=root_password

NEWS_API_URL=https://newsapi.org/v2/top-headlines/sources
NEWS_API_KEY=ab2cbc3b9552472b96c36c17d0273953
NEWS_API_COUNTRY=us

GUARDIAN_API_URL=https://content.guardianapis.com/search
GUARDIAN_API_KEY=test

````

**Build and Start Docker Containers: Run the following command to build and start your Docker containers:**

`docker-compose up --build -d`

**Install Composer Dependencies:** Access the app container and install dependencies and generate key:

```
docker exec -it laravel_app bash
php artisan key:generate
composer install if need by default it is install from Docker
```

Run Migrations and Seed Database: Run the following commands to set up your database:

```
docker exec -it laravel_app bash
php artisan migrate --seed

```

## API Documentation
The application includes **Swagger/OpenAPI** documentation for the available API routes. Visit the following URL to access the documentation:

## Docker Commands Reference
Start Containers: `docker-compose up -d`
Stop Containers: `docker-compose down`
Rebuild Containers: `docker-compose up --build`
Access App Container Shell: `docker exec -it laravel_app bash`

## Notes
Ensure that the storage and `bootstrap/cache` directories have the correct permissions:

```
docker exec -it laravel_app bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
``` 

The application runs on http://localhost:8080 by default.

## Additional Information

**Composer**: The composer install command ensures that all PHP dependencies are installed.

**Database**: MySQL is used as the database, with the connection configured via Docker.

**Caching** and Performance: The application implements caching strategies for optimized API calls.

**Contributing**

Feel free to fork the project, create a feature branch, and submit a pull request.


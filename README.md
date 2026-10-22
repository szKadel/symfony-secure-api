# Secure Task API

A RESTful API built with **PHP 8.3 and Symfony 7.4**, featuring JWT authentication, user-specific task management, input validation, and automated functional tests.

The project demonstrates backend development practices, including separation of concerns, application handlers, database migrations, and secure access to resources.

## Tech Stack

- PHP 8.3
- Symfony 7.4
- PostgreSQL 16
- Doctrine ORM and Migrations
- LexikJWTAuthenticationBundle
- Symfony Validator
- PHPUnit
- Docker and Docker Compose

## Features

- User registration with password hashing
- JWT-based authentication
- Protected task management endpoints
- Full CRUD operations for tasks
- User-specific task ownership and access control
- Request validation and consistent JSON error responses
- Functional API tests

## Architecture

The application follows a layered structure with thin controllers and dedicated application handlers.

Controllers handle HTTP requests and responses, while application handlers contain the business logic for individual use cases.

Examples of application use cases:

- `CreateTask`
- `GetTasks`
- `GetTask`
- `UpdateTask`
- `DeleteTask`
- `RegisterUser`

This approach keeps responsibilities separated and makes the code easier to test and maintain.

## API Endpoints

### Authentication

| Method | Endpoint | Description |
|---|---|---|
| POST | `/api/register` | Register a new user |
| POST | `/api/login` | Authenticate and obtain a JWT |

### Tasks

All task endpoints require a valid JWT.

| Method | Endpoint | Description |
|---|---|---|
| POST | `/api/tasks` | Create a task |
| GET | `/api/tasks` | List the authenticated user's tasks |
| GET | `/api/tasks/{id}` | Get a specific task |
| PATCH | `/api/tasks/{id}` | Update a task |
| DELETE | `/api/tasks/{id}` | Delete a task |

Users can only access and modify their own tasks.

## Authentication Example

### Register a user

```http
POST /api/register
Content-Type: application/json

{
  "email": "user@example.com",
  "password": "SecurePassword123!"
}
```

### Log in

```http
POST /api/login
Content-Type: application/json

{
  "email": "user@example.com",
  "password": "SecurePassword123!"
}
```

A successful login returns a JWT token.

### Access a protected endpoint

```http
GET /api/tasks
Authorization: Bearer YOUR_JWT_TOKEN
```

## Running the Project

### JWT setup (Windows PowerShell)

Set a local development passphrase:

```powershell
$env:JWT_PASSPHRASE = "dev_jwt_passphrase_change_me"
```

Build the Docker image:

```powershell
docker compose build
```

Generate the JWT key pair:

```powershell
docker compose run --rm -v "${PWD}:/app" api php bin/console lexik:jwt:generate-keypair --no-interaction
```

Start the application:

```powershell
docker compose up -d
```

The generated keys are stored in `config/jwt/` and excluded from version control.

**Note:** Set `JWT_PASSPHRASE` again when opening a new PowerShell session before running Docker Compose commands.

### Prerequisites

- Docker
- Docker Compose

### Start the containers

```bash
docker compose up -d --build
```

### Run database migrations

```bash
docker compose run --rm -v "${PWD}:/app" api php bin/console doctrine:migrations:migrate --no-interaction
```

The API is available at:

`http://localhost:8081`

### JWT configuration

JWT authentication requires an RSA key pair and a configured `JWT_PASSPHRASE` environment variable.

Keep private keys and local secrets outside version control.

## Running Tests

The project includes functional tests covering task operations, validation, authentication requirements, and user access restrictions.

Run the task API test suite:

```bash
docker compose run --rm -v "${PWD}:/app" -e APP_ENV=test api php bin/phpunit tests/Controller/TaskControllerTest.php --no-progress
```

Latest verified result:

**13 tests, 33 assertions — passing.**

Tests use a separate PostgreSQL test database.

## Security

- Passwords are hashed before storage.
- JWT authentication protects task endpoints.
- Tasks are associated with individual users.
- Attempts to access another user's tasks return HTTP `404`.
- Invalid requests return appropriate HTTP error responses.
- Private JWT keys and local environment secrets are excluded from Git.

## Project Purpose

This project was created as a backend portfolio project to demonstrate practical skills in PHP, Symfony, REST API design, authentication, database persistence, automated testing, and Docker-based development.

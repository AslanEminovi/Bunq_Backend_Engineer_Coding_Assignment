# Chat Application Backend - Technical Assessment

<img width="718" height="806" alt="image" src="https://github.com/user-attachments/assets/901e9c9e-a0ab-4894-8370-2eb715be29df" />


This is a comprehensive chat application backend implementation built for the Backend Engineering assessment. The application demonstrates modern PHP development practices, clean architecture principles, and secure coding standards.

## Overview

The application provides a complete RESTful API for a chat system where users can create accounts, form chat groups, and exchange messages. All groups are public, allowing any authenticated user to join and participate in discussions. The system emphasizes security, scalability, and maintainability through careful architectural decisions and implementation patterns.

## Core Requirements Fulfilled

This implementation satisfies all assessment requirements:

- **Chat Groups**: Users can create and join public chat groups
- **Messaging**: Send and retrieve messages within groups
- **User Management**: Token-based user identification and authentication
- **SQLite Database**: Lightweight, file-based data persistence
- **RESTful API**: Complete JSON API following REST principles
- **Slim Framework**: Utilizes Slim 4 for routing and HTTP handling
- **Clean Code**: Well-structured, readable, and maintainable codebase
- **Testing**: Comprehensive unit and integration test coverage

## Architecture Overview

The application follows Clean Architecture principles with clear separation of concerns across multiple layers:

## System Architecture

```mermaid
graph TB
    subgraph "Clients"
        WEB[Web Apps]
        API[API Tools]
    end

    subgraph "API Layer"
        ROUTES[Route Handlers]
        MIDDLEWARE[Middleware]
    end

    subgraph "Business Logic"
        SERVICES[Services]
    end

    subgraph "Data Layer"
        REPOS[Repositories]
        DB[(SQLite Database)]
    end

    WEB --> ROUTES
    API --> ROUTES
    ROUTES --> MIDDLEWARE
    MIDDLEWARE --> SERVICES
    SERVICES --> REPOS
    REPOS --> DB
```

### Architectural Layers

#### 1. Presentation Layer (`src/Presentation/`)

- **Purpose**: Handles HTTP requests and responses
- **Components**: Route handlers, request validation, response formatting
- **Responsibilities**:
  - Parse incoming HTTP requests
  - Validate request data and headers
  - Coordinate with application services
  - Format and return HTTP responses
  - Handle authentication token extraction

#### 2. Application Layer (`src/Application/`)

- **Purpose**: Contains business logic and use cases
- **Components**: Services that orchestrate domain operations
- **Responsibilities**:
  - Implement business rules and workflows
  - Coordinate between presentation and infrastructure layers
  - Handle transaction boundaries
  - Enforce business constraints and validation

#### 3. Domain Layer (`src/Domain/`)

- **Purpose**: Core business entities and rules
- **Components**: Entity classes representing business concepts
- **Responsibilities**:
  - Define core business objects (User, Group, Message)
  - Encapsulate business rules within entities
  - Provide data transformation methods
  - Maintain entity integrity

#### 4. Infrastructure Layer (`src/Infrastructure/`)

- **Purpose**: External system integrations and technical concerns
- **Components**: Database access, middleware, validation utilities
- **Responsibilities**:
  - Database connectivity and operations
  - HTTP middleware for cross-cutting concerns
  - Input validation and sanitization
  - External service integrations

### Design Patterns Implemented

#### Repository Pattern

- Abstracts data access logic from business logic
- Provides consistent interface for data operations
- Enables easy testing through mock implementations
- Located in `src/Infrastructure/Repository/`

#### Service Layer Pattern

- Encapsulates complex business operations
- Provides clear API for use cases
- Handles transaction management
- Located in `src/Application/Service/`

#### Dependency Injection

- Services receive dependencies through constructor injection
- Promotes loose coupling and testability
- Facilitates easy mocking for unit tests

#### Middleware Pattern

- Cross-cutting concerns handled through middleware chain
- JSON response formatting, CORS handling
- Authentication and authorization checks

## Technical Implementation Details

### Database Design

The SQLite database schema is designed for efficiency and data integrity:

#### Users Table

```sql
CREATE TABLE users (
    id TEXT PRIMARY KEY,
    username VARCHAR(255) UNIQUE NOT NULL,
    token TEXT UNIQUE NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
```

#### Groups Table

```sql
CREATE TABLE groups (
    id TEXT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    created_by TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
);
```

#### Group Members Table

```sql
CREATE TABLE group_members (
    id TEXT PRIMARY KEY,
    group_id TEXT NOT NULL,
    user_id TEXT NOT NULL,
    joined_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (group_id) REFERENCES groups(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE(group_id, user_id)
);
```

#### Messages Table

```sql
CREATE TABLE messages (
    id TEXT PRIMARY KEY,
    group_id TEXT NOT NULL,
    user_id TEXT NOT NULL,
    content TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (group_id) REFERENCES groups(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

### Security Implementation

#### Authentication System

- Token-based authentication using 64-character hexadecimal tokens
- Tokens generated using cryptographically secure random bytes
- No session management required, stateless authentication
- Bearer token format for HTTP Authorization headers

#### Input Validation and Sanitization

- Comprehensive validation rules for all user inputs
- HTML entity encoding to prevent XSS attacks
- SQL injection prevention through prepared statements
- Request size limits and content type validation

#### Data Protection

- Foreign key constraints ensure referential integrity
- Proper error handling without information leakage
- CORS configuration for controlled cross-origin access
- Content Security Policy headers

### Performance Considerations

#### Database Optimization

- Primary key indexes on all tables
- Unique constraints for username and token fields
- Foreign key indexes for efficient join operations
- Query optimization for message retrieval with pagination

#### Caching Strategy

- SQLite connection reuse through singleton pattern
- Prepared statement caching
- Response compression support

#### Scalability Design

- Stateless application design
- Horizontal scaling capability
- Database connection pooling ready
- Pagination for large data sets

## Bonus Features and Enhancements

Beyond the core requirements, this implementation includes several advanced features:

### 1. Comprehensive Validation Framework

- Custom validation classes for different input types
- Detailed error messages for client debugging
- Sanitization utilities for XSS prevention
- File upload validation (extensible for future features)

### 2. Advanced Middleware System

- CORS middleware for cross-origin support
- JSON content-type enforcement
- Request/response logging capabilities
- Error handling and exception management

### 3. Professional Testing Suite

- Unit tests for business logic validation
- Integration tests for API endpoint testing
- Test database isolation and cleanup
- Mock objects for external dependencies
- Code coverage reporting capabilities

### 4. API Pagination and Filtering

- Configurable page sizes for message retrieval
- Offset-based pagination for large datasets
- Query parameter validation
- Default limits to prevent resource exhaustion

### 5. Robust Error Handling

- Structured error responses with consistent format
- Appropriate HTTP status codes for different scenarios
- Detailed logging for debugging and monitoring
- Graceful degradation for edge cases

### 6. Developer Experience Features

- Comprehensive API documentation with examples
- Clear project structure and naming conventions
- Extensive inline code comments
- Development server setup with hot reloading
- Automated testing with simple commands

### 7. Production-Ready Features

- Environment-based configuration support
- Health check endpoint for monitoring
- Proper HTTP status code usage
- Content negotiation support
- Security headers implementation

## Technology Stack and Dependencies

### Core Technologies

- **PHP 8.0+**: Modern PHP with strict typing and improved performance
- **Slim Framework 4**: Lightweight, fast micro-framework for HTTP handling
- **SQLite**: File-based database for simple deployment and testing
- **Composer**: Dependency management and autoloading

### Development Tools

- **PHPUnit 10**: Advanced testing framework with modern features
- **UUID Library**: RFC 4122 compliant unique identifier generation
- **PSR Standards**: Compliance with PHP-FIG standards for interoperability

### Quality Assurance

- **Strict Typing**: Enabled throughout the codebase for type safety
- **Error Reporting**: Comprehensive error handling and reporting
- **Code Documentation**: Extensive PHPDoc comments for maintainability

## Installation and Setup

### Prerequisites

- PHP 8.0 or higher with SQLite extension
- Composer for dependency management
- Git for version control

### Installation Steps

1. **Clone the repository**:

   ```bash
   git clone https://github.com/YourUsername/chat_application_assessment.git
   cd chat_application_assessment
   ```

2. **Install dependencies**:

   ```bash
   composer install
   ```

3. **Start the development server**:

   ```bash
   composer start
   ```

   The application will be available at `http://localhost:8080`

4. **Run the test suite**:
   ```bash
   composer test
   ```

### Project Structure

```
chat_application/
├── public/
│   └── index.php                 # Application entry point and bootstrap
├── src/
│   ├── Application/
│   │   ├── App.php              # Main application configuration and setup
│   │   └── Service/             # Business logic and use case implementations
│   │       ├── UserService.php
│   │       ├── GroupService.php
│   │       └── MessageService.php
│   ├── Domain/
│   │   └── Entity/              # Core business entities
│   │       ├── User.php
│   │       ├── Group.php
│   │       └── Message.php
│   ├── Infrastructure/
│   │   ├── Database/
│   │   │   └── DatabaseConnection.php  # Database connectivity and schema
│   │   ├── Middleware/          # HTTP middleware components
│   │   │   ├── CorsMiddleware.php
│   │   │   └── JsonMiddleware.php
│   │   ├── Repository/          # Data access layer implementations
│   │   │   ├── UserRepository.php
│   │   │   ├── GroupRepository.php
│   │   │   └── MessageRepository.php
│   │   └── Validation/          # Input validation and sanitization
│   │       └── Validator.php
│   └── Presentation/
│       └── Routes/              # HTTP route handlers and controllers
│           ├── UserRoutes.php
│           ├── GroupRoutes.php
│           └── MessageRoutes.php
├── tests/
│   ├── Unit/                    # Unit tests for business logic
│   │   └── UserServiceTest.php
│   └── Integration/             # Integration tests for API endpoints
│       └── ApiTest.php
├── database/                    # SQLite database files (auto-created)
├── composer.json               # Project dependencies and scripts
├── phpunit.xml                # PHPUnit configuration
└── README.md                  # This documentation
```

## API Documentation

### Base URL

```
http://localhost:8080/api/v1
```

### Authentication

Most endpoints require authentication via Bearer token in the Authorization header:

```
Authorization: Bearer {user_token}
```

### Endpoints Overview

#### Health Check

```http
GET /health
```

Returns server health status for monitoring purposes.

#### User Management

- `POST /users` - Create new user account
- `GET /users/{id}` - Retrieve user information
- `POST /users/authenticate` - Validate user token

#### Group Management

- `POST /groups` - Create new chat group (requires auth)
- `GET /groups` - List all available groups
- `GET /groups/{id}` - Get specific group details
- `POST /groups/{id}/join` - Join a group (requires auth)
- `GET /groups/{id}/members` - List group members

#### Messaging

- `POST /groups/{id}/messages` - Send message to group (requires auth)
- `GET /groups/{id}/messages` - Retrieve group messages (with pagination)

### Example Usage Workflow

1. **Create a user account**:

   ```bash
   curl -X POST http://localhost:8080/api/v1/users \
     -H "Content-Type: application/json" \
     -d '{"username": "alice"}'
   ```

2. **Create a chat group**:

   ```bash
   curl -X POST http://localhost:8080/api/v1/groups \
     -H "Content-Type: application/json" \
     -H "Authorization: Bearer YOUR_TOKEN" \
     -d '{"name": "General Discussion", "description": "Main chat room"}'
   ```

3. **Send a message**:

   ```bash
   curl -X POST http://localhost:8080/api/v1/groups/GROUP_ID/messages \
     -H "Content-Type: application/json" \
     -H "Authorization: Bearer YOUR_TOKEN" \
     -d '{"content": "Hello everyone!"}'
   ```

4. **Retrieve messages**:
   ```bash
   curl "http://localhost:8080/api/v1/groups/GROUP_ID/messages?limit=20&offset=0"
   ```

## Testing Strategy

The application includes comprehensive testing to ensure reliability and maintainability:

### Unit Tests

- Test business logic in isolation
- Mock external dependencies
- Validate edge cases and error conditions
- Ensure proper data transformation

### Integration Tests

- Test complete API workflows
- Validate HTTP request/response handling
- Test database interactions
- Verify authentication and authorization

### Test Execution

```bash
# Run all tests
composer test

# Run specific test suites
./vendor/bin/phpunit tests/Unit
./vendor/bin/phpunit tests/Integration

# Generate coverage report
./vendor/bin/phpunit --coverage-html coverage/
```

## Production Deployment Considerations

For production deployment, consider the following enhancements:

1. **Web Server Configuration**: Configure Apache or Nginx to serve the application
2. **Database Migration**: Implement database migration system for schema changes
3. **Environment Configuration**: Use environment variables for sensitive configuration
4. **Logging**: Implement structured logging for monitoring and debugging
5. **Caching**: Add Redis or Memcached for session and query caching
6. **Load Balancing**: Configure load balancing for horizontal scaling
7. **SSL/TLS**: Implement HTTPS with proper certificate management
8. **Rate Limiting**: Add rate limiting to prevent abuse
9. **Monitoring**: Implement application performance monitoring
10. **Backup Strategy**: Regular database backups and disaster recovery

## Code Quality and Standards

This implementation adheres to industry best practices:

- **PSR Standards**: Follows PSR-4 autoloading and PSR-12 coding standards
- **SOLID Principles**: Single responsibility, open/closed, interface segregation
- **DRY Principle**: No code duplication, reusable components
- **Documentation**: Comprehensive inline documentation and API docs
- **Error Handling**: Consistent error handling throughout the application
- **Type Safety**: Strict typing enabled for better code reliability

## Assessment Criteria Fulfillment

This implementation specifically addresses the assessment criteria:

### Good and Clean Code

- Clear naming conventions and code organization
- Consistent formatting and documentation
- Proper separation of concerns
- Reusable and maintainable components

### Security

- Input validation and sanitization
- SQL injection prevention
- XSS protection
- Secure authentication system

### Well-Structured

- Clean architecture with defined layers
- Design patterns for common problems
- Testable and extensible codebase
- Clear dependency management

### Scalability

- Stateless application design
- Efficient database queries
- Pagination for large datasets
- Horizontal scaling capabilities

I hope this implementation demonstrates my technical capabilities and attention to detail. I'm excited about the possibility of passing this assessment and joining the bunq engineering team to contribute to building innovative financial technology solutions. Thank you for the opportunity to showcase my backend development skills through this comprehensive chat application.

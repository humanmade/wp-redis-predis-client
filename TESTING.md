# Testing Guide

This document describes how to run tests for the WP Redis Predis Client library.

## Quick Start

### Prerequisites

- PHP 8.2 or higher
- Composer
- Redis server (for full test suite)
- Docker (optional, for WordPress integration tests)

### Running Tests Locally (Standalone)

The simplest way to run tests is using the standalone test suite with mocked WordPress functions:

```bash
# Install dependencies
composer install

# Start Redis (using Docker)
docker run -d --name redis-test -p 6379:6379 redis:latest

# Run tests
vendor/bin/phpunit

# Stop Redis when done
docker stop redis-test && docker rm redis-test
```

**Expected Results:**
- Tests: 22
- Assertions: 24
- Failures: 1 (auth test - expected when Redis has no password)

The standalone tests use lightweight WordPress function mocks defined in `tests/bootstrap.php`, so they run quickly without requiring a full WordPress installation.

## Testing with Docker Compose (RECOMMENDED)

The simplest way to test across multiple PHP versions using Docker:

### Quick Docker Test

```bash
# Start only Redis
docker compose up -d redis

# Run tests with PHP 8.2
docker compose run --rm test-php82 vendor/bin/phpunit

# Run tests with PHP 8.3
docker compose run --rm test-php83 vendor/bin/phpunit

# Run tests with PHP 8.4
docker compose run --rm test-php84 vendor/bin/phpunit

# Stop services
docker compose down
```

**Results:** Tests: 22, Assertions: 24, Failures: 1 (auth test - expected)

This approach:
- Uses lightweight WordPress function mocks (no full WordPress setup needed)
- Tests across multiple PHP versions (8.2, 8.3, 8.4)
- Perfect for CI/CD pipelines and quick verification
- No database setup required

## Notes About WordPress Integration Testing

The `bin/` scripts in this repository (`bin/setup-wp-redis.sh` and `bin/test-wp-redis.sh`) are designed for testing the **wp-redis plugin** with this Predis client, not for running this repository's tests.

If you need to test the actual integration with the wp-redis plugin:
1. Use the `bin/setup-wp-redis.sh` script to set up a WordPress test environment
2. Use the `bin/test-wp-redis.sh` script to run wp-redis plugin tests with this client

For regular development and testing of this library itself, use the standalone or Docker Compose approaches described above.

## Test Suites

### FunctionsTest

Tests the core functionality of the Predis client adapter:

- **test_dependencies**: Verifies Predis\Client class is available
- **test_socket_params**: Tests Unix socket connection parameters
- **test_tls_params**: Tests TLS/SSL connection parameters
- **test_redis_client_connection**: Tests basic Redis connection
- **test_perform_connection**: Tests connection setup
- **test_setup_second_database**: Tests multi-database support
- **test_setup_connection_auth**: Tests authentication (requires Redis with auth)
- **test_append_error_messages**: Tests error message handling
- **test_check_client_dependencies_callback**: Tests dependency check callback
- **test_client_connection_callback**: Tests connection callback
- **test_setup_client_connection_callback**: Tests setup callback

### WPPredisDecoratorTest

Tests the Predis decorator class that provides WordPress-compatible methods:

- **test_is_connected**: Tests connection status
- **test_hexists**: Tests hash field existence check
- **test_exists**: Tests key existence check
- **test_get**: Tests get operation
- **test_hget**: Tests hash get operation
- **test_setex**: Tests set with expiration
- **test_set**: Tests basic set operation
- **test_delete**: Tests key deletion
- **test_hdelete**: Tests hash field deletion
- **test_info**: Tests info command transformation
- **test_close**: Tests connection close

## Configuration

### Custom PHPUnit Configuration

The test suite uses `phpunit.xml` for configuration. You can create `phpunit.xml.dist` for local overrides:

```xml
<?xml version="1.0" encoding="UTF-8"?>
<phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:noNamespaceSchemaLocation="https://schema.phpunit.de/11.5/phpunit.xsd"
         bootstrap="tests/bootstrap.php"
         colors="true">
    <testsuites>
        <testsuite name="default">
            <directory suffix="Test.php">tests</directory>
        </testsuite>
    </testsuites>
</phpunit>
```

## Testing Approaches Compared

### Standalone Tests (Recommended for Quick Development)

**When to use:**
- Local development
- Quick test runs
- CI/CD pipelines
- Testing library code changes

**Pros:**
- Fast execution (< 1 second)
- No external dependencies beyond Redis
- Easy to set up
- Works on any machine with PHP and Redis

**Cons:**
- WordPress functions are mocked
- Doesn't test WordPress integration
- May not catch WordPress-specific issues

**Setup:**
```bash
# Start Redis
docker run -d --name redis-test -p 6379:6379 redis:latest

# Run tests
vendor/bin/phpunit
```

### Docker Compose Tests (Recommended for Integration Testing)

**When to use:**
- Testing WordPress integration
- Verifying compatibility with wp-redis plugin
- Testing before releases
- Debugging WordPress-specific issues

**Pros:**
- Real WordPress environment
- All WordPress functions available
- Tests actual integration
- Reproducible across machines

**Cons:**
- Slower execution (setup time)
- Requires Docker
- More complex setup

**Setup:**
```bash
# Start all services
docker compose up -d

# Run tests
docker compose run --rm test-php82 bash docker/run-tests.sh
```

## Troubleshooting

### Redis Connection Errors

```
Error: Connection refused [tcp://127.0.0.1:6379]
```

**Solution:** Start Redis server:
```bash
docker run -d --name redis-test -p 6379:6379 redis:latest
```

### WordPress Functions Not Found (in old setup)

This has been fixed with WordPress function mocks in `tests/bootstrap.php`. If you still see these errors, ensure you're using the latest test bootstrap.

### Docker Services Not Starting

```bash
# Check service logs
docker compose logs db
docker compose logs redis

# Check service health
docker compose ps

# Restart services
docker compose down -v
docker compose up -d
```

### Tests Timing Out

Increase PHPUnit timeout or check if services are responsive:

```bash
# Test Redis connection
docker compose exec redis redis-cli ping

# Test database connection
docker compose exec db mysqladmin ping -h db -u wordpress -pwordpress
```

### Permission Issues with Docker

On Linux, you may need to fix file permissions after running Docker tests:

```bash
sudo chown -R $USER:$USER .
```

## Known Test Behaviors

### Auth Test Failure

The `test_setup_connection_auth` test expects to fail when Redis doesn't have authentication configured. This is normal behavior when testing against a standard Redis container without auth.

To test with authentication:

```bash
# Start Redis with auth
docker run -d --name redis-test -p 6379:6379 redis:latest redis-server --requirepass "testpassword"

# Update test or skip auth tests
vendor/bin/phpunit --exclude-group auth
```

### Flush Warnings

You may see warnings about `wp_cache_flush()` being requested. These are informational messages from the decorator class and don't indicate test failures.

## CI/CD Integration

### GitHub Actions Example

```yaml
name: Tests

on: [push, pull_request]

jobs:
  test:
    runs-on: ubuntu-latest

    strategy:
      matrix:
        php: ['8.2', '8.3', '8.4']

    services:
      redis:
        image: redis:latest
        ports:
          - 6379:6379

    steps:
      - uses: actions/checkout@v3

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: ${{ matrix.php }}
          extensions: redis

      - name: Install dependencies
        run: composer install

      - name: Run tests
        run: vendor/bin/phpunit
```

## Contributing

When adding new tests:

1. Follow existing test naming conventions (`test_*`)
2. Add appropriate assertions
3. Document complex test scenarios
4. Ensure tests work in both standalone and Docker environments
5. Update this documentation if adding new test suites

## Additional Resources

- [PHPUnit Documentation](https://phpunit.de/documentation.html)
- [WordPress Test Library](https://make.wordpress.org/core/handbook/testing/automated-testing/phpunit/)
- [Predis Documentation](https://github.com/predis/predis)
- [wp-redis Plugin](https://github.com/pantheon-systems/wp-redis)

# Upload Limits Configuration

## Problem
The default PHP configuration has limits that prevent uploading large files:
- `upload_max_filesize`: 8M (default)
- `post_max_size`: 8M (default)

## Solution
We've configured the server to support much larger uploads:
- `upload_max_filesize`: 100M
- `post_max_size`: 100M
- `max_file_uploads`: 100
- `memory_limit`: 512M
- `max_execution_time`: 300 seconds
- `max_input_time`: 300 seconds

## How to Start Server

### Option 1: Use the provided script (Recommended)
```bash
./start-server-large-uploads.sh
```

### Option 2: Manual command
```bash
php -d upload_max_filesize=100M \
    -d post_max_size=100M \
    -d max_file_uploads=100 \
    -d memory_limit=512M \
    -d max_execution_time=300 \
    -d max_input_time=300 \
    -S localhost:8000 -t public
```

### Option 3: Laravel Artisan (Limited)
```bash
php artisan serve
```
**Note**: This method may not respect the increased upload limits.

## Verification
To verify the configuration is working, visit:
http://localhost:8000/php-config.php

You should see the updated limits displayed.

## Important Notes
- These settings only apply to the PHP built-in server
- For production, configure these limits in your web server (Apache/Nginx) configuration
- The `.htaccess` file contains similar settings for Apache
- Laravel's `ini_set()` calls in the code cannot override `upload_max_filesize` and `post_max_size`

# Upload Limits Configuration

## Problem
The default PHP configuration has limits that prevent uploading large files:
- `upload_max_filesize`: 8M (default)
- `post_max_size`: 8M (default)

## Solution
We've configured the server to support much larger uploads:
- `upload_max_filesize`: 100M
- `post_max_size`: 100M
- `max_file_uploads`: 200
- `max_input_vars`: 20000
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
    -d max_file_uploads=200 \
    -d max_input_vars=20000 \
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

## Common Issues

### Multiple Large Uploads Fail
If the second upload fails after a successful first upload with many images, it's likely hitting the `max_input_vars` limit (default: 1000).

**Why?** Each uploaded image creates multiple input variables. With 100 images + form fields, you can easily exceed 1000 variables.

**Solution:** We've increased `max_input_vars` to 20000 to support bulk uploads.

### Production Server Configuration
For production servers using PHP-FPM, edit your PHP configuration:

```bash
# Edit PHP-FPM configuration
sudo nano /etc/php/8.2/fpm/php.ini

# Add/modify these lines:
upload_max_filesize = 100M
post_max_size = 100M
max_file_uploads = 200
max_input_vars = 20000
memory_limit = 512M
max_execution_time = 300
max_input_time = 300

# Restart PHP-FPM
sudo systemctl restart php8.2-fpm
```

For Nginx, ensure `client_max_body_size` is set properly in your nginx.conf:
```nginx
client_max_body_size 0;  # unlimited (or set to e.g., 512M)
```

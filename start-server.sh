#!/bin/bash

# Start PHP server with custom configuration for large file uploads
echo "Starting PHP server with increased upload limits..."
echo "upload_max_filesize: 100M"
echo "post_max_size: 100M"
echo "memory_limit: 512M"

# Start server with custom PHP configuration
php -d upload_max_filesize=100M \
    -d post_max_size=100M \
    -d max_file_uploads=100 \
    -d memory_limit=512M \
    -d max_execution_time=300 \
    -d max_input_time=300 \
    -S localhost:8000 -t public

echo "Server started on http://localhost:8000"
echo "Press Ctrl+C to stop the server"

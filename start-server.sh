#!/bin/bash

# Start PHP server with unlimited upload configuration
echo "Starting PHP server with ABSOLUTELY NO LIMITS..."
echo "upload_max_filesize: UNLIMITED (0)"
echo "post_max_size: UNLIMITED (0)"
echo "memory_limit: UNLIMITED (-1)"
echo "max_file_uploads: 1000"
echo "max_input_vars: 100000"

# Start server with unlimited PHP configuration
php -d upload_max_filesize=0 \
    -d post_max_size=0 \
    -d max_file_uploads=1000 \
    -d memory_limit=-1 \
    -d max_execution_time=0 \
    -d max_input_time=-1 \
    -d max_input_vars=100000 \
    -S localhost:8000 -t public

echo "Server started on http://localhost:8000"
echo "Press Ctrl+C to stop the server"

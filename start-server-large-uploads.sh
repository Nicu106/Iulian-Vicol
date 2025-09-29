#!/bin/bash

echo "Starting PHP server with large file upload support..."
echo "upload_max_filesize: 100M"
echo "post_max_size: 100M"
echo "memory_limit: 512M"
echo "Server will run on http://localhost:8000"

php -d upload_max_filesize=100M \
    -d post_max_size=100M \
    -d max_file_uploads=100 \
    -d memory_limit=512M \
    -d max_execution_time=300 \
    -d max_input_time=300 \
    -S localhost:8000 -t public

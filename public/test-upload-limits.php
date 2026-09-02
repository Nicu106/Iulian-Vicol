<?php
header('Content-Type: text/plain');
echo "=== PHP-FPM Upload Limits Test ==="  . PHP_EOL;
echo "PHP Version: " . phpversion() . PHP_EOL;
echo "max_input_vars: " . ini_get('max_input_vars') . PHP_EOL;
echo "max_file_uploads: " . ini_get('max_file_uploads') . PHP_EOL;
echo "upload_max_filesize: " . ini_get('upload_max_filesize') . PHP_EOL;
echo "post_max_size: " . ini_get('post_max_size') . PHP_EOL;
echo "memory_limit: " . ini_get('memory_limit') . PHP_EOL;
echo PHP_EOL;
if ((int)ini_get('max_input_vars') >= 20000) {
    echo "✓ SUCCESS! Server is properly configured for large uploads." . PHP_EOL;
} else {
    echo "✗ FAILED! max_input_vars is too low." . PHP_EOL;
}
?>

<?php
// PHP Configuration for large file uploads
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>PHP Upload Configuration</title>
    <style>
        body { font-family: monospace; padding: 20px; background: #f5f5f5; }
        .config { background: white; padding: 20px; border-radius: 8px; max-width: 600px; margin: 0 auto; }
        h1 { color: #333; font-size: 18px; margin-bottom: 20px; }
        .setting { padding: 8px; margin: 4px 0; background: #f9f9f9; border-left: 3px solid #4CAF50; }
        .setting.warning { border-left-color: #ff9800; }
        .setting.error { border-left-color: #f44336; }
        .label { font-weight: bold; display: inline-block; width: 200px; }
        .value { color: #0066cc; }
    </style>
</head>
<body>
    <div class="config">
        <h1>📊 Current PHP Upload Configuration</h1>
        <?php
        $settings = [
            'upload_max_filesize' => ['recommended' => '100M', 'critical' => true],
            'post_max_size' => ['recommended' => '100M', 'critical' => true],
            'max_file_uploads' => ['recommended' => '200', 'critical' => true],
            'max_input_vars' => ['recommended' => '20000', 'critical' => true],
            'memory_limit' => ['recommended' => '512M', 'critical' => false],
            'max_execution_time' => ['recommended' => '300', 'critical' => false],
            'max_input_time' => ['recommended' => '300', 'critical' => false],
            'upload_tmp_dir' => ['recommended' => '', 'critical' => false],
        ];

        foreach ($settings as $key => $info) {
            $value = ini_get($key);
            $class = 'setting';

            if ($info['critical']) {
                if ($key === 'max_input_vars' && (int)$value < 5000) {
                    $class .= ' error';
                    $value .= ' ⚠️ TOO LOW! Recommended: ' . $info['recommended'];
                } elseif ($key === 'max_file_uploads' && (int)$value < 100) {
                    $class .= ' error';
                    $value .= ' ⚠️ TOO LOW! Recommended: ' . $info['recommended'];
                }
            }

            if (empty($value) && $key === 'upload_tmp_dir') {
                $value = sys_get_temp_dir() . ' (system default)';
            }

            echo "<div class='{$class}'>";
            echo "<span class='label'>{$key}:</span> ";
            echo "<span class='value'>{$value}</span>";
            echo "</div>";
        }
        ?>

        <div style="margin-top: 20px; padding: 10px; background: #e3f2fd; border-radius: 4px;">
            <strong>💡 Tip:</strong> If you see warnings, restart your server with:<br>
            <code>./start-server-large-uploads.sh</code>
        </div>
    </div>
</body>
</html>

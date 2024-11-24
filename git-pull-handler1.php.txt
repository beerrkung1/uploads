<?php
$payload = json_decode(file_get_contents('php://input'), true);
if ($payload['ref'] === 'refs/heads/main') { // ??????????? branch main
    shell_exec('cd /path/to/your/project && git pull origin main');
}
?>

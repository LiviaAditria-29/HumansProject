<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

if (current_user()) {
    header('Location: /humansproject/admin/dashboard.php');
} else {
    header('Location: /humansproject/login.php');
}
exit;
?>


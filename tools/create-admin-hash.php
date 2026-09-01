<?php

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

fwrite(STDOUT, "Nueva contraseña: ");
$password = trim(fgets(STDIN));
if (strlen($password) < 14) {
    fwrite(STDERR, "La contraseña debe tener al menos 14 caracteres.\n");
    exit(1);
}

fwrite(STDOUT, password_hash($password, PASSWORD_DEFAULT) . PHP_EOL);

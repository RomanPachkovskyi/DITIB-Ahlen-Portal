<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$path = $root . '/config/system-version.json';
$timezone = new DateTimeZone('Europe/Berlin');
$today = new DateTimeImmutable('now', $timezone);

$data = [];

if (is_file($path)) {
    $decoded = json_decode((string) file_get_contents($path), true);
    $data = is_array($decoded) ? $decoded : [];
}

$data['name'] = (string) ($data['name'] ?? 'DiTiB-Registrierungssystem');
$data['major'] = (int) ($data['major'] ?? 1);
$data['minor'] = ((int) ($data['minor'] ?? 0)) + 1;
$data['updated_at'] = $today->format('Y-m-d');

$json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

if ($json === false) {
    fwrite(STDERR, "Could not encode system version metadata.\n");
    exit(1);
}

file_put_contents($path, $json . PHP_EOL);

printf(
    "System version updated: v%d.%03d - Letzte Aktualisierung: %s\n",
    $data['major'],
    $data['minor'],
    $today->format('d.m.Y')
);

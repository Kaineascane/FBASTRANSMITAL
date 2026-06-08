<?php

function loadAppConfig(): array
{
    $root = dirname(__DIR__);
    $httpHost = $_SERVER['HTTP_HOST'] ?? '';
    $isLocal = $httpHost === 'localhost'
        || $httpHost === '127.0.0.1'
        || strpos($httpHost, 'localhost:') === 0
        || strpos($httpHost, '127.0.0.1:') === 0;

    $localPath = $root . '/config.local.php';
    $configPath = $root . '/config.php';

    if ($isLocal && is_file($localPath)) {
        $config = require $localPath;
    } elseif (is_file($configPath)) {
        $config = require $configPath;
    } else {
        return [];
    }

    return is_array($config) ? $config : [];
}

function isLocalEnvironment(): bool
{
    $httpHost = $_SERVER['HTTP_HOST'] ?? '';

    return $httpHost === 'localhost'
        || $httpHost === '127.0.0.1'
        || strpos($httpHost, 'localhost:') === 0
        || strpos($httpHost, '127.0.0.1:') === 0;
}

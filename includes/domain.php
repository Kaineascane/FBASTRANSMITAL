<?php

/**
 * Redirect visitors to the canonical Hostinger (or other) domain when configured.
 * InfinityFree subdomain stays allowed while DNS/SSL is being set up.
 */
function applyDomainSettings(array $config): void
{
    if (PHP_SAPI === 'cli') {
        return;
    }

    $appUrl = trim((string) ($config['app_url'] ?? ''));
    if ($appUrl === '') {
        return;
    }

    $parsed = parse_url($appUrl);
    if (!$parsed || empty($parsed['host'])) {
        return;
    }

    $canonicalHost = strtolower($parsed['host']);
    $canonicalScheme = strtolower($parsed['scheme'] ?? 'https');
    $forceHttps = (bool) ($config['force_https'] ?? true);
    $allowInfinityFree = (bool) ($config['allow_infinityfree_fallback'] ?? true);

    $currentHost = strtolower($_SERVER['HTTP_HOST'] ?? '');
    if ($currentHost === '') {
        return;
    }

    $isHttps = isRequestHttps();

    $allowedHosts = [ $canonicalHost ];
    foreach ($config['allowed_hosts'] ?? [] as $host) {
        $host = strtolower(trim((string) $host));
        if ($host !== '') {
            $allowedHosts[] = $host;
        }
    }
    $allowedHosts = array_unique($allowedHosts);

    if (in_array($currentHost, $allowedHosts, true)) {
        if ($forceHttps && !$isHttps && $canonicalScheme === 'https') {
            redirectTo(buildRequestUrl('https', $currentHost));
        }

        return;
    }

    if ($allowInfinityFree && isInfinityFreeHost($currentHost)) {
        if ($forceHttps && !$isHttps && $canonicalScheme === 'https') {
            redirectTo(buildRequestUrl('https', $currentHost));
        }

        return;
    }

    $target = rtrim($appUrl, '/');
    $uri = $_SERVER['REQUEST_URI'] ?? '/';
    redirectTo($target . $uri);
}

function isRequestHttps(): bool
{
    if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
        return true;
    }
    if (isset($_SERVER['SERVER_PORT']) && (int) $_SERVER['SERVER_PORT'] === 443) {
        return true;
    }

    return strtolower($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https';
}

function isInfinityFreeHost(string $host): bool
{
    $suffixes = [
        'infinityfree.io',
        'infinityfreeapp.com',
        'infinityfree.net',
        'rf.gd',
        '42web.io',
        'epizy.com',
    ];

    foreach ($suffixes as $suffix) {
        if ($host === $suffix) {
            return true;
        }
        $tail = '.' . $suffix;
        if (strlen($host) > strlen($tail) && substr($host, -strlen($tail)) === $tail) {
            return true;
        }
    }

    return false;
}

function buildRequestUrl(string $scheme, string $host): string
{
    $uri = $_SERVER['REQUEST_URI'] ?? '/';

    return $scheme . '://' . $host . $uri;
}

function redirectTo(string $url): void
{
    header('Location: ' . $url, true, 301);
    exit;
}

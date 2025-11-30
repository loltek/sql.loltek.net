<?php
// Simple updater to fetch the latest English Adminer build from GitHub releases.
// Run manually (CLI) when you want to refresh the copy under ./install.

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    exit("This script must be run from the CLI.\n");
}

const API_URL = 'https://api.github.com/repos/vrana/adminer/releases/latest';
const UA = 'AdminerUpdater/1.0 (+sql.loltek.net)';

$installDir = __DIR__ . '/install';
if (!is_dir($installDir) && !mkdir($installDir, 0755, true)) {
    exit("Failed to create install directory: {$installDir}\n");
}

$context = stream_context_create([
    'http' => [
        'method'  => 'GET',
        'header'  => "User-Agent: " . UA . "\r\nAccept: application/vnd.github+json\r\n",
        'timeout' => 20,
    ],
]);

$response = @file_get_contents(API_URL, false, $context);
if ($response === false) {
    exit("Unable to reach GitHub releases API.\n");
}

$release = json_decode($response, true, 512, JSON_THROW_ON_ERROR);
if (empty($release['tag_name'])) {
    exit("Unexpected response from GitHub releases API.\n");
}

$tag = $release['tag_name'];
$version = ltrim($tag, 'v');
$assetName = null;
$downloadUrl = null;

if (!empty($release['assets'])) {
    foreach ($release['assets'] as $asset) {
        if (!empty($asset['name']) && preg_match('/adminer-[0-9]+\.[0-9]+\.[0-9]+-en\.php$/', $asset['name'])) {
            $assetName = $asset['name'];
            $downloadUrl = $asset['browser_download_url'] ?? null;
            break;
        }
    }
}

if ($assetName === null) {
    $assetName = sprintf('adminer-%s-en.php', $version);
    $downloadUrl = sprintf('https://github.com/vrana/adminer/releases/download/%s/%s', $tag, $assetName);
}

$destination = $installDir . '/' . $assetName;
if (is_file($destination)) {
    echo "{$assetName} is already the latest version.\n";
    exit(0);
}

$downloadCtx = stream_context_create([
    'http' => [
        'method'  => 'GET',
        'header'  => "User-Agent: " . UA . "\r\n",
        'timeout' => 60,
    ],
]);

$payload = @file_get_contents($downloadUrl, false, $downloadCtx);
if ($payload === false || $payload === '') {
    exit("Failed to download {$downloadUrl}.\n");
}

if (file_put_contents($destination, $payload) === false) {
    exit("Unable to write {$destination}.\n");
}

chmod($destination, 0644);

echo "Installed {$assetName} to {$destination}.\n";

echo "Cleaning up older Adminer builds...\n";
foreach (glob($installDir . '/adminer-*-en.php') as $file) {
    if ($file !== $destination) {
        @unlink($file);
    }
}

$latestSymlink = $installDir . '/adminer-latest.php';
if (is_link($latestSymlink) || file_exists($latestSymlink)) {
    unlink($latestSymlink);
}

$symlinkTarget = basename($destination);
if (!symlink($symlinkTarget, $latestSymlink)) {
    exit("Installed {$assetName}, but failed to update latest symlink.\n");
}

echo "'{$latestSymlink}' now points to {$symlinkTarget}.\n";

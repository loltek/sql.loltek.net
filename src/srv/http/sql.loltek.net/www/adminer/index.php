<?php

declare(strict_types=1);

define('ADMINER_DIR', __DIR__ . '/install/');

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['auth']) && is_array($_POST['auth'])) {
    $serverValue = trim((string) ($_POST['auth']['server'] ?? ''));
    $lower = strtolower($serverValue);
    $looksLikeSocket = $serverValue === ''
        || $lower === 'localhost'
        || $serverValue[0] === '/'
        || str_contains($serverValue, '.sock')
        || preg_match('~:\s*/~', $serverValue) === 1;

    if ($looksLikeSocket) {
        header('HTTP/1.1 400 Bad Request');
        header('Content-Type: text/html; charset=UTF-8');
        echo <<<HTML
<!DOCTYPE html>
<html lang="en">
<meta charset="utf-8">
<title>Socket connections disabled</title>
<body style="font-family:system-ui,Segoe UI,Helvetica,Arial,sans-serif;margin:2rem;">
    <h1 style="margin-top:0;">Socket connections disabled</h1>
    <p>Adminer may only connect using TCP. Please use <code>sql.loltek.net:3306</code> for MySQL or <code>sql.loltek.net:5432</code> for PostgreSQL.</p>
    <p><a href="/adminer/">Return to Adminer</a></p>
</body>
</html>
HTML;
        exit;
    }
}

ob_start();
register_shutdown_function(function (): void {
    $response = ob_get_clean();
    if ($response === false) {
        return;
    }

    if (str_contains($response, 'adminer-presets')) {
        echo $response;
        return;
    }

    if (str_contains($response, "<input type='checkbox' name='auth[permanent]'")) {
        $nonceAttr = '';
        if (preg_match('/<script[^>]+nonce="([^"]+)"/i', $response, $matches)) {
            $nonce = htmlspecialchars($matches[1], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $nonceAttr = ' nonce="' . $nonce . '"';
        }

        $presetSnippet = <<<HTML
<div id="adminer-presets" style="position:relative;z-index:2;margin:0.5em 0;padding:0.5em 0.75em;border:1px solid #c6d9c6;border-radius:4px;background:#f4fff4;font-size:0.9em;">
    <strong style="display:block;margin-bottom:0.35em;">Quick presets</strong>
    <div style="display:flex;gap:0.4em;flex-wrap:wrap;">
        <button type="button" class="adminer-preset" data-driver="server" data-server="sql.loltek.net:3306" data-username="test" data-password="test" style="flex:0 0 auto;padding:0.3em 0.6em;border:1px solid #9ccc9c;border-radius:3px;background:#eaf6ea;color:#2f5b2f;cursor:pointer;">MySQL sql.loltek.net</button>
        <button type="button" class="adminer-preset" data-driver="pgsql" data-server="sql.loltek.net:5432" data-username="test" data-password="test" style="flex:0 0 auto;padding:0.3em 0.6em;border:1px solid #9fbadf;border-radius:3px;background:#eef3fb;color:#2f4c7f;cursor:pointer;">PostgreSQL sql.loltek.net</button>
    </div>
</div>
<script{$nonceAttr}>
(function() {
    function applyPreset(btn) {
        var driver = document.querySelector('select[name="auth[driver]"]');
        var server = document.querySelector('input[name="auth[server]"]');
        var username = document.querySelector('input[name="auth[username]"]');
        var password = document.querySelector('input[name="auth[password]"]');
        if (!driver || !server || !username || !password) {
            return;
        }
        driver.value = btn.dataset.driver || driver.value;
        server.value = btn.dataset.server || '';
        username.value = btn.dataset.username || '';
        password.value = btn.dataset.password || '';
        username.focus();
    }

    document.querySelectorAll('#adminer-presets .adminer-preset').forEach(function(btn) {
        btn.addEventListener('click', function() {
            applyPreset(btn);
        });
    });

    var presets = document.getElementById('adminer-presets');
    if (presets && document.body && document.body.firstChild !== presets) {
        document.body.insertBefore(presets, document.body.firstChild);
    }
})();
</script>
HTML;

        $response .= PHP_EOL . $presetSnippet;
    }

    echo $response;
});

include ADMINER_DIR . '/adminer-latest.php';

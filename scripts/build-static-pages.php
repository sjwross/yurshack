#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Render public PHP pages to static HTML for GitHub Pages.
 * Usage: php scripts/build-static-pages.php [output-dir]
 */

$root = dirname(__DIR__) . '/yurshack.com';
$out = $argv[1] ?? (dirname(__DIR__) . '/_pages');

putenv('YURSHACK_STATIC=1');
$_SERVER['REQUEST_METHOD'] = 'GET';
$_GET = [];
$_POST = [];

$pages = ['index', 'services', 'order', 'contact', 'terms'];

if (is_dir($out)) {
    // clean previous html only
    foreach (glob($out . '/*.html') ?: [] as $old) {
        unlink($old);
    }
} else {
    mkdir($out, 0755, true);
}

foreach ($pages as $page) {
    $php = $root . '/' . $page . '.php';
    if (!is_readable($php)) {
        fwrite(STDERR, "Missing {$php}\n");
        exit(1);
    }
    $_SERVER['SCRIPT_NAME'] = '/' . $page . '.php';
    $_SERVER['PHP_SELF'] = '/' . $page . '.php';

    ob_start();
    include $php;
    $html = ob_get_clean();

    // Static hosting has no PHP handlers — forms use mailto via site.js.
    $html = preg_replace(
        '/(<form[^>]*\s)action="[^"]*"/',
        '$1action="#"',
        $html
    ) ?? $html;

    $dest = $out . '/' . $page . '.html';
    file_put_contents($dest, $html);
    echo "Wrote {$dest}\n";
}

foreach (['styles.css', 'site.js'] as $asset) {
    copy($root . '/' . $asset, $out . '/' . $asset);
    echo "Copied {$asset}\n";
}

$assetsOut = $out . '/assets';
if (!is_dir($assetsOut)) {
    mkdir($assetsOut, 0755, true);
}
foreach (glob($root . '/assets/*') ?: [] as $file) {
    $name = basename($file);
    copy($file, $assetsOut . '/' . $name);
    echo "Copied assets/{$name}\n";
}

echo "Static site ready in {$out}\n";

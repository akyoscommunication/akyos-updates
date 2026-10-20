<?php

/**
 * php tests/ManualValidationCheck.php
 */
declare(strict_types=1);

$GLOBALS['options'] = [];
function get_option(string $k, $d = false) { return $GLOBALS['options'][$k] ?? $d; }
function update_option(string $k, $v, $a = null): bool { $GLOBALS['options'][$k] = $v; return true; }
function wp_json_encode($v) { return json_encode($v); }
function wp_date(string $f, $t) { return date($f, (int) $t); }
function wp_get_current_user() { return new class { public string $user_login = ''; public function exists(): bool { return false; } }; }

require dirname(__DIR__) . '/src/Service/ManualValidationService.php';

use AkyosUpdates\Service\ManualValidationService as V;

function assert_true(bool $ok, string $msg): void
{
    if (!$ok) {
        fwrite(STDERR, "FAIL: {$msg}\n");
        exit(1);
    }
}

$raw = ['id' => 'seo.indexability', 'status' => 'info', 'severity' => 'neutral', 'message' => 'Aperçu', 'payload' => ['rows' => [['name' => 'page', 'indexable' => true]]]];

$r = V::apply($raw);
assert_true($r['status'] === 'warn' && $r['validation']['validated'] === false, 'non validé => warn');
assert_true(V::apply($r) === $r, 'apply idempotent');

V::set('seo.indexability', $raw, true);
$r = V::apply($r);
assert_true($r['status'] === 'ok' && $r['validation']['validatedBy'] === 'MAW', 'validé => ok');
assert_true($r['validation']['autoStatus'] === 'info', 'statut auto conservé');

$changed = $raw;
$changed['payload']['rows'][0]['indexable'] = false;
$r = V::apply($changed);
assert_true($r['status'] === 'warn' && $r['validation']['stale'] === true, 'config modifiée => caduque');

V::set('seo.indexability', $raw, false);
assert_true(V::apply($raw)['validation']['stale'] === false, 'dévalidation => plus de validation stockée');

assert_true(V::apply(['id' => 'wordpress.version', 'status' => 'fail']) === ['id' => 'wordpress.version', 'status' => 'fail'], 'check non concerné intact');
assert_true(V::summarize([V::apply($raw), ['id' => 'x']])[0]['checkId'] === 'seo.indexability', 'summarize');

echo "OK\n";

<?php

/**
 * php tests/RepoPlanCheck.php
 */
declare(strict_types=1);

require dirname(__DIR__) . '/src/Core/Actions/ActionInterface.php';
require dirname(__DIR__) . '/src/Core/Actions/Plugins/GenerateGitignorePluginExceptionsAction.php';
require dirname(__DIR__) . '/src/Cli/RepoPlan.php';

use AkyosUpdates\Cli\RepoPlan;

function assert_same(mixed $expected, mixed $actual, string $msg): void
{
    if ($expected !== $actual) {
        fwrite(STDERR, "FAIL: {$msg}\n" . var_export($actual, true) . "\n");
        exit(1);
    }
}

$plan = RepoPlan::fromResults([
    ['id' => 'plugins.auth_json_gitignore', 'status' => 'fail'],
    ['id' => 'plugins.auth_json_root', 'status' => 'fail'],
    ['id' => 'plugins.composer_audit', 'status' => 'warn', 'payload' => [
        'installationType' => 'bedrock',
        'missingWpackagistSlugs' => ['contact-form-7'],
        'missingGitignoreSlugs' => ['maison'],
    ]],
], ['contact-form-7' => '6.1']);

assert_same(['auth.json', '!web/app/plugins/maison/'], $plan['gitignore'], 'auth.json + exception Bedrock');
assert_same([['slug' => 'contact-form-7', 'version' => '6.1']], $plan['composerRequire'], 'extension de l’annuaire avec sa version');
assert_same(true, $plan['serverAuthJson'], 'auth.json absent du serveur');
assert_same(['auth.json'], $plan['untrack'], 'auth.json toujours sorti de l’index');

$clean = RepoPlan::fromResults([
    ['id' => 'plugins.auth_json_gitignore', 'status' => 'ok'],
    ['id' => 'plugins.auth_json_root', 'status' => 'ok'],
    ['id' => 'plugins.composer_audit', 'status' => 'ok', 'payload' => ['missingGitignoreSlugs' => ['x']]],
]);
assert_same([], $clean['gitignore'], 'rapport vert : rien à ajouter');
assert_same([], $clean['composerRequire'], 'rapport vert : rien à requérir');
assert_same(false, $clean['serverAuthJson'], 'auth.json présent');

$classic = RepoPlan::fromResults([['id' => 'plugins.auth_json_root', 'status' => 'fail']]);
assert_same(false, $classic['serverAuthJson'], 'jamais d’auth.json dans une racine web classique');

echo "OK\n";

<?php

/**
 * php tests/AutoFixPlannerCheck.php
 */
declare(strict_types=1);

require dirname(__DIR__) . '/src/Cli/AutoFixPlanner.php';

use AkyosUpdates\Cli\AutoFixPlanner;

function assert_true(bool $ok, string $msg): void
{
    if (!$ok) {
        fwrite(STDERR, "FAIL: {$msg}\n");
        exit(1);
    }
}

$plan = AutoFixPlanner::plan([
    [
        'id' => 'wordpress.comments_disabled',
        'title' => 'Commentaires',
        'status' => 'fail',
        'actionable' => true,
        'actionId' => 'wordpress.disable_comments',
        'payload' => [],
    ],
    [
        'id' => 'wordpress.favicon',
        'title' => 'Favicon',
        'status' => 'fail',
        'actionable' => true,
        'actionId' => 'wordpress.set_favicon',
        'payload' => [],
    ],
    [
        'id' => 'wordpress.translations',
        'title' => 'Traductions',
        'status' => 'ok',
        'actionable' => true,
        'actionId' => 'wordpress.update_translations',
        'payload' => [],
    ],
    [
        'id' => 'seo.site_indexing',
        'title' => 'Indexation',
        'status' => 'fail',
        'actionable' => true,
        'actionId' => 'seo.toggle_site_indexing',
        'payload' => ['indexed' => false, 'isProduction' => true],
    ],
    [
        'id' => 'seo.site_indexing_dev',
        'title' => 'Indexation dev',
        'status' => 'fail',
        'actionable' => true,
        'actionId' => 'seo.toggle_site_indexing',
        'payload' => ['indexed' => false, 'isProduction' => false],
    ],
    [
        'id' => 'images.smush_nextgen_config',
        'title' => 'Next-gen',
        'status' => 'warn',
        'actionable' => true,
        'actionId' => 'images.smush_apply_nextgen_config',
        'payload' => ['nextGenEnabled' => false, 'activeFormat' => 'webp'],
    ],
]);

assert_true(count($plan['run']) === 3, '3 jobs auto (comments, indexing prod, nextgen)');
assert_true(count($plan['skip']) === 2, '2 skips (favicon + indexing déjà OK en dev)');

$byCheck = [];
foreach ($plan['run'] as $job) {
    $byCheck[$job['checkId']] = $job;
}
assert_true(($byCheck['seo.site_indexing']['payload']['indexed'] ?? null) === true, 'prod non indexé → indexed true');
assert_true(($byCheck['images.smush_nextgen_config']['payload']['nextGenEnabled'] ?? null) === true, 'nextgen forcé ON');
assert_true(($byCheck['wordpress.comments_disabled']['payload'] ?? null) === [], 'comments payload vide');

$only = AutoFixPlanner::plan([
    [
        'id' => 'wordpress.comments_disabled',
        'title' => 'Commentaires',
        'status' => 'fail',
        'actionable' => true,
        'actionId' => 'wordpress.disable_comments',
        'payload' => [],
    ],
    [
        'id' => 'wordpress.favicon',
        'title' => 'Favicon',
        'status' => 'fail',
        'actionable' => true,
        'actionId' => 'wordpress.set_favicon',
        'payload' => [],
    ],
], ['wordpress.comments_disabled']);
assert_true(count($only['run']) === 1 && $only['run'][0]['checkId'] === 'wordpress.comments_disabled', '--only filtre');
assert_true($only['skip'] === [], '--only n’inclut pas les autres checks');

fwrite(STDOUT, "OK AutoFixPlanner\n");

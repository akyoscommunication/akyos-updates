<?php

namespace AkyosUpdates\Core\Actions;

use AkyosUpdates\Service\HummingbirdService;

final class HummingbirdApplyAdvancedConfigAction implements ActionInterface
{
    public function getId(): string
    {
        return 'performance.hummingbird_apply_advanced_config';
    }

    public function run(array $payload = []): ActionResult
    {
        if (! class_exists('\Hummingbird\Core\Utils')) {
            return ActionResult::failure('Hummingbird non disponible.');
        }

        $advancedModule = \Hummingbird\Core\Utils::get_module('advanced');
        if (! is_object($advancedModule) || ! method_exists($advancedModule, 'get_options') || ! method_exists($advancedModule, 'update_options')) {
            return ActionResult::failure('Module Advanced Hummingbird indisponible.');
        }

        $opts = (array) $advancedModule->get_options();
        $opts['query_string'] = true;
        $opts['emoji'] = true;
        $prefetchRaw = $opts['prefetch'] ?? [];
        $prefetchList = is_array($prefetchRaw) ? $prefetchRaw : [];
        $opts['prefetch'] = HummingbirdService::mergePrefetchUrls($prefetchList);
        $preconnectRaw = $opts['preconnect'] ?? [];
        $preconnectList = is_array($preconnectRaw) ? $preconnectRaw : [];
        $opts['preconnect'] = HummingbirdService::mergePreconnectUrls($preconnectList);
        $advancedModule->update_options($opts);

        $updated = (array) $advancedModule->get_options();
        $queryStringEnabled = ! empty($updated['query_string']);
        $emojiEnabled = ! empty($updated['emoji']);
        $prefetchUpdated = is_array($updated['prefetch'] ?? null) ? $updated['prefetch'] : [];
        $prefetchMissing = HummingbirdService::missingPrefetchUrls($prefetchUpdated);
        $preconnectUpdated = is_array($updated['preconnect'] ?? null) ? $updated['preconnect'] : [];
        $preconnectMissing = HummingbirdService::missingPreconnectUrls($preconnectUpdated);
        $fullyApplied = $queryStringEnabled && $emojiEnabled && $prefetchMissing === [] && $preconnectMissing === [];

        $message = $fullyApplied
            ? 'Réglages Advanced Hummingbird appliqués.'
            : sprintf(
                'Configuration partielle. Reste à corriger : %s.',
                implode(', ', $this->describeGaps($queryStringEnabled, $emojiEnabled, $prefetchMissing, $preconnectMissing))
            );
        $data = [
            'queryStringEnabled' => $queryStringEnabled,
            'emojiEnabled' => $emojiEnabled,
            'prefetchMissing' => $prefetchMissing,
            'prefetchUrls' => array_values(array_map('strval', $prefetchUpdated)),
            'preconnectMissing' => $preconnectMissing,
            'preconnectUrls' => array_values(array_map('strval', $preconnectUpdated)),
        ];
        if ($fullyApplied) {
            return ActionResult::success($message, $data);
        }

        return ActionResult::failure($message, $data);
    }

    private function describeGaps(bool $queryStringEnabled, bool $emojiEnabled, array $prefetchMissing, array $preconnectMissing): array
    {
        $gaps = [];
        if (! $queryStringEnabled) {
            $gaps[] = 'query strings';
        }
        if (! $emojiEnabled) {
            $gaps[] = 'emoji';
        }
        if ($prefetchMissing !== []) {
            $gaps[] = 'prefetch DNS';
        }
        if ($preconnectMissing !== []) {
            $gaps[] = 'preconnect';
        }

        return $gaps;
    }
}

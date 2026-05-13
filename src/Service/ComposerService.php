<?php

namespace AkyosUpdates\Service;

final class ComposerService
{
    /**
     * @param  array<string, mixed>  $require
     * @return array<int, string>
     */
    public static function fromRequire(array $require): array
    {
        $managedByComposer = [];
        foreach (array_keys($require) as $packageName) {
            if (! is_string($packageName) || $packageName === '') {
                continue;
            }

            if (str_starts_with($packageName, 'wpackagist-plugin/')) {
                $managedByComposer[] = str_replace('wpackagist-plugin/', '', $packageName);
                continue;
            }

            if (str_starts_with($packageName, 'wpmudev/')) {
                $managedByComposer[] = str_replace('wpmudev/', '', $packageName);
                continue;
            }

            if ($packageName === 'wpengine/advanced-custom-fields-pro') {
                $managedByComposer[] = 'advanced-custom-fields-pro';
            }

            if (str_contains($packageName, '/')) {
                [, $packageSlug] = explode('/', $packageName, 2);
                if (is_string($packageSlug) && $packageSlug !== '') {
                    $managedByComposer[] = $packageSlug;
                }
            }
        }

        return array_values(array_unique($managedByComposer));
    }
}

<?php

namespace AkyosUpdates\Core\Checks\Images;

use AkyosUpdates\Core\Checks\CheckInterface;
use AkyosUpdates\Core\Checks\CheckResult;
use AkyosUpdates\Core\Context\SiteContext;
use Smush\Core\Stats\Global_Stats;

final class SmushBulkPendingCheck implements CheckInterface
{
    public function getId(): string
    {
        return 'images.smush_bulk_pending';
    }

    public function getCategory(): string
    {
        return 'Images';
    }

    public function getTitle(): string
    {
        return 'Optimisation Smush en attente';
    }
    public function getSuccessMessage(): string
    {
        return 'Optimisation Smush mise à jour.';
    }


    public function run(SiteContext $context): CheckResult
    {
        if (! class_exists('\Smush\Core\Stats\Global_Stats')) {
            return new CheckResult(
                $this->getId(),
                $this->getCategory(),
                $this->getTitle(),
                'warn',
                'warning',
                'Smush détecté mais API stats indisponible.',
                false,
                null,
                []
            );
        }

        $stats = Global_Stats::get();
        $remainingCount = (int) $stats->get_remaining_count();
        $totalCount = (int) $stats->get_total_optimizable_items_count();
        $optimizedCount = max(0, $totalCount - $remainingCount);
        $isOutdated = (bool) $stats->is_outdated();

        if ($remainingCount > 0) {
            return new CheckResult(
                $this->getId(),
                $this->getCategory(),
                $this->getTitle(),
                'warn',
                'warning',
                sprintf('%d image(s) en attente d’optimisation Smush.', $remainingCount),
                true,
                'images.smush_bulk_optimize',
                [
                    'remainingCount' => $remainingCount,
                    'totalCount' => $totalCount,
                    'optimizedCount' => $optimizedCount,
                    'isOutdated' => $isOutdated,
                ]
            );
        }

        return new CheckResult(
            $this->getId(),
            $this->getCategory(),
            $this->getTitle(),
            'ok',
            'success',
            'Toutes les images suivies par Smush sont optimisées.',
            false,
            null,
            [
                'remainingCount' => 0,
                'totalCount' => $totalCount,
                'optimizedCount' => $optimizedCount,
                'isOutdated' => $isOutdated,
            ]
        );
    }
}

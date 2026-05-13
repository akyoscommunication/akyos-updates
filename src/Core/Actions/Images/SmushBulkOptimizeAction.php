<?php

namespace AkyosUpdates\Core\Actions;

use Smush\Core\Optimizer;
use Smush\Core\Stats\Global_Stats;

final class SmushBulkOptimizeAction implements ActionInterface
{
    public function getId(): string
    {
        return 'images.smush_bulk_optimize';
    }

    public function run(array $payload = []): ActionResult
    {
        if (! class_exists('\WP_Smush')) {
            return ActionResult::failure('Smush n’est pas actif.');
        }

        $batchSize = isset($payload['batchSize']) ? (int) $payload['batchSize'] : 20;
        $batchSize = max(1, min($batchSize, 50));

        $stats = Global_Stats::get();
        $beforeRemaining = (int) $stats->get_remaining_count();
        $ids = $this->getPendingAttachmentIds($stats);

        if ($ids === []) {
            return ActionResult::success('Aucune image en attente dans Smush.', [
                'optimizedCount' => 0,
                'processedCount' => 0,
                'remainingCount' => 0,
                'batchSize' => $batchSize,
            ]);
        }

        $optimizer = Optimizer::get_instance();
        $processedCount = 0;
        $optimizedCount = 0;
        $errorCount = 0;

        foreach (array_slice($ids, 0, $batchSize) as $attachmentId) {
            $processedCount++;
            $optimized = (bool) $optimizer->optimize($attachmentId);
            if ($optimized) {
                $optimizedCount++;
            } else {
                $errorCount++;
            }
        }

        $afterRemaining = (int) Global_Stats::get()->get_remaining_count();
        $reduced = max(0, $beforeRemaining - $afterRemaining);
        $effectiveOptimized = max($optimizedCount, $reduced);

        return ActionResult::success(
            $afterRemaining > 0
                ? sprintf(
                    'Optimisation lancée: %d image(s) traitée(s), %d restante(s).',
                    $processedCount,
                    $afterRemaining
                )
                : sprintf('Optimisation Smush terminée: %d image(s) traitée(s).', $processedCount),
            [
            'optimizedCount' => $effectiveOptimized,
            'processedCount' => $processedCount,
            'errorCount' => $errorCount,
            'remainingCount' => $afterRemaining,
            'batchSize' => $batchSize,
            ]
        );
    }

    private function getPendingAttachmentIds(Global_Stats $stats): array
    {
        $ids = array_merge(
            $stats->get_optimize_list()->get_ids(),
            $stats->get_reoptimize_list()->get_ids(),
            $stats->get_error_list()->get_ids()
        );

        $ids = array_values(array_unique(array_map('intval', $ids)));
        return array_values(array_filter($ids, static fn (int $id): bool => $id > 0));
    }
}

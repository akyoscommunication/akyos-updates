<?php

namespace AkyosUpdates\Core\Checks;

final class CheckResult
{
    public function __construct(
        private string $id,
        private string $category,
        private string $title,
        private string $status,
        private string $severity,
        private string $message,
        private bool $actionable = false,
        private ?string $actionId = null,
        private array $payload = [],
        private bool $countsTowardCategoryStats = true,
    ) {
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'category' => $this->category,
            'title' => $this->title,
            'status' => $this->status,
            'severity' => $this->severity,
            'message' => $this->message,
            'actionable' => $this->actionable,
            'actionId' => $this->actionId,
            'payload' => $this->payload,
            'countsTowardCategoryStats' => $this->countsTowardCategoryStats,
        ];
    }
}

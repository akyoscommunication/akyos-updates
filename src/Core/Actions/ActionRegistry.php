<?php

namespace AkyosUpdates\Core\Actions;

final class ActionRegistry
{
    /** @var array<string, ActionInterface> */
    private array $actions = [];

    /** @param ActionInterface[] $actions */
    public function __construct(array $actions)
    {
        foreach ($actions as $action) {
            $this->actions[$action->getId()] = $action;
        }
    }

    public function run(string $actionId, array $payload = []): array
    {
        if (! isset($this->actions[$actionId])) {
            return ActionResult::failure(sprintf('Action inconnue: %s', $actionId), [], 'akyos_updates_unknown_action')
                ->toArray();
        }

        return $this->actions[$actionId]->run($payload)->toArray();
    }
}

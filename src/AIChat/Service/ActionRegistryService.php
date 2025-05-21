<?php

namespace AkyosUpdates\AIChat\Service;

use AkyosUpdates\AIChat\Action\AIActionInterface;
use Psr\Container\ContainerInterface; // For service locator pattern if actions are services

class ActionRegistryService
{
    /** @var AIActionInterface[] */
    private array $actions = [];
    private ContainerInterface $container; // Optional: for loading actions as services

    // You can inject actions directly, or use a service locator/tagging in Symfony
    public function __construct(iterable $taggedActions, ContainerInterface $container)
    {
        $this->container = $container;
        foreach ($taggedActions as $action) {
            if ($action instanceof AIActionInterface) {
                $this->registerAction($action);
            }
        }
        // If actions are not services or not tagged, you might load them from a config array
        // Or manually instantiate and register them here.
    }

    public function registerAction(AIActionInterface $action): void
    {
        if (isset($this->actions[$action->getName()])) {
            // Handle duplicate action name warning or error
            trigger_error("AI Action with name '" . $action->getName() . "' already registered.", E_USER_WARNING);
            return;
        }
        $this->actions[$action->getName()] = $action;
    }

    public function getAction(string $name): ?AIActionInterface
    {
        return $this->actions[$name] ?? null;
        // Alternative if actions are service IDs stored in a config array:
        // if (isset($this->actionServiceIds[$name]) && $this->container->has($this->actionServiceIds[$name])) {
        //     return $this->container->get($this->actionServiceIds[$name]);
        // }
        // return null;
    }

    /**
     * @return AIActionInterface[]
     */
    public function getAllActions(): array
    {
        return $this->actions;
    }

    public function getActionSchemas(): array
    {
        $schemas = [];
        foreach ($this->actions as $action) {
            $schema = $action->getParametersSchema();
            if ($schema) {
                $schemas[] = [
                    'type' => 'function',
                    'function' => [
                        'name' => $action->getName(),
                        'description' => sprintf("Executes the %s action.", $action->getName()), // Generic description, can be improved
                        'parameters' => $schema,
                    ],
                ];
            }
        }
        return $schemas;
    }
} 
<?php

namespace Akyos\Updates\AIChat\Utils;

use Akyos\Updates\AIChat\Service\ActionRegistryService;

class ToolSchemaGenerator
{
    private ActionRegistryService $actionRegistry;

    public function __construct(ActionRegistryService $actionRegistry)
    {
        $this->actionRegistry = $actionRegistry;
    }

    /**
     * Generates the tool schemas compatible with OpenAI API based on registered actions.
     *
     * @return array
     */
    public function generate(): array
    {
        $tools = [];
        $actions = $this->actionRegistry->getAllActions();

        foreach ($actions as $action) {
            $parameterSchema = $action->getParametersSchema();
            if ($parameterSchema === null) { // Action might not have parameters
                $parameterSchema = ['type' => 'object', 'properties' => new \stdClass()];
            }

            // A more detailed description could be sourced from the action itself
            $description = sprintf("Performs the '%s' action. Use this to %s.",
                $action->getName(),
                str_replace('_', ' ', $action->getName())
            );

            $tools[] = [
                'type' => 'function',
                'function' => [
                    'name' => $action->getName(),
                    'description' => $description,
                    'parameters' => $parameterSchema,
                ],
            ];
        }
        return $tools;
    }
} 
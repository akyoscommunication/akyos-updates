<?php

namespace Akyos\Updates\AIChat\Action;

interface AIActionInterface
{
    /**
     * Gets the unique name of the action, used for mapping and tool_calls.
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Executes the action with the given parameters.
     *
     * @param array $parameters Parameters extracted from the AI's response.
     * @return array Result of the action execution, to be sent back or logged.
     */
    public function execute(array $parameters): array;

    /**
     * Optional: Provides a schema for the action's parameters,
     * useful for generating tool definitions for the AI.
     *
     * @return array|null
     */
    public function getParametersSchema(): ?array;
} 
<?php

namespace AkyosUpdates\AIChat\Service;

use AkyosUpdates\AIChat\Action\AIActionInterface;

class IntentRouterService
{
    private OpenAIService $openAIService;
    private ActionRegistryService $actionRegistry;

    public function __construct(OpenAIService $openAIService, ActionRegistryService $actionRegistry)
    {
        $this->openAIService = $openAIService;
        $this->actionRegistry = $actionRegistry;
    }

    public function processUserMessage(string $userMessage, array $conversationHistory = []): array
    {
        $tools = $this->actionRegistry->getActionSchemas();
        $aiApiResponse = $this->openAIService->chat($userMessage, $conversationHistory, $tools);

        // Check for tool_calls first
        if (!empty($aiApiResponse['choices'][0]['message']['tool_calls'])) {
            $toolCall = $aiApiResponse['choices'][0]['message']['tool_calls'][0]; // Assuming one tool call for simplicity
            $functionCall = $toolCall['function'];
            $actionName = $functionCall['name'];
            $arguments = json_decode($functionCall['arguments'], true);

            $action = $this->actionRegistry->getAction($actionName);
            if ($action instanceof AIActionInterface) {
                try {
                    $result = $action->execute($arguments ?? []);
                    // You might want to send this result back to the AI as a new message with role 'tool'
                    // For now, we return it directly to the client
                    return [
                        'type' => 'action_executed',
                        'action' => $actionName,
                        'parameters' => $arguments,
                        'result' => $result
                    ];
                } catch (\Exception $e) {
                    return [
                        'type' => 'error',
                        'message' => sprintf("Error executing action %s: %s", $actionName, $e->getMessage())
                    ];
                }
            } else {
                return [
                    'type' => 'error',
                    'message' => sprintf("Action %s not found or not executable.", $actionName)
                ];
            }
        }

        // Fallback to interpreting intent from text content
        $aiMessageContent = $aiApiResponse['choices'][0]['message']['content'] ?? null;
        if ($aiMessageContent) {
            $decodedContent = json_decode($aiMessageContent, true);
            // Basic validation of the expected JSON structure
            if (isset($decodedContent['intent']) && isset($decodedContent['parameters'])) {
                $intentName = $decodedContent['intent'];
                $parameters = $decodedContent['parameters'];

                $action = $this->actionRegistry->getAction($intentName);
                if ($action instanceof AIActionInterface) {
                    try {
                        $result = $action->execute($parameters ?? []);
                        return [
                            'type' => 'intent_executed',
                            'intent' => $intentName,
                            'parameters' => $parameters,
                            'reasoning' => $decodedContent['reasoning'] ?? 'N/A',
                            'result' => $result
                        ];
                    } catch (\Exception $e) {
                        return [
                            'type' => 'error',
                            'message' => sprintf("Error executing intent %s: %s", $intentName, $e->getMessage())
                        ];
                    }
                } else {
                    // Fallback: AI provided an intent, but we don't have a direct action for it.
                    // You could log this, or try more flexible mapping here.
                    return [
                        'type' => 'intent_not_actionable',
                        'intent' => $intentName,
                        'parameters' => $parameters,
                        'reasoning' => $decodedContent['reasoning'] ?? 'N/A',
                        'message' => sprintf("Intent '%s' was understood but no direct action is configured.", $intentName)
                    ];
                }
            } else {
                 return [
                    'type' => 'unknown_ai_response',
                    'message' => 'AI response content was not in the expected JSON format (intent, parameters).',
                    'raw_content' => $aiMessageContent
                ];
            }
        }

        return [
            'type' => 'error',
            'message' => 'Could not process AI response.',
            'raw_response' => $aiApiResponse
        ];
    }
} 
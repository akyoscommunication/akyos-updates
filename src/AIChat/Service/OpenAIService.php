<?php

namespace Akyos\Updates\AIChat\Service;

// Uses for HttpClientInterface might be needed depending on your setup
// use Symfony\Contracts\HttpClient\HttpClientInterface;

class OpenAIService
{
    private string $apiKey;
    private string $model;
    // private HttpClientInterface $httpClient; // If you use Symfony's HTTP client

    public function __construct(string $apiKey, string $model /*, HttpClientInterface $httpClient */)
    {
        $this->apiKey = $apiKey;
        $this->model = $model;
        // $this->httpClient = $httpClient;
    }

    /**
     * Sends a message and context to the OpenAI API.
     *
     * @param string $userMessage The user's message.
     * @param array $conversationHistory Optional history of the conversation.
     * @param array|null $tools Optional tools (functions) the AI can call.
     * @return array The API response (parsed JSON).
     */
    public function chat(string $userMessage, array $conversationHistory = [], ?array $tools = null): array
    {
        // This is a placeholder for the actual API call logic.
        // You would use $this->httpClient or cURL to make a POST request to OpenAI API.
        // Ensure to include $this->apiKey in the headers (Authorization: Bearer YOUR_API_KEY).
        // The body would contain the model, messages (system prompt, history, user message),
        // and tools if provided.

        // Example system prompt (should be managed by PromptBuilder ideally)
        $systemPrompt = "You are a helpful WordPress assistant. Your goal is to understand user requests related to WordPress administration (technical or editorial) and deduce the corresponding action or function, even if not explicitly listed. Your response format must be JSON containing: action_name, parameters, and reasoning. If you can directly call a provided tool, please do so.";

        $messages = array_merge(
            [['role' => 'system', 'content' => $systemPrompt]],
            $conversationHistory,
            [['role' => 'user', 'content' => $userMessage]]
        );

        $requestBody = [
            'model' => $this->model,
            'messages' => $messages,
            // 'response_format' => ['type' => 'json_object'], // For newer models that support JSON mode
        ];

        if ($tools) {
            $requestBody['tools'] = $tools;
            $requestBody['tool_choice'] = "auto"; // or specific tool
        }

        // Simulate an API call and response
        // In a real scenario, this would be an HTTP request to OpenAI.
        // For now, it returns a mock response indicating an intent to update WordPress.

        // Check if the user specifically asked to update WordPress for a demo of tool_call
        if (stripos($userMessage, 'update wordpress to 6.0') !== false && $tools) {
             return [
                'choices' => [
                    [
                        'message' => [
                            'tool_calls' => [
                                [
                                    'id' => 'call_abc123',
                                    'type' => 'function',
                                    'function' => [
                                        'name' => 'update_wordpress',
                                        'arguments' => json_encode(['version' => '6.0'])
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ];
        }

        return [
            'choices' => [
                [
                    'message' => [
                        'role' => 'assistant',
                        'content' => json_encode([
                            'intent' => 'update_wordpress',
                            'reasoning' => "The user asked to update WordPress. I will try to update it to the latest version by default if no version is specified.",
                            'parameters' => ['version' => 'latest']
                        ])
                    ]
                ]
            ]
        ];
        // Actual implementation would parse the $response->toArray() or similar
    }
} 
<?php

namespace AkyosUpdates\AIChat\Prompt;

class PromptBuilder
{
    private string $baseSystemPrompt;

    public function __construct(string $baseSystemPrompt = '')
    {
        $this->baseSystemPrompt = $baseSystemPrompt ?: $this->getDefaultSystemPrompt();
    }

    private function getDefaultSystemPrompt(): string
    {
        return "You are a specialized WordPress assistant integrated into the Akyos Updates plugin. Your primary function is to accurately understand user requests concerning WordPress site management, updates, content creation, and other administrative tasks. You must determine the correct action and necessary parameters. Respond exclusively in a JSON format containing: 'intent' (a string representing the action to take, e.g., 'update_wordpress'), 'parameters' (an object with action-specific parameters), and 'reasoning' (a brief explanation of your decision-making process). If a user request matches a predefined tool or function you can call, prioritize using that tool.";
    }

    public function getSystemPrompt(array $additionalContext = []): string
    {
        $prompt = $this->baseSystemPrompt;
        if (!empty($additionalContext)) {
            // Example: Append context to the prompt
            // $prompt .= "\n\nAdditional Context:\n";
            // foreach ($additionalContext as $key => $value) {
            //     $prompt .= sprintf("- %s: %s\n", ucfirst($key), $value);
            // }
        }
        return $prompt;
    }

    public function buildMessages(string $userMessage, array $conversationHistory = [], array $systemPromptContext = []): array
    {
        $systemMessage = [
            'role' => 'system',
            'content' => $this->getSystemPrompt($systemPromptContext)
        ];

        $messages = [$systemMessage];
        foreach ($conversationHistory as $entry) {
            // Ensure history entries have 'role' and 'content'
            if (isset($entry['role'], $entry['content'])) {
                $messages[] = $entry;
            }
        }
        $messages[] = ['role' => 'user', 'content' => $userMessage];

        return $messages;
    }
} 
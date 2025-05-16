<?php

namespace Akyos\Updates\Controller;

use Akyos\Updates\AIChat\Service\IntentRouterService;
use Akyos\Updates\AIChat\Service\OpenAIService; // Potentially needed for type hinting or direct use
use Akyos\Updates\AIChat\Prompt\PromptBuilder; // Potentially needed
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route; // Ensure your Kernel/config loads routes from annotations

class AIChatController extends AbstractController
{
    private IntentRouterService $intentRouterService;

    public function __construct(IntentRouterService $intentRouterService)
    {
        $this->intentRouterService = $intentRouterService;
    }

    /**
     * Handles chat submissions to the AI.
     *
     * @Route("/api/ai-chat", name="akyos_updates_ai_chat_submit", methods={"POST"})
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function handleSubmit(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return new JsonResponse([
                    'type' => 'error',
                    'message' => 'Invalid JSON payload: ' . json_last_error_msg()
                ], JsonResponse::HTTP_BAD_REQUEST);
            }

            $userMessage = $data['message'] ?? null;
            $conversationHistory = $data['history'] ?? []; // Optional: pass conversation history

            if (empty($userMessage)) {
                return new JsonResponse([
                    'type' => 'error',
                    'message' => 'Parameter "message" is required.'
                ], JsonResponse::HTTP_BAD_REQUEST);
            }

            // Validate history format if needed
            if (!is_array($conversationHistory)) {
                return new JsonResponse([
                    'type' => 'error',
                    'message' => 'Parameter "history" must be an array.'
                ], JsonResponse::HTTP_BAD_REQUEST);
            }

            $aiResponse = $this->intentRouterService->processUserMessage($userMessage, $conversationHistory);

            return new JsonResponse($aiResponse);

        } catch (\Throwable $e) {
            // Log the error internally
            // error_log("AI Chat Controller Error: " . $e->getMessage() . "\n" . $e->getTraceAsString());

            return new JsonResponse([
                'type' => 'error',
                'message' => 'An unexpected error occurred. Please try again later.',
                // 'detail' => $e->getMessage() // Optionally include detail in dev environment
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
} 
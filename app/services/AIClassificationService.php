<?php

/**
 * AI Classification Service
 *
 * Provides AI-powered violation analysis using the Google Gemini API.
 * All requests go through PHP cURL — no build tools or packages required.
 *
 * Features:
 *   - assessSeverity()      → Suggests severity level from violation description
 *   - classifyCategory()    → Suggests violation category from description
 *   - generateCaseSummary() → Generates a concise admin-facing case summary
 *
 * Design principles:
 *   - Never throws exceptions to callers — returns structured error payload instead.
 *   - API key is read from the GEMINI_API_KEY env variable.
 *   - Student PII (names) is never sent to the external API.
 *   - Timeout is capped at 15 s to avoid blocking the UI.
 *   - callGemini()     → forces responseMimeType: application/json (Phases 1 & 2)
 *   - callGeminiText() → plain-text response (Phase 3 prose output)
 */

class AIClassificationService
{
    private const API_ENDPOINT       = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent';
    private const TIMEOUT_SECONDS     = 15;  // JSON calls (fast, deterministic)
    private const TEXT_TIMEOUT_SECONDS = 30;  // Prose calls (slower — model "thinks" before writing)

    /** Valid severity values that match the violations ENUM */
    private const VALID_SEVERITIES = ['minor', 'moderate', 'major', 'critical'];

    /** Valid category values that match Violation::getCategories() keys */
    private const VALID_CATEGORIES = [
        'Cheating',
        'Attendance Fraud',
        'Misconduct',
        'Plagiarism',
        'Bullying',
        'Vandalism',
        'Drug Violation',
        'Other',
    ];

    // =========================================================================
    // Public API
    // =========================================================================

    /**
     * Assess the severity of a violation description.
     *
     * Returns an array with keys:
     *   - success    (bool)
     *   - severity   (string|null)  one of: minor, moderate, major, critical
     *   - confidence (string|null)  e.g. "high", "medium", "low"
     *   - reasoning  (string|null)  brief explanation from the AI
     *   - error      (string|null)  human-readable error message on failure
     *
     * @param  string $description  Raw violation description text
     * @param  string $type         Violation category (e.g. "Bullying")
     * @return array
     */
    public function assessSeverity(string $description, string $type = ''): array
    {
        $description = trim($description);

        if (strlen($description) < 10) {
            return $this->error('Description is too short to assess.');
        }

        $apiKey = $this->getApiKey();
        if (!$apiKey) {
            return $this->error('AI service is not configured. Contact your administrator.');
        }

        $prompt = $this->buildSeverityPrompt($description, $type);

        $raw = $this->callGemini($apiKey, $prompt);
        if (!$raw['success']) {
            return $this->error($raw['error']);
        }

        return $this->parseSeverityResponse($raw['text']);
    }

    /**
     * Classify the most appropriate category for a violation description.
     *
     * Returns an array with keys:
     *   - success    (bool)
     *   - category   (string|null)  one of the VALID_CATEGORIES values
     *   - confidence (string|null)  "high", "medium", or "low"
     *   - reasoning  (string|null)  brief explanation from the AI
     *   - error      (string|null)  human-readable error message on failure
     *
     * @param  string $description  Raw violation description text
     * @return array
     */
    public function classifyCategory(string $description): array
    {
        $description = trim($description);

        if (strlen($description) < 10) {
            return $this->categoryError('Description is too short to classify.');
        }

        $apiKey = $this->getApiKey();
        if (!$apiKey) {
            return $this->categoryError('AI service is not configured. Contact your administrator.');
        }

        $prompt = $this->buildCategoryPrompt($description);

        $raw = $this->callGemini($apiKey, $prompt);
        if (!$raw['success']) {
            return $this->categoryError($raw['error']);
        }

        return $this->parseCategoryResponse($raw['text']);
    }

    /**
     * Generate a concise professional case summary for admin review.
     *
     * $caseData expected keys:
     *   type, severity, description, status, incident_date,
     *   sanction_notes (optional), action_count (int)
     *
     * Returns an array with keys:
     *   - success  (bool)
     *   - summary  (string|null)  2–4 sentence plain text
     *   - error    (string|null)
     *
     * Student PII is never included in the payload sent to the API.
     *
     * @param  array $caseData
     * @return array
     */
    public function generateCaseSummary(array $caseData): array
    {
        $description = trim($caseData['description'] ?? '');
        if (strlen($description) < 10) {
            return $this->summaryError('Case description is too short to summarise.');
        }

        $apiKey = $this->getApiKey();
        if (!$apiKey) {
            return $this->summaryError('AI service is not configured. Contact your administrator.');
        }

        $prompt = $this->buildSummaryPrompt($caseData);

        $raw = $this->callGeminiText($apiKey, $prompt);
        if (!$raw['success']) {
            return $this->summaryError($raw['error']);
        }

        return $this->parseSummaryResponse($raw['text']);
    }

    // =========================================================================
    // Prompt Engineering
    // =========================================================================

    /**
     * Build the severity assessment prompt.
     * Student names/PII are already not included since only the description
     * and category are passed.
     */
    private function buildSeverityPrompt(string $description, string $type): string
    {
        $typeContext = $type ? "Violation category: {$type}\n" : '';

        return <<<PROMPT
You are an academic disciplinary compliance assistant. Assess the severity of this student violation report.

{$typeContext}Violation description:
"{$description}"

Classify the severity using ONLY one of these levels:
- minor: Minor rule infractions with minimal impact (first-time petty offense, dress code, minor tardiness)
- moderate: Repeated minor offenses or moderate policy violations (repeated lateness, minor cheating attempt, verbal conflict)
- major: Serious violations requiring formal disciplinary action (confirmed cheating, aggression, bullying, significant misconduct)
- critical: Severe violations posing safety risk or major integrity breach (violence, drug use, dangerous behavior, severe fraud)

Return a JSON object with exactly three fields:
- severity: one of minor, moderate, major, or critical
- confidence: one of high, medium, or low
- reasoning: a single sentence explanation, maximum 150 characters
PROMPT;
    }

    /**
     * Build the category classification prompt.
     */
    private function buildCategoryPrompt(string $description): string
    {
        $categories = implode("\n", array_map(
            fn($c) => "- {$c}",
            self::VALID_CATEGORIES
        ));

        return <<<PROMPT
You are an academic disciplinary compliance assistant. Classify this student violation report into the most appropriate category.

Violation description:
"{$description}"

Choose ONLY one category from this exact list:
{$categories}

Return a JSON object with exactly three fields:
- category: one of the exact category strings listed above
- confidence: one of high, medium, or low
- reasoning: a single sentence explanation, maximum 150 characters
PROMPT;
    }

    /**
     * Build the case summary prompt for admin review.
     * No student PII is included.
     */
    private function buildSummaryPrompt(array $data): string
    {
        $type        = $data['type']        ?? 'Unknown';
        $severity    = $data['severity']    ?? 'unknown';
        $status      = $data['status']      ?? 'unknown';
        $description = trim($data['description'] ?? '');
        $incidentDate = $data['incident_date'] ?? 'unknown date';
        $actionCount = (int) ($data['action_count'] ?? 0);
        $hasSanction = !empty(trim($data['sanction_notes'] ?? ''));

        $sanctionLine = $hasSanction
            ? "A sanction has been assigned to this case."
            : "No sanction has been recorded yet.";

        $actionsLine = $actionCount > 0
            ? "The case has {$actionCount} recorded action(s) in its history."
            : "No case actions have been recorded yet.";

        return <<<PROMPT
You are an academic compliance officer writing a formal internal case summary.
Do NOT include student names or personally identifiable information.
Write in a professional, third-person, factual tone.

Case details:
- Violation category: {$type}
- Severity level: {$severity}
- Current status: {$status}
- Incident date: {$incidentDate}
- {$sanctionLine}
- {$actionsLine}

Description on file:
"{$description}"

Write a concise case summary in 2 to 4 sentences. Summarise the nature of the violation, its severity, current status, and any notable context. Do not use bullet points, headers, or markdown formatting — plain paragraph text only.
PROMPT;
    }

    // =========================================================================
    // Gemini API Call
    // =========================================================================

    /**
     * Make a request to the Gemini generateContent endpoint.
     *
     * @param  string $apiKey
     * @param  string $prompt
     * @return array  ['success' => bool, 'text' => string|null, 'error' => string|null]
     */
    private function callGemini(string $apiKey, string $prompt): array
    {
        if (!function_exists('curl_init')) {
            return ['success' => false, 'text' => null, 'error' => 'cURL extension is not available on this server.'];
        }

        $url     = self::API_ENDPOINT . '?key=' . urlencode($apiKey);
        $payload = json_encode([
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature'      => 0.2,            // Low temp → deterministic, factual
                'maxOutputTokens'  => 8192,           // Very generous — actual JSON output is ~30 tokens;
                                                      // JSON-constrained mode can over-count, so give plenty of room
                'topP'             => 0.8,
                'responseMimeType' => 'application/json', // Force pure JSON, no markdown fences
            ],
        ]);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
            CURLOPT_TIMEOUT        => self::TIMEOUT_SECONDS,
            CURLOPT_CONNECTTIMEOUT => 8,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr  = curl_error($ch);
        curl_close($ch);

        if ($curlErr) {
            error_log('ACVMS AIClassificationService::callGemini — cURL error: ' . $curlErr);
            return ['success' => false, 'text' => null, 'error' => 'Network error communicating with AI service.'];
        }

        if ($httpCode === 400) {
            error_log('ACVMS AIClassificationService::callGemini — 400 Bad Request: ' . $response);
            return ['success' => false, 'text' => null, 'error' => 'Invalid request to AI service. Check your API key.'];
        }

        if ($httpCode === 404) {
            error_log('ACVMS AIClassificationService::callGemini — 404 Model Not Found: ' . $response);
            return ['success' => false, 'text' => null, 'error' => 'AI model not found. Please contact your administrator.'];
        }

        if ($httpCode === 429) {
            return ['success' => false, 'text' => null, 'error' => 'AI service rate limit reached. Please try again in a moment.'];
        }

        if ($httpCode !== 200) {
            error_log('ACVMS AIClassificationService::callGemini — HTTP ' . $httpCode . ': ' . $response);
            return ['success' => false, 'text' => null, 'error' => 'AI service returned an unexpected error (HTTP ' . $httpCode . ').'];
        }

        $data = json_decode($response, true);

        // Check for truncation — but attempt recovery first.
        // JSON-constrained mode sometimes reports MAX_TOKENS even when the output is complete.
        // If there is valid text content, pass it through; the parser will catch broken JSON.
        $finishReason = $data['candidates'][0]['finishReason'] ?? 'STOP';
        $text         = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;

        if ($finishReason === 'MAX_TOKENS') {
            if ($text !== null && trim($text) !== '') {
                // Content present — try to use it; parser will reject if it's truly broken.
                error_log('ACVMS AIClassificationService::callGemini — finishReason=MAX_TOKENS but content present; attempting parse.');
            } else {
                // No content at all — genuinely truncated.
                error_log('ACVMS AIClassificationService::callGemini — Response truly truncated (MAX_TOKENS, no content).');
                return ['success' => false, 'text' => null, 'error' => 'AI response was truncated. Please try again.'];
            }
        }

        if ($text === null) {
            error_log('ACVMS AIClassificationService::callGemini — Unexpected response structure: ' . $response);
            return ['success' => false, 'text' => null, 'error' => 'AI service returned an unreadable response.'];
        }

        return ['success' => true, 'text' => trim($text), 'error' => null];
    }


    /**
     * Like callGemini() but requests plain text output (no JSON MIME type).
     * Used for the case summary which returns prose, not structured JSON.
     *
     * @param  string $apiKey
     * @param  string $prompt
     * @return array  ['success' => bool, 'text' => string|null, 'error' => string|null]
     */
    private function callGeminiText(string $apiKey, string $prompt): array
    {
        if (!function_exists('curl_init')) {
            return ['success' => false, 'text' => null, 'error' => 'cURL extension is not available on this server.'];
        }

        $url     = self::API_ENDPOINT . '?key=' . urlencode($apiKey);
        $payload = json_encode([
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature'     => 0.4,   // Slightly higher for natural prose
                'maxOutputTokens' => 8192,  // Generous ceiling — prose summaries vary in length;
                                            // better to over-provision than truncate mid-sentence
                'topP'            => 0.9,
                // No responseMimeType — plain text output
            ],
        ]);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
            CURLOPT_TIMEOUT        => self::TEXT_TIMEOUT_SECONDS, // 30 s — prose is slower than JSON
            CURLOPT_CONNECTTIMEOUT => 8,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr  = curl_error($ch);
        curl_close($ch);

        if ($curlErr) {
            error_log('ACVMS AIClassificationService::callGeminiText — cURL error: ' . $curlErr);
            return ['success' => false, 'text' => null, 'error' => 'Network error communicating with AI service.'];
        }

        if ($httpCode === 429) {
            return ['success' => false, 'text' => null, 'error' => 'AI service rate limit reached. Please try again in a moment.'];
        }

        if ($httpCode !== 200) {
            error_log('ACVMS AIClassificationService::callGeminiText — HTTP ' . $httpCode . ': ' . $response);
            return ['success' => false, 'text' => null, 'error' => 'AI service returned an unexpected error (HTTP ' . $httpCode . ').'];
        }

        $data = json_decode($response, true);

        // MAX_TOKENS resilience: if content is present despite the flag, try to use it.
        $finishReason = $data['candidates'][0]['finishReason'] ?? 'STOP';
        $text         = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;

        if ($finishReason === 'MAX_TOKENS') {
            if ($text !== null && trim($text) !== '') {
                error_log('ACVMS AIClassificationService::callGeminiText — finishReason=MAX_TOKENS but content present; attempting use.');
                // Fall through — parseSummaryResponse will reject if it’s genuinely too short
            } else {
                error_log('ACVMS AIClassificationService::callGeminiText — Response truly truncated (MAX_TOKENS, no content).');
                return ['success' => false, 'text' => null, 'error' => 'AI summary was truncated. Please try again.'];
            }
        }

        if ($text === null) {
            error_log('ACVMS AIClassificationService::callGeminiText — Unexpected structure: ' . $response);
            return ['success' => false, 'text' => null, 'error' => 'AI service returned an unreadable response.'];
        }

        return ['success' => true, 'text' => trim($text), 'error' => null];
    }

    // =========================================================================
    // Response Parsing
    // =========================================================================

    /**
     * Parse the Gemini text output into a structured severity result.
     */
    private function parseSeverityResponse(string $text): array
    {
        // Strip markdown fences if present (```json ... ```)
        $text = preg_replace('/^```(?:json)?\s*/i', '', $text);
        $text = preg_replace('/\s*```$/', '', $text);
        $text = trim($text);

        $parsed = json_decode($text, true);

        if (!is_array($parsed)) {
            error_log('ACVMS AIClassificationService::parseSeverityResponse — JSON parse failed: ' . $text);
            return $this->error('AI response could not be understood. Please try again.');
        }

        $severity  = strtolower(trim($parsed['severity']  ?? ''));
        $confidence = strtolower(trim($parsed['confidence'] ?? 'medium'));
        $reasoning  = trim($parsed['reasoning'] ?? '');

        if (!in_array($severity, self::VALID_SEVERITIES, true)) {
            error_log('ACVMS AIClassificationService::parseSeverityResponse — Invalid severity: ' . $severity);
            return $this->error('AI returned an unrecognised severity level.');
        }

        return [
            'success'    => true,
            'severity'   => $severity,
            'confidence' => $confidence,
            'reasoning'  => $reasoning ?: 'No additional reasoning provided.',
            'error'      => null,
        ];
    }

    /**
     * Parse the Gemini text output into a structured category result.
     */
    private function parseCategoryResponse(string $text): array
    {
        // Strip markdown fences if present
        $text = preg_replace('/^```(?:json)?\s*/i', '', $text);
        $text = preg_replace('/\s*```$/', '', $text);
        $text = trim($text);

        $parsed = json_decode($text, true);

        if (!is_array($parsed)) {
            error_log('ACVMS AIClassificationService::parseCategoryResponse — JSON parse failed: ' . $text);
            return $this->categoryError('AI response could not be understood. Please try again.');
        }

        $category  = trim($parsed['category']   ?? '');
        $confidence = strtolower(trim($parsed['confidence'] ?? 'medium'));
        $reasoning  = trim($parsed['reasoning']  ?? '');

        // Case-insensitive match against valid categories
        $matched = null;
        foreach (self::VALID_CATEGORIES as $valid) {
            if (strcasecmp($category, $valid) === 0) {
                $matched = $valid;
                break;
            }
        }

        if ($matched === null) {
            error_log('ACVMS AIClassificationService::parseCategoryResponse — Invalid category: ' . $category);
            return $this->categoryError('AI returned an unrecognised category.');
        }

        return [
            'success'    => true,
            'category'   => $matched,
            'confidence' => $confidence,
            'reasoning'  => $reasoning ?: 'No additional reasoning provided.',
            'error'      => null,
        ];
    }

    /**
     * Parse the plain-text case summary response.
     */
    private function parseSummaryResponse(string $text): array
    {
        // Strip any accidental markdown
        $text = preg_replace('/^#+\s*/m', '', $text);
        $text = preg_replace('/\*+/', '', $text);
        $text = trim($text);

        if (strlen($text) < 20) {
            error_log('ACVMS AIClassificationService::parseSummaryResponse — Response too short: ' . $text);
            return $this->summaryError('AI returned an unusably short summary. Please try again.');
        }

        return [
            'success' => true,
            'summary' => $text,
            'error'   => null,
        ];
    }

    // =========================================================================
    // Helpers
    // =========================================================================

    private function getApiKey(): string
    {
        return env('GEMINI_API_KEY', '');
    }

    /** Error payload for severity assessment */
    private function error(string $message): array
    {
        return [
            'success'    => false,
            'severity'   => null,
            'confidence' => null,
            'reasoning'  => null,
            'error'      => $message,
        ];
    }

    /** Error payload for category classification */
    private function categoryError(string $message): array
    {
        return [
            'success'    => false,
            'category'   => null,
            'confidence' => null,
            'reasoning'  => null,
            'error'      => $message,
        ];
    }

    /** Error payload for case summary */
    private function summaryError(string $message): array
    {
        return [
            'success' => false,
            'summary' => null,
            'error'   => $message,
        ];
    }
}

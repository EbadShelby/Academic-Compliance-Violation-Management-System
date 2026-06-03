<?php

/**
 * AI Controller
 *
 * Handles AJAX requests for AI-powered violation analysis features.
 * All endpoints return JSON and require an active authenticated session.
 *
 * Routes:
 *   POST /ai/assess-severity    → assess()           (teacher|admin)
 *   POST /ai/classify-category  → classifyCategory() (teacher|admin)
 *   POST /ai/summarize-case     → summarizeCase()    (admin only)
 *
 * All responses follow this envelope:
 *   { "success": bool, "data": {...}|null, "error": string|null }
 */

class AIController extends Controller
{
    // =========================================================================
    // POST /ai/assess-severity
    // =========================================================================

    /**
     * Assess the severity of a violation based on its description.
     *
     * Expected JSON body (or POST fields):
     *   - description (string, required, min 10 chars)
     *   - type        (string, optional) — violation category
     *
     * Returns JSON:
     *   {
     *     "success": true,
     *     "data": {
     *       "severity":   "major",
     *       "confidence": "high",
     *       "reasoning":  "..."
     *     }
     *   }
     */
    public function assess(): void
    {
        // ── Output buffer: MUST be first. ─────────────────────────────────────
        // APP_ENV=development turns on display_errors. Any PHP notice/warning
        // would print HTML and corrupt the JSON response. We capture everything
        // and discard it before sending our clean JSON envelope.
        ob_start();

        try {
            // Must be logged in as teacher or admin
            authorize(['teacher', 'admin', 'registrar']);

            // Parse body — support both JSON body and regular POST fields
            $input = $this->parseInput();

            $description = trim($input['description'] ?? '');
            $type        = trim($input['type']        ?? '');

            // Basic server-side guard
            if (strlen($description) < 10) {
                $this->sendJson(['success' => false, 'data' => null, 'error' => 'Description must be at least 10 characters.']);
            }

            if (strlen($description) > 5000) {
                $this->sendJson(['success' => false, 'data' => null, 'error' => 'Description exceeds maximum length.']);
            }

            // Load and call the AI service
            if (!class_exists('AIClassificationService', false)) {
                require_once BASE_PATH . '/app/services/AIClassificationService.php';
            }

            $ai     = new AIClassificationService();
            $result = $ai->assessSeverity($description, $type);

            if (!$result['success']) {
                $this->sendJson([
                    'success' => false,
                    'data'    => null,
                    'error'   => $result['error'],
                ]);
            }

            $this->sendJson([
                'success' => true,
                'data'    => [
                    'severity'   => $result['severity'],
                    'confidence' => $result['confidence'],
                    'reasoning'  => $result['reasoning'],
                ],
                'error' => null,
            ]);

        } catch (Throwable $e) {
            error_log('ACVMS AIController::assess — Uncaught: ' . $e->getMessage());
            $this->sendJson([
                'success' => false,
                'data'    => null,
                'error'   => 'An unexpected server error occurred. Please try again.',
            ], 500);
        }
    }
    // =========================================================================
    // POST /ai/classify-category
    // =========================================================================

    /**
     * Suggest a violation category based on the description.
     *
     * Expected JSON body (or POST fields):
     *   - description (string, required, min 10 chars)
     *
     * Returns JSON:
     *   {
     *     "success": true,
     *     "data": {
     *       "category":   "Bullying",
     *       "confidence": "high",
     *       "reasoning":  "..."
     *     }
     *   }
     */
    public function classifyCategory(): void
    {
        ob_start();

        try {
            authorize(['teacher', 'admin', 'registrar']);

            $input       = $this->parseInput();
            $description = trim($input['description'] ?? '');

            if (strlen($description) < 10) {
                $this->sendJson(['success' => false, 'data' => null, 'error' => 'Description must be at least 10 characters.']);
            }

            if (strlen($description) > 5000) {
                $this->sendJson(['success' => false, 'data' => null, 'error' => 'Description exceeds maximum length.']);
            }

            if (!class_exists('AIClassificationService', false)) {
                require_once BASE_PATH . '/app/services/AIClassificationService.php';
            }

            $ai     = new AIClassificationService();
            $result = $ai->classifyCategory($description);

            if (!$result['success']) {
                $this->sendJson(['success' => false, 'data' => null, 'error' => $result['error']]);
            }

            $this->sendJson([
                'success' => true,
                'data'    => [
                    'category'   => $result['category'],
                    'confidence' => $result['confidence'],
                    'reasoning'  => $result['reasoning'],
                ],
                'error' => null,
            ]);

        } catch (Throwable $e) {
            error_log('ACVMS AIController::classifyCategory — Uncaught: ' . $e->getMessage());
            $this->sendJson(['success' => false, 'data' => null, 'error' => 'An unexpected server error occurred. Please try again.'], 500);
        }
    }

    // =========================================================================
    // POST /ai/summarize-case
    // =========================================================================

    /**
     * Generate a concise AI case summary for admin review.
     *
     * Expected JSON body (or POST fields):
     *   - description   (string, required)
     *   - type          (string, required)
     *   - severity      (string, optional)
     *   - status        (string, optional)
     *   - incident_date (string, optional)
     *   - sanction_notes(string, optional)
     *   - action_count  (int,    optional)
     *
     * Returns JSON:
     *   {
     *     "success": true,
     *     "data": { "summary": "..." }
     *   }
     */
    public function summarizeCase(): void
    {
        ob_start();

        try {
            // Case summary is admin-only — teachers don't access the review page
            authorize(['admin', 'registrar']);

            $input       = $this->parseInput();
            $description = trim($input['description'] ?? '');
            $type        = trim($input['type']        ?? '');

            if (strlen($description) < 10) {
                $this->sendJson(['success' => false, 'data' => null, 'error' => 'Description is required and must be at least 10 characters.']);
            }

            if (!$type) {
                $this->sendJson(['success' => false, 'data' => null, 'error' => 'Violation type is required for case summary.']);
            }

            if (!class_exists('AIClassificationService', false)) {
                require_once BASE_PATH . '/app/services/AIClassificationService.php';
            }

            $ai     = new AIClassificationService();
            $result = $ai->generateCaseSummary([
                'type'          => $type,
                'severity'      => $input['severity']       ?? '',
                'description'   => $description,
                'status'        => $input['status']         ?? '',
                'incident_date' => $input['incident_date']  ?? '',
                'sanction_notes'=> $input['sanction_notes'] ?? '',
                'action_count'  => (int) ($input['action_count'] ?? 0),
            ]);

            if (!$result['success']) {
                $this->sendJson(['success' => false, 'data' => null, 'error' => $result['error']]);
            }

            $this->sendJson([
                'success' => true,
                'data'    => ['summary' => $result['summary']],
                'error'   => null,
            ]);

        } catch (Throwable $e) {
            error_log('ACVMS AIController::summarizeCase — Uncaught: ' . $e->getMessage());
            $this->sendJson(['success' => false, 'data' => null, 'error' => 'An unexpected server error occurred. Please try again.'], 500);
        }
    }

    /**
     * Flush the output buffer (discarding any stray PHP error HTML),
     * then send a clean JSON response and exit.
     */
    private function sendJson(array $payload, int $status = 200): never
    {
        // Capture and discard any accidental PHP output (notices, warnings, etc.)
        $leaked = ob_get_clean();
        if ($leaked && trim($leaked) !== '') {
            error_log('ACVMS AIController::sendJson — Leaked PHP output discarded: ' . substr($leaked, 0, 500));
        }

        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($payload);
        exit;
    }

    private function parseInput(): array
    {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';

        if (str_contains($contentType, 'application/json')) {
            $body = file_get_contents('php://input');
            $json = json_decode($body, true);
            return is_array($json) ? $json : [];
        }

        // Regular form POST
        return $_POST;
    }
}

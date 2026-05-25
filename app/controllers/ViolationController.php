<?php

/**
 * Violation Controller
 *
 * Manages academic violation records.
 *
 * Authorization matrix:
 *   index   → admin, teacher, student  (students see only their own — enforced in model)
 *   create  → admin, teacher
 *   store   → admin, teacher
 *   show    → admin, teacher, student  (students see only their own — enforced in model)
 *   edit    → admin, teacher
 *   update  → admin, teacher
 *   delete  → admin only
 */

class ViolationController extends Controller
{
    // ── List violations ───────────────────────────────────────────────────────

    /**
     * GET /violations
     *
     * - Admins/Teachers see all violations.
     * - Students see only their own (scoped in model, Phase 7+).
     */
    public function index(): void
    {
        authorize(['admin', 'teacher', 'student']);

        $this->view('violations.index', [
            'title'     => 'Violations — ' . APP_NAME,
            'pageTitle' => 'Violations',
            'user'      => currentUser(),
        ]);
    }

    // ── Show create form ──────────────────────────────────────────────────────

    /**
     * GET /violations/create
     *
     * Students cannot create violations.
     */
    public function create(): void
    {
        authorize(['admin', 'teacher']);

        $this->view('violations.create', [
            'title'     => 'Report Violation — ' . APP_NAME,
            'pageTitle' => 'Report a Violation',
            'user'      => currentUser(),
        ]);
    }

    // ── Store new violation ───────────────────────────────────────────────────

    /**
     * POST /violations
     */
    public function store(): void
    {
        authorize(['admin', 'teacher']);

        // TODO (Phase 7+): validate and persist violation
        $this->redirect(APP_URL . '/violations');
    }

    // ── Show single violation ─────────────────────────────────────────────────

    /**
     * GET /violations/{id}
     *
     * Students may only view their own violation — enforced here and in the model.
     */
    public function show(int $id): void
    {
        authorize(['admin', 'teacher', 'student']);

        // TODO (Phase 7+): load violation; if student, confirm ownership
        $this->view('violations.show', [
            'title'       => 'Violation Details — ' . APP_NAME,
            'pageTitle'   => 'Violation Details',
            'user'        => currentUser(),
            'violationId' => $id,
        ]);
    }

    // ── Show edit form ────────────────────────────────────────────────────────

    /**
     * GET /violations/{id}/edit
     */
    public function edit(int $id): void
    {
        authorize(['admin', 'teacher']);

        $this->view('violations.edit', [
            'title'       => 'Edit Violation — ' . APP_NAME,
            'pageTitle'   => 'Edit Violation',
            'user'        => currentUser(),
            'violationId' => $id,
        ]);
    }

    // ── Update violation ──────────────────────────────────────────────────────

    /**
     * POST /violations/{id}
     */
    public function update(int $id): void
    {
        authorize(['admin', 'teacher']);

        // TODO (Phase 7+): validate and persist changes
        $this->redirect(APP_URL . '/violations/' . $id);
    }

    // ── Delete violation ──────────────────────────────────────────────────────

    /**
     * POST /violations/{id}/delete
     *
     * Closing/archiving a case is admin-only.
     */
    public function delete(int $id): void
    {
        authorize('admin');

        // TODO (Phase 7+): soft-delete or archive the violation
        $this->redirect(APP_URL . '/violations');
    }
}

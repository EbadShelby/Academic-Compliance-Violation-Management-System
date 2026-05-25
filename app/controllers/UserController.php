<?php

/**
 * User Controller
 *
 * Manages user accounts. Restricted to Admin only.
 *
 * All actions begin with:
 *   authorize('admin');
 *
 * This ensures:
 *  - Guests are redirected to /login.
 *  - Non-admin authenticated users receive a 403 Forbidden response.
 */

class UserController extends Controller
{
    // ── List all users ────────────────────────────────────────────────────────

    /**
     * GET /admin/users
     */
    public function index(): void
    {
        authorize('admin');

        // TODO (Phase 7+): fetch users from User model and render view
        $this->view('users.index', [
            'title'     => 'Manage Users — ' . APP_NAME,
            'pageTitle' => 'User Management',
            'user'      => currentUser(),
        ]);
    }

    // ── Show create form ──────────────────────────────────────────────────────

    /**
     * GET /admin/users/create
     */
    public function create(): void
    {
        authorize('admin');

        $this->view('users.create', [
            'title'     => 'Create User — ' . APP_NAME,
            'pageTitle' => 'Create User',
            'user'      => currentUser(),
        ]);
    }

    // ── Store new user ────────────────────────────────────────────────────────

    /**
     * POST /admin/users
     */
    public function store(): void
    {
        authorize('admin');

        // TODO (Phase 7+): validate input and persist to DB
        $this->redirect(APP_URL . '/admin/users');
    }

    // ── Show single user ──────────────────────────────────────────────────────

    /**
     * GET /admin/users/{id}
     */
    public function show(int $id): void
    {
        authorize('admin');

        $this->view('users.show', [
            'title'     => 'View User — ' . APP_NAME,
            'pageTitle' => 'User Details',
            'user'      => currentUser(),
            'userId'    => $id,
        ]);
    }

    // ── Show edit form ────────────────────────────────────────────────────────

    /**
     * GET /admin/users/{id}/edit
     */
    public function edit(int $id): void
    {
        authorize('admin');

        $this->view('users.edit', [
            'title'     => 'Edit User — ' . APP_NAME,
            'pageTitle' => 'Edit User',
            'user'      => currentUser(),
            'userId'    => $id,
        ]);
    }

    // ── Update user ───────────────────────────────────────────────────────────

    /**
     * POST /admin/users/{id}
     */
    public function update(int $id): void
    {
        authorize('admin');

        // TODO (Phase 7+): validate and persist changes
        $this->redirect(APP_URL . '/admin/users/' . $id);
    }

    // ── Delete user ───────────────────────────────────────────────────────────

    /**
     * POST /admin/users/{id}/delete
     */
    public function delete(int $id): void
    {
        authorize('admin');

        // TODO (Phase 7+): soft-delete or hard-delete user record
        $this->redirect(APP_URL . '/admin/users');
    }
}

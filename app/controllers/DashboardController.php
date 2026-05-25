<?php

/**
 * Dashboard Controller
 *
 * The home screen after login — shows a summary for all roles.
 */

class DashboardController extends Controller
{
    public function index(): void
    {
        // Guard: must be logged in
        AuthMiddleware::handle();

        $user = Session::user();

        $this->view('dashboard.index', [
            'title'     => 'Dashboard — ' . APP_NAME,
            'pageTitle' => 'Dashboard',
            'user'      => $user,
        ]);
    }
}

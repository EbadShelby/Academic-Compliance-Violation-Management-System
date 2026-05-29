<?php
/**
 * Dashboard router stub — Phase 13
 *
 * The DashboardController now dispatches to role-specific views directly,
 * so this file is no longer the primary view. It is kept for backwards-
 * compatibility (in case anything include-chains to it) but simply
 * redirects to the controller entry point by re-triggering the controller.
 *
 * In practice, main.php resolves $content via the controller's view() call,
 * so this file should never be rendered independently.
 */
?>
<!-- dashboard/index.php: see DashboardController — role-based dispatch in Phase 13 -->

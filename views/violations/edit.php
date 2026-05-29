<?php
/**
 * views/violations/edit.php
 * Edit violation — stub for future phase
 */
?>

<div class="detail-card" style="background:var(--surface-card);border:1px solid var(--border-subtle);border-radius:1rem;overflow:hidden;">
    <div class="detail-card-header" style="display:flex;align-items:center;gap:.625rem;padding:.875rem 1.25rem;border-bottom:1px solid var(--border-subtle);font-size:.9375rem;font-weight:700;background:rgba(255,255,255,.025);">
        <i class="bi bi-pencil-square" style="color:var(--brand-primary);"></i>
        Edit Violation #<?= $violation['id'] ?>
    </div>
    <div style="padding:2rem;text-align:center;">
        <i class="bi bi-tools" style="font-size:3rem;color:var(--text-muted);opacity:.4;display:block;margin-bottom:1rem;"></i>
        <h5 style="color:var(--text-primary);">Coming Soon</h5>
        <p class="text-muted" style="font-size:.875rem;max-width:360px;margin:0 auto 1.5rem;">
            Violation editing will be available in the next phase. This stub is here to
            prevent routing errors.
        </p>
        <a href="<?= APP_URL ?>/violations/<?= $violation['id'] ?>" class="btn-back"
           style="display:inline-flex;align-items:center;gap:.4rem;padding:.5rem 1.25rem;background:rgba(255,255,255,.06);border:1px solid var(--border-subtle);border-radius:.625rem;color:var(--text-muted);font-size:.875rem;text-decoration:none;">
            <i class="bi bi-arrow-left"></i> Back to Violation
        </a>
    </div>
</div>

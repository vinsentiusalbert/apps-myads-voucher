<?php
    $icon = $icon ?? 'dashboard';
?>

<?php if(empty($subnav)): ?>
    <span class="portal-nav__icon-wrap">
        <?php if($icon === 'dashboard'): ?>
            <svg class="portal-nav__svg" viewBox="0 0 24 24" aria-hidden="true">
                <path d="M4 4h7v7H4zM13 4h7v4h-7zM13 10h7v10h-7zM4 13h7v7H4z" fill="currentColor"/>
            </svg>
        <?php elseif($icon === 'sms'): ?>
            <svg class="portal-nav__svg" viewBox="0 0 24 24" aria-hidden="true">
                <path d="M4 6.5A2.5 2.5 0 0 1 6.5 4h11A2.5 2.5 0 0 1 20 6.5v7A2.5 2.5 0 0 1 17.5 16h-7.2L6 19.5V16.3A2.5 2.5 0 0 1 4 13.8z" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                <path d="M7.5 8.5h9M7.5 11.5h6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
            </svg>
        <?php elseif($icon === 'wa'): ?>
            <svg class="portal-nav__svg" viewBox="0 0 24 24" aria-hidden="true">
                <path d="M12 4a8 8 0 0 0-6.93 12l-.87 4 4.1-.8A8 8 0 1 0 12 4Z" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                <path d="M9 9.7c.2-.5.4-.5.7-.5h.6c.2 0 .4 0 .6.5.2.6.8 2 .8 2.2s.1.3 0 .5c-.1.2-.2.3-.4.5l-.3.3c-.1.1-.3.3-.1.6.2.3.8 1.3 2 2 .8.5 1.4.7 1.7.8.3.1.5.1.7-.1l.9-1.1c.2-.2.4-.2.7-.1l1.8.8c.3.1.5.2.5.4 0 .2-.1 1-.6 1.4-.5.4-1.2.5-1.5.5-.4 0-1-.1-1.8-.5a10 10 0 0 1-3-2 10.6 10.6 0 0 1-2.1-2.8c-.4-.8-.6-1.5-.6-2 0-.5.2-1 .5-1.3.3-.3.8-.6 1-.6Z" fill="currentColor"/>
            </svg>
        <?php endif; ?>
    </span>
<?php else: ?>
    <?php if($icon === 'location'): ?>
        <svg viewBox="0 0 24 24">
            <path d="M12 5a7 7 0 1 0 7 7c0-.8-.14-1.57-.4-2.28" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
            <path d="M12 2v4M12 18v4M2 12h4M18 12h4" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
            <circle cx="12" cy="12" r="2.5" fill="currentColor"/>
        </svg>
    <?php elseif($icon === 'broadcast'): ?>
        <svg viewBox="0 0 24 24">
            <rect x="4" y="6" width="16" height="12" rx="3" fill="none" stroke="currentColor" stroke-width="1.8"/>
            <path d="M7 10h10M7 14h7" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
        </svg>
    <?php elseif($icon === 'targeted'): ?>
        <svg viewBox="0 0 24 24">
            <circle cx="12" cy="12" r="6.5" fill="none" stroke="currentColor" stroke-width="1.8"/>
            <path d="M12 3v4M12 17v4M3 12h4M17 12h4" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
            <circle cx="12" cy="12" r="1.8" fill="currentColor"/>
        </svg>
    <?php elseif($icon === 'template'): ?>
        <svg viewBox="0 0 24 24">
            <rect x="4" y="5" width="16" height="14" rx="2.5" fill="none" stroke="currentColor" stroke-width="1.8"/>
            <path d="M8 9h8M8 12h8M8 15h5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
        </svg>
    <?php endif; ?>
<?php endif; ?>
<?php /**PATH D:\1 Kerja\shop-myads\resources\views/partials/portal-sidebar-icon.blade.php ENDPATH**/ ?>
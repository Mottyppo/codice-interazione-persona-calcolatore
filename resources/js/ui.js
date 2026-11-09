// Interactions shared by the Blade pages. Form actions and application requests stay server-side.
function initializeUi() {
    const authBackground = document.querySelector('[data-auth-dot-background]');
    const authCard = authBackground?.querySelector('[data-auth-card]');
    if (authCard) {
        // Follow the actual form card, including mobile layout and validation messages.
        const centerDotFade = () => {
            const card = authCard.getBoundingClientRect();
            const background = authBackground.getBoundingClientRect();
            authBackground.style.setProperty('--dot-focus-position', `50% ${card.top - background.top + card.height / 2}px`);
        };
        centerDotFade();
        const observer = new ResizeObserver(centerDotFade);
        observer.observe(authCard);
        observer.observe(authBackground);
    }

    const menuButton = document.querySelector('[data-menu-toggle]');
    const menu = menuButton && document.getElementById(menuButton.getAttribute('aria-controls'));
    menuButton?.addEventListener('click', () => {
        const open = menuButton.getAttribute('aria-expanded') !== 'true';
        menu?.classList.toggle('is-open', open);
        menuButton.setAttribute('aria-expanded', String(open));
    });

    const dropdownButton = document.querySelector('[data-dropdown-toggle]');
    const dropdown = dropdownButton && document.getElementById(dropdownButton.getAttribute('aria-controls'));
    function closeDropdown(restoreFocus = false) {
        if (!dropdown || dropdown.hidden) return;
        dropdown.hidden = true;
        dropdownButton.setAttribute('aria-expanded', 'false');
        if (restoreFocus) dropdownButton.focus();
    }
    dropdownButton?.addEventListener('click', (event) => {
        event.preventDefault();
        const open = dropdown.hidden;
        dropdown.hidden = !open;
        dropdownButton.setAttribute('aria-expanded', String(open));
    });
    document.addEventListener('click', (event) => {
        if (!event.target.closest('[data-dropdown]')) closeDropdown();
        const dismiss = event.target.closest('[data-dismiss-alert]');
        dismiss?.closest('[role="alert"]')?.remove();
    });
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') closeDropdown(true);
        if (!dropdown || !['ArrowDown', 'ArrowUp'].includes(event.key)) return;
        if (event.target !== dropdownButton && !dropdown.contains(event.target)) return;
        event.preventDefault();
        dropdown.hidden = false;
        dropdownButton.setAttribute('aria-expanded', 'true');
        const items = [...dropdown.querySelectorAll('a[href], button:not(:disabled)')];
        const current = items.indexOf(document.activeElement);
        const next = event.key === 'ArrowDown'
            ? Math.min(current + 1, items.length - 1)
            : current < 0 ? items.length - 1 : Math.max(0, current - 1);
        items[next]?.focus();
    });

    const modal = document.getElementById('confirmModal');
    if (!modal) return;
    const body = document.getElementById('confirmModalBody');
    const confirm = document.getElementById('confirmModalOk');
    const confirmedForms = new WeakSet();
    let pendingForm = null;
    let submitter = null;
    let previousFocus = null;
    let previousOverflow = '';

    function closeModal() {
        if (modal.hidden) return;
        modal.hidden = true;
        modal.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = previousOverflow;
        pendingForm = null;
        submitter = null;
        if (previousFocus?.isConnected) previousFocus.focus();
    }

    document.addEventListener('submit', (event) => {
        const form = event.target;
        if (!form.matches('form[data-confirm]')) return;
        if (confirmedForms.has(form)) {
            confirmedForms.delete(form);
            return;
        }
        event.preventDefault();
        pendingForm = form;
        submitter = event.submitter;
        previousFocus = document.activeElement;
        previousOverflow = document.body.style.overflow;
        body.textContent = form.dataset.confirm;
        modal.hidden = false;
        modal.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
        modal.focus();
    });
    modal.addEventListener('click', (event) => {
        if (event.target === modal || event.target.closest('[data-dialog-backdrop]') === event.target || event.target.closest('[data-dialog-close]')) closeModal();
    });
    confirm.addEventListener('click', () => {
        const form = pendingForm;
        const button = submitter;
        if (!form) return;
        closeModal();
        confirmedForms.add(form);
        form.requestSubmit(button?.form === form ? button : undefined);
    });
    document.addEventListener('keydown', (event) => {
        if (modal.hidden) return;
        if (event.key === 'Escape') {
            event.preventDefault();
            closeModal();
        }
        if (event.key === 'Tab') {
            const focusable = [...modal.querySelectorAll('button:not(:disabled), a[href], input:not(:disabled), [tabindex="0"]')];
            const first = focusable[0];
            const last = focusable.at(-1);
            if (event.shiftKey && (document.activeElement === first || document.activeElement === modal)) {
                event.preventDefault();
                last?.focus();
            } else if (!event.shiftKey && (document.activeElement === last || !modal.contains(document.activeElement))) {
                event.preventDefault();
                first?.focus();
            }
        }
    });
    document.addEventListener('focusin', (event) => {
        if (!modal.hidden && !modal.contains(event.target)) modal.focus();
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeUi, { once: true });
} else {
    initializeUi();
}

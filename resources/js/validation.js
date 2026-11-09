// Keep native constraint validation, with Italian messages and inline feedback
// also when the browser or operating system uses another language.
const fieldSelector = 'input:not([type="hidden"]):not([type="submit"]):not([type="button"]), select, textarea';

function formatLimit(field, value) {
    if (field.type === 'date' && /^\d{4}-\d{2}-\d{2}$/.test(value)) return value.split('-').reverse().join('/');
    return value;
}

export function validationMessage(field) {
    const validity = field.validity;
    const label = field.labels?.[0]?.textContent.trim().replace(/\s+/g, ' ').replace(/\s*\(facoltativ[aeio]\)\s*/i, '') || 'questo campo';
    if (validity.valueMissing || (field.required && ['text', 'search', 'textarea'].includes(field.type) && !field.value.trim())) {
        if (field.type === 'file') return 'Seleziona un file CSV da caricare.';
        if (field.type === 'radio' || field.type === 'checkbox' || field.tagName === 'SELECT') return `Seleziona un valore per «${label}».`;
        return `Compila il campo «${label}».`;
    }
    if (validity.typeMismatch) return field.type === 'email' ? 'Inserisci un indirizzo email valido, ad esempio nome@dominio.it.' : 'Inserisci un indirizzo web valido.';
    if (validity.badInput) return ['time', 'date'].includes(field.type) ? 'Inserisci una data o un orario completo e valido.' : 'Inserisci un numero valido.';
    if (validity.patternMismatch) return field.dataset.formatMessage || 'Rispetta il formato richiesto per questo campo.';
    if (validity.tooLong) return `Inserisci al massimo ${field.maxLength} caratteri.`;
    if (validity.tooShort) return `Inserisci almeno ${field.minLength} caratteri.`;
    if (validity.rangeUnderflow) {
        if (field.type === 'number') return `Inserisci un numero maggiore o uguale a ${field.min}.`;
        return `Inserisci ${field.type === 'time' ? 'un orario' : 'una data'} uguale o successiv${field.type === 'time' ? 'o' : 'a'} a ${formatLimit(field, field.min)}.`;
    }
    if (validity.rangeOverflow) {
        if (field.type === 'number') return `Inserisci un numero minore o uguale a ${field.max}.`;
        return `Inserisci ${field.type === 'time' ? 'un orario' : 'una data'} uguale o precedente a ${formatLimit(field, field.max)}.`;
    }
    if (validity.stepMismatch) return field.type === 'number' ? 'Inserisci un numero intero valido.' : 'Inserisci un orario valido con ore e minuti.';
    return '';
}

function describe(field, id, add) {
    const descriptions = new Set((field.getAttribute('aria-describedby') || '').split(/\s+/).filter(Boolean));
    if (add) descriptions.add(id); else descriptions.delete(id);
    if (descriptions.size) field.setAttribute('aria-describedby', [...descriptions].join(' '));
    else field.removeAttribute('aria-describedby');
}

function showError(field, message) {
    if (!field.id) field.id = `validation-field-${++generatedId}`;
    const id = `${field.id}-client-error`;
    let error = document.getElementById(id);
    if (!error) {
        error = document.createElement('div');
        error.id = id;
        error.className = 'ui-field-error';
        error.setAttribute('aria-live', 'polite');
        const group = field.closest('[data-validation-group], .ui-input-group');
        (group || field).insertAdjacentElement('afterend', error);
    }
    error.textContent = message;
    error.hidden = !message;
    describe(field, id, Boolean(message));
    field.setAttribute('aria-invalid', String(Boolean(message)));
    field.classList.toggle('ui-invalid', Boolean(message));
}

let generatedId = 0;
const attempted = new WeakSet();

function validate(field, display = false) {
    if (!field.matches(fieldSelector) || !field.willValidate) return;
    field.setCustomValidity('');
    let message = validationMessage(field);
    if (!message && field.value && field.dataset.timeAfter) {
        const start = document.getElementById(field.dataset.timeAfter);
        if (start?.value && start.validity.valid && field.value <= start.value) message = 'L’ora di fine deve essere successiva all’ora di inizio.';
    }
    if (!message && field.value && field.dataset.dateAfterOrEqual) {
        const start = document.getElementById(field.dataset.dateAfterOrEqual);
        if (start?.value && start.validity.valid && field.value < start.value) message = 'La data di fine non può precedere quella di inizio.';
    }
    field.setCustomValidity(message);
    const error = document.getElementById(`${field.id}-client-error`);
    if (display || attempted.has(field) || error) showError(field, message);
    return message;
}

function onEdit(event) {
    const field = event.target;
    if (!(field instanceof HTMLElement) || !field.matches(fieldSelector)) return;
    const serverError = document.getElementById(`${field.id}-error`)
        || document.getElementById(`${field.name?.replace(/\[\]$/, '')}-error`);
    if (serverError) {
        serverError.hidden = true;
        describe(field, serverError.id, false);
    }
    field.setAttribute('aria-invalid', 'false');
    field.classList.remove('ui-invalid');
    validate(field);
    if (field.type === 'radio' && field.name) {
        field.form?.querySelectorAll('input[type="radio"]').forEach((radio) => {
            if (radio.name !== field.name || radio === field) return;
            if (serverError) describe(radio, serverError.id, false);
            radio.setAttribute('aria-invalid', 'false');
            radio.classList.remove('ui-invalid');
            validate(radio);
        });
    }
    field.form?.querySelectorAll('[data-time-after], [data-date-after-or-equal]').forEach((related) => {
        if (related === field || related.dataset.timeAfter === field.id || related.dataset.dateAfterOrEqual === field.id) {
            const message = validate(related);
            if (message || attempted.has(related)) showError(related, message || '');
        }
    });
}

document.addEventListener('invalid', (event) => {
    attempted.add(event.target);
    validate(event.target, true);
}, true);
document.addEventListener('input', onEdit);
document.addEventListener('change', onEdit);
document.addEventListener('field-validation-errors', (event) => {
    Object.entries(event.detail || {}).forEach(([name, messages]) => {
        const field = document.getElementById(name);
        if (field?.matches(fieldSelector)) {
            attempted.add(field);
            showError(field, Array.isArray(messages) ? messages.join(' ') : String(messages));
        }
    });
});

function initializeValidation() {
    document.querySelectorAll('[data-time-after], [data-date-after-or-equal]').forEach((field) => {
        const message = validate(field);
        if (message) showError(field, message);
    });
}
if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', initializeValidation, { once: true });
else initializeValidation();

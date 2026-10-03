document.querySelectorAll('.field').forEach((field, index) => {
    const label = field.querySelector('label');
    const control = field.querySelector('input:not([type="hidden"]), select, textarea');

    if (!label || !control) return;

    if (!control.id) control.id = `field-${index + 1}`;
    if (!label.htmlFor) label.htmlFor = control.id;

    const error = field.querySelector('.error-text');
    if (error) {
        if (!error.id) error.id = `${control.id}-error`;
        control.setAttribute('aria-invalid', 'true');
        control.setAttribute('aria-describedby', error.id);
    }
});

document.querySelectorAll('input:not([type="hidden"]), select, textarea').forEach((control) => {
    if (control.labels?.length || control.hasAttribute('aria-label') || control.hasAttribute('aria-labelledby')) return;

    const fallback = control.getAttribute('placeholder') || control.name?.replaceAll('_', ' ').replaceAll('[]', '');
    if (fallback) control.setAttribute('aria-label', fallback);
});

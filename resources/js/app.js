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

const updateControlledPanel = (control) => {
    const panel = document.getElementById(control.dataset.controls);
    if (!panel) return;

    const selectedControl = document.querySelector(`[name="${control.name}"]:checked`);
    const isVisible = selectedControl?.value === control.dataset.showWhen;

    panel.hidden = !isVisible;
    document.querySelectorAll(`[name="${control.name}"][data-controls="${control.dataset.controls}"]`).forEach((relatedControl) => {
        relatedControl.setAttribute('aria-expanded', String(isVisible));
    });
    panel.querySelectorAll('input, select, textarea').forEach((field) => {
        field.disabled = !isVisible;
    });
};

document.querySelectorAll('[data-controls]').forEach((control) => {
    control.addEventListener('change', () => updateControlledPanel(control));
    updateControlledPanel(control);
});

const updateLimitedGroup = (groupName, changedControl = null) => {
    const controls = [...document.querySelectorAll(`[data-limited-group="${groupName}"]`)];
    const exclusiveControl = controls.find((control) => control.hasAttribute('data-exclusive-option'));

    if (changedControl?.checked && changedControl === exclusiveControl) {
        controls.filter((control) => control !== exclusiveControl).forEach((control) => {
            control.checked = false;
        });
    } else if (changedControl?.checked && exclusiveControl) {
        exclusiveControl.checked = false;
    }

    const selectedCount = controls.filter((control) => control.checked).length;
    const limit = Number(controls[0]?.dataset.selectionLimit ?? 0);
    const status = document.querySelector(`[data-selection-status="${groupName}"]`);

    if (status) status.textContent = `${selectedCount} of ${limit} selected`;

    controls.forEach((control) => {
        const primaryControl = document.querySelector(`[data-primary-selection="${groupName}"]`);
        const duplicatesPrimary = primaryControl && control.dataset.primarySensitive === groupName && control.value === primaryControl.value;
        control.disabled = Boolean(duplicatesPrimary || (!control.checked && selectedCount >= limit));
    });
};

document.querySelectorAll('[data-limited-group]').forEach((control) => {
    control.addEventListener('change', () => updateLimitedGroup(control.dataset.limitedGroup, control));
});

document.querySelectorAll('[data-primary-selection]').forEach((control) => {
    const syncPrimarySelection = () => {
        const groupName = control.dataset.primarySelection;
        document.querySelectorAll(`[data-primary-sensitive="${groupName}"]`).forEach((secondaryControl) => {
            if (secondaryControl.value === control.value) secondaryControl.checked = false;
        });
        updateLimitedGroup(groupName);
    };

    control.addEventListener('change', syncPrimarySelection);
    syncPrimarySelection();
});

new Set([...document.querySelectorAll('[data-limited-group]')].map((control) => control.dataset.limitedGroup))
    .forEach((groupName) => updateLimitedGroup(groupName));

const updateProfilePreview = (select) => {
    const role = select.dataset.profileSelect;
    const selectedId = select.value;

    document.querySelectorAll(`[data-profile-card="${role}"]`).forEach((card) => {
        card.classList.toggle('hidden', card.dataset.userId !== selectedId);
    });

    const placeholder = document.querySelector(`[data-profile-placeholder="${role}"]`);
    if (placeholder) placeholder.classList.toggle('hidden', selectedId !== '');
};

document.querySelectorAll('[data-profile-select]').forEach((select) => {
    select.addEventListener('change', () => updateProfilePreview(select));
    updateProfilePreview(select);
});

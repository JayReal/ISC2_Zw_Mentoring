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

    updateMatchingOverlap();
};

const valuesFor = (card, key) => new Set((card?.dataset[key] ?? '').split(',').filter(Boolean));
const setsOverlap = (first, second) => [...first].some((value) => second.has(value));

const updateOverlapItem = (name, alignedText, differentText, aligned) => {
    const item = document.querySelector(`[data-overlap="${name}"]`);
    if (!item) return;

    item.textContent = aligned ? alignedText : differentText;
    item.classList.toggle('text-green-800', aligned);
    item.classList.toggle('font-medium', aligned);
    item.classList.toggle('text-neutral-600', !aligned);
};

function updateMatchingOverlap() {
    const panel = document.querySelector('[data-overlap-panel]');
    if (!panel) return;

    const mentorId = document.querySelector('[data-profile-select="mentor"]')?.value;
    const menteeId = document.querySelector('[data-profile-select="mentee"]')?.value;
    const mentor = document.querySelector(`[data-profile-card="mentor"][data-user-id="${mentorId}"]`);
    const mentee = document.querySelector(`[data-profile-card="mentee"][data-user-id="${menteeId}"]`);
    panel.classList.toggle('hidden', !mentor || !mentee);
    if (!mentor || !mentee) return;

    updateOverlapItem('availability', 'Shared availability recorded', 'No shared availability recorded', setsOverlap(valuesFor(mentor, 'availability'), valuesFor(mentee, 'availability')));
    updateOverlapItem('clusters', 'Shared community recorded', 'No shared community recorded', setsOverlap(valuesFor(mentor, 'clusters'), valuesFor(mentee, 'clusters')));
    const mentorStyles = new Set([...valuesFor(mentor, 'styles')].filter((value) => value !== 'no-preference'));
    const menteeStyles = new Set([...valuesFor(mentee, 'styles')].filter((value) => value !== 'no-preference'));
    updateOverlapItem('styles', 'Shared working preference', 'Different or unrecorded preferences', setsOverlap(mentorStyles, menteeStyles));

    const formatsAlign = mentor.dataset.format && mentee.dataset.format && (mentor.dataset.format === mentee.dataset.format || mentor.dataset.format === 'hybrid' || mentee.dataset.format === 'hybrid');
    updateOverlapItem('format', 'Meeting formats align', 'Meeting formats differ', formatsAlign);
    updateOverlapItem('language', 'Preferred language aligns', 'Preferred languages differ', Boolean(mentor.dataset.language && mentor.dataset.language === mentee.dataset.language));
}

document.querySelectorAll('[data-profile-select]').forEach((select) => {
    select.addEventListener('change', () => updateProfilePreview(select));
    updateProfilePreview(select);
});

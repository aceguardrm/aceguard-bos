'use strict';

document.addEventListener('DOMContentLoaded', () => {
    const controlForms = document.querySelectorAll('.ag-control-item');

    if (!controlForms.length) {
        return;
    }

    controlForms.forEach((form) => {
        form.addEventListener('submit', async (event) => {
            event.preventDefault();

            if (form.dataset.saving === 'true') {
                return;
            }

            await updateSecurityControl(form);
        });
    });
});

/**
 * Update one security control without reloading the page.
 */
async function updateSecurityControl(form) {
    const toggleButton = form.querySelector('.ag-toggle-button');
    const enabledInput = form.querySelector('input[name="enabled"]');

    if (!toggleButton || !enabledInput) {
        showWorkspaceMessage(
            'This security control could not be updated.',
            'error'
        );

        return;
    }

    form.dataset.saving = 'true';
    toggleButton.disabled = true;
    toggleButton.classList.add('is-loading');

    try {
        const formData = new FormData(form);

        const response = await fetch(form.action, {
            method: 'POST',
            body: formData,
            credentials: 'same-origin',
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        const payload = await readResponse(response);

        if (!response.ok) {
            throw new Error(
                payload.message
                || firstValidationError(payload.errors)
                || 'The security control could not be updated.'
            );
        }

        updateControlRow(form, payload.control);
        updateSecuritySummary(payload.security);
        updateCategoryProgress(form);

        showWorkspaceMessage(
            payload.message || 'Security control updated.',
            'success'
        );
    } catch (error) {
        console.error('Security control update failed:', error);

        showWorkspaceMessage(
            error.message || 'An unexpected error occurred.',
            'error'
        );
    } finally {
        form.dataset.saving = 'false';
        toggleButton.disabled = false;
        toggleButton.classList.remove('is-loading');
    }
}

/**
 * Safely read a JSON response.
 */
async function readResponse(response) {
    const contentType = response.headers.get('content-type') || '';

    if (!contentType.includes('application/json')) {
        return {};
    }

    return response.json();
}

/**
 * Return the first Laravel validation error.
 */
function firstValidationError(errors) {
    if (!errors || typeof errors !== 'object') {
        return null;
    }

    const messages = Object.values(errors).flat();

    return messages.length ? messages[0] : null;
}

/**
 * Update the selected control row.
 */
function updateControlRow(form, control) {
    const toggleButton = form.querySelector('.ag-toggle-button');
    const enabledInput = form.querySelector('input[name="enabled"]');
    const statusBadge = form.querySelector('.ag-control-status');
    const description = form.querySelector('.ag-control-copy p');

    const enabled = Boolean(control.enabled);

    toggleButton.classList.toggle(
        'ag-toggle-button--active',
        enabled
    );

    toggleButton.setAttribute('aria-pressed', enabled ? 'true' : 'false');

    enabledInput.value = enabled ? '0' : '1';

    if (statusBadge) {
        statusBadge.textContent = enabled
            ? 'Complete'
            : 'Outstanding';

        statusBadge.classList.toggle(
            'ag-control-status--complete',
            enabled
        );

        statusBadge.classList.toggle(
            'ag-control-status--outstanding',
            !enabled
        );
    }

    if (description) {
        description.textContent = enabled
            ? 'This security control is currently satisfied.'
            : 'Complete this control to improve the client’s security posture.';
    }
}

/**
 * Update score, counters, progress and recommendations.
 */
function updateSecuritySummary(security) {
    const score = Number(security.score || 0);
    const completed = Number(security.completed || 0);
    const outstanding = Number(security.outstanding || 0);
    const earned = Number(security.earned || 0);
    const maximum = Number(security.maximum || 0);
    const rating = security.rating || 'Not assessed';

    updateScoreRing(score, rating);
    updateScoreCopy(earned, maximum, rating);
    updateMetricCards(completed, outstanding);
    updateWorkspaceSummary(
        completed,
        outstanding,
        earned,
        maximum,
        rating
    );
    updateRecommendations(security.recommendations || []);
}

/**
 * Animate the security score ring.
 */
function updateScoreRing(score, rating) {
    const ring = document.querySelector('.ag-score-ring');
    const scoreElement = document.querySelector(
        '.ag-score-ring__centre strong'
    );
    const ratingElement = document.querySelector(
        '.ag-score-ring__centre span'
    );

    if (!ring) {
        return;
    }

    const previousScore = parseInt(
        ring.style.getPropertyValue('--score'),
        10
    ) || 0;

    const colour = scoreColour(score);

    ring.style.setProperty('--score-colour', colour);
    animateNumber(previousScore, score, 450, (value) => {
        ring.style.setProperty('--score', String(value));

        if (scoreElement) {
            scoreElement.textContent = `${value}%`;
        }
    });

    if (ratingElement) {
        ratingElement.textContent = rating;
    }
}

/**
 * Update text beside the score and its progress bar.
 */
function updateScoreCopy(earned, maximum, rating) {
    const heading = document.querySelector('.ag-score-copy h3');
    const paragraph = document.querySelector('.ag-score-copy p');
    const progress = document.querySelector(
        '.ag-score-copy .ag-progress__value'
    );

    const score = maximum > 0
        ? Math.round((earned / maximum) * 100)
        : 0;

    if (heading) {
        heading.textContent = `${rating} cyber posture`;
    }

    if (paragraph) {
        paragraph.textContent =
            `${earned} of ${maximum} available security `
            + 'points have been achieved.';
    }

    if (progress) {
        progress.style.width = `${score}%`;
        progress.style.background = scoreColour(score);
    }
}

/**
 * Update completed and outstanding metric cards.
 */
function updateMetricCards(completed, outstanding) {
    const metricCards = document.querySelectorAll('.ag-metric-card');

    if (metricCards[0]) {
        const completedValue = metricCards[0].querySelector('strong');

        if (completedValue) {
            animateDisplayedNumber(completedValue, completed);
        }
    }

    if (metricCards[1]) {
        const outstandingValue = metricCards[1].querySelector('strong');

        if (outstandingValue) {
            animateDisplayedNumber(outstandingValue, outstanding);
        }
    }
}

/**
 * Update the assessment summary panel.
 */
function updateWorkspaceSummary(
    completed,
    outstanding,
    earned,
    maximum,
    rating
) {
    const summaryRows = document.querySelectorAll(
        '.ag-summary-list > div'
    );

    if (summaryRows[1]) {
        const assessed = summaryRows[1].querySelector('dd');

        if (assessed) {
            assessed.textContent = String(completed + outstanding);
        }
    }

    if (summaryRows[2]) {
        const points = summaryRows[2].querySelector('dd');

        if (points) {
            points.textContent = `${earned} / ${maximum}`;
        }
    }

    if (summaryRows[3]) {
        const currentRating = summaryRows[3].querySelector('dd');

        if (currentRating) {
            currentRating.textContent = rating;
        }
    }
}

/**
 * Update recommendations after each control change.
 */
function updateRecommendations(recommendations) {
    const list = document.querySelector('.ag-recommendation-list');

    if (!list) {
        return;
    }

    list.replaceChildren();

    if (!recommendations.length) {
        const positiveState = document.createElement('div');
        positiveState.className = 'ag-positive-state';

        const icon = document.createElement('i');
        icon.className = 'fas fa-circle-check';

        const paragraph = document.createElement('p');
        paragraph.textContent =
            'All assessed security controls are complete.';

        positiveState.append(icon, paragraph);
        list.appendChild(positiveState);

        return;
    }

    recommendations.forEach((recommendationText) => {
        const recommendation = document.createElement('div');
        recommendation.className = 'ag-recommendation';

        const iconWrapper = document.createElement('div');
        iconWrapper.className = 'ag-recommendation__icon';

        const icon = document.createElement('i');
        icon.className = 'fas fa-arrow-trend-up';

        const paragraph = document.createElement('p');
        paragraph.textContent = recommendationText;

        iconWrapper.appendChild(icon);
        recommendation.append(iconWrapper, paragraph);
        list.appendChild(recommendation);
    });
}

/**
 * Update the completion count for the affected category.
 */
function updateCategoryProgress(form) {
    const categoryCard = form.closest('.ag-category-card');

    if (!categoryCard) {
        return;
    }

    const forms = categoryCard.querySelectorAll('.ag-control-item');
    const completed = categoryCard.querySelectorAll(
        '.ag-toggle-button--active'
    ).length;

    const counter = categoryCard.querySelector('.ag-category-count');

    if (counter) {
        counter.textContent =
            `${completed} / ${forms.length} complete`;
    }
}

/**
 * Display a temporary workspace notification.
 */
function showWorkspaceMessage(message, type) {
    let notification = document.querySelector(
        '.ag-live-notification'
    );

    if (!notification) {
        notification = document.createElement('div');
        notification.className = 'ag-live-notification';
        notification.setAttribute('role', 'status');
        notification.setAttribute('aria-live', 'polite');

        document.body.appendChild(notification);
    }

    notification.className =
        `ag-live-notification ag-live-notification--${type}`;

    notification.textContent = message;
    notification.classList.add('is-visible');

    window.clearTimeout(notification.hideTimer);

    notification.hideTimer = window.setTimeout(() => {
        notification.classList.remove('is-visible');
    }, 3000);
}

/**
 * Animate an integer value.
 */
function animateNumber(start, end, duration, callback) {
    const startedAt = performance.now();

    const step = (currentTime) => {
        const elapsed = currentTime - startedAt;
        const progress = Math.min(elapsed / duration, 1);
        const eased = 1 - Math.pow(1 - progress, 3);
        const value = Math.round(start + ((end - start) * eased));

        callback(value);

        if (progress < 1) {
            requestAnimationFrame(step);
        }
    };

    requestAnimationFrame(step);
}

/**
 * Animate a number already displayed in an element.
 */
function animateDisplayedNumber(element, newValue) {
    const currentValue = parseInt(element.textContent, 10) || 0;

    animateNumber(currentValue, newValue, 350, (value) => {
        element.textContent = String(value);
    });
}

/**
 * Choose a colour based on the calculated score.
 */
function scoreColour(score) {
    if (score >= 90) {
        return '#10b981';
    }

    if (score >= 75) {
        return '#2563eb';
    }

    if (score >= 60) {
        return '#f59e0b';
    }

    if (score >= 40) {
        return '#f97316';
    }

    return '#dc2626';
}
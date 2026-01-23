document.addEventListener('DOMContentLoaded', () => {
    const passwordInput = document.getElementById('password');
    const confirmInput = document.getElementById('password_confirmation');
    const submitBtn = document.getElementById('reset-password-btn');

    const fill = document.getElementById('password-strength-fill');
    const strengthText = document.getElementById('password-strength-text');
    const matchText = document.getElementById('password-match-text');

    const criteriaItems = document.querySelectorAll('#password-criteria li');

    if (!passwordInput || !confirmInput || !submitBtn) return;

    const labels = globalThis.passwordI18n;

    let isStrongEnough = false;
    let isMatching = false;

    const rules = {
        length: (pw) => pw.length >= 12,
        lower: (pw) => /[a-z]/.test(pw),
        upper: (pw) => /[A-Z]/.test(pw),
        number: (pw) => /\d/.test(pw),
        symbol: (pw) => /[^A-Za-z0-9]/.test(pw),
    };

    const ICON_CHECK_SVG = `
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" role="img" focusable="false" aria-hidden="true">
  <path d="M470.6 105.4c12.5 12.5 12.5 32.8 0 45.3l-256 256c-12.5 12.5-32.8 12.5-45.3 0l-128-128c-12.5-12.5-12.5-32.8 0-45.3s32.8-12.5 45.3 0L192 338.7 425.4 105.4c12.5-12.5 32.8-12.5 45.2 0z"/>
</svg>
`;

    const ICON_XMARK_SVG = `
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512" role="img" focusable="false" aria-hidden="true">
  <path d="M342.6 150.6c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L192 210.7 86.6 105.4c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3L146.7 256 41.4 361.4c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0L192 301.3 297.4 406.6c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3L237.3 256 342.6 150.6z"/>
</svg>
`;
    function updateCriteria(pw) {
        let validCount = 0;

        criteriaItems.forEach((item) => {
            const rule = item.dataset.rule;
            const ok = rules[rule](pw);

            item.classList.toggle('valid', ok);
            item.classList.toggle('invalid', !ok);

            const icon = item.querySelector('.criterion-icon');
            if (icon) icon.innerHTML = ok ? ICON_CHECK_SVG : ICON_XMARK_SVG;

            if (ok) validCount++;
        });

        return validCount;
    }

    function updateStrength(pw) {
        const validCount = updateCriteria(pw);

        fill.style.transition = 'width 0.3s ease, background-color 0.3s ease';
        fill.className = 'password-strength-fill';

        strengthText.className = 'password-strength-text';

        if (!pw) {
            fill.style.width = '0%';
            strengthText.textContent = labels.empty;
            isStrongEnough = false;
            return;
        }

        if (validCount <= 2) {
            fill.style.width = '25%';
            fill.classList.add('weak');
            strengthText.textContent = labels.weak;
            isStrongEnough = false;
        } else if (validCount === 3) {
            fill.style.width = '50%';
            fill.classList.add('medium');
            strengthText.textContent = labels.medium;
            isStrongEnough = false;
        } else if (validCount === 4) {
            fill.style.width = '75%';
            fill.classList.add('strong');
            strengthText.textContent = labels.strong;
            isStrongEnough = true;
        } else {
            fill.style.width = '100%';
            fill.classList.add('very-strong');
            strengthText.textContent = labels.veryStrong;
            isStrongEnough = true;
        }
    }

    function updateMatch() {
        const pw = passwordInput.value || '';
        const confirm = confirmInput.value || '';

        matchText.classList.remove('match', 'no-match');

        if (!confirm) {
            matchText.textContent = labels.matchEmpty;
            isMatching = false;
            return;
        }

        if (pw === confirm) {
            matchText.classList.add('match');
            matchText.textContent = labels.matchOk;
            isMatching = true;
        } else {
            matchText.classList.add('no-match');
            matchText.textContent = labels.matchNo;
            isMatching = false;
        }
    }

    function updateSubmitState() {
        submitBtn.disabled = !(isStrongEnough && isMatching);
    }

    passwordInput.addEventListener('input', () => {
        updateStrength(passwordInput.value);
        updateMatch();
        updateSubmitState();
    });

    confirmInput.addEventListener('input', () => {
        updateMatch();
        updateSubmitState();
    });

    // init
    updateStrength('');
    updateMatch();
    updateSubmitState();
});

function isValidEmail(email) {
    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return typeof email === 'string' && emailPattern.test(email.trim());
}

document.addEventListener('DOMContentLoaded', () => {
    const emailInput = document.getElementById('email');
    const submitBtn = document.getElementById('submit-btn');

    emailInput.addEventListener('input', () => {
        const isValid = isValidEmail(emailInput.value);
        submitBtn.disabled = !isValid;
    });
});

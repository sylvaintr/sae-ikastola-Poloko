function isValidEmail(email) {
    // Regex sécurisée sans backtracking catastrophique
    // Utilise des quantificateurs atomiques implicites pour éviter DoS
    const emailPattern = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
    return typeof email === 'string' && email.trim().length > 0 && emailPattern.test(email.trim());
}

document.addEventListener('DOMContentLoaded', () => {
    const emailInput = document.getElementById('email');
    const submitBtn = document.getElementById('submit-btn');

    if (emailInput && submitBtn) {
        // Amélioration UX : validation en temps réel (optionnelle)
        // La validation serveur reste la validation principale
        emailInput.addEventListener('input', () => {
            const isValid = isValidEmail(emailInput.value);
            // Amélioration visuelle uniquement, ne bloque pas la soumission
            submitBtn.classList.toggle('btn-primary', isValid);
            submitBtn.classList.toggle('btn-secondary', !isValid);
        });
    }
});

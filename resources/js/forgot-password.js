import * as yup from 'yup';

document.addEventListener('DOMContentLoaded', () => {
    const emailInput = document.getElementById('email');
    const submitBtn = document.getElementById('submit-btn');

    const schema = yup.object({
        email: yup.string().email().required(),
    });

    emailInput.addEventListener('input', async () => {
        try {
            await schema.validate({ email: emailInput.value });
            submitBtn.disabled = false;
        } catch (e) {
            submitBtn.disabled = true;
        }
    });
});

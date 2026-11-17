document.querySelectorAll('[data-password-toggle]').forEach((button) => {
    button.addEventListener('click', () => {
        const input = document.getElementById(button.dataset.passwordToggle);
        const visible = input.type === 'password';
        input.type = visible ? 'text' : 'password';
        button.textContent = visible ? 'Sembunyikan' : 'Tampilkan';
        button.setAttribute('aria-pressed', String(visible));
        const label = document.querySelector(`label[for="${input.id}"]`).textContent.toLowerCase();
        button.setAttribute('aria-label', `${visible ? 'Sembunyikan' : 'Tampilkan'} ${label}`);
    });
});
document.querySelector('[data-auth-errors]')?.focus();
document.querySelectorAll('[data-auth-form]').forEach((form) => {
    form.addEventListener('submit', () => { form.querySelector('[type="submit"]').disabled = true; });
});
window.addEventListener('pageshow', (event) => {
    if (event.persisted) window.location.reload();
});

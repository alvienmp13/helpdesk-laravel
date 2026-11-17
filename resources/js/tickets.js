export function initTickets() {
    const form = document.querySelector('[data-ticket-search-form]');
    if (!form) return null;
    document.querySelectorAll('[data-delete-draft-form]').forEach((item) => {
        item.addEventListener('submit', (event) => {
            if (!window.confirm('Hapus draft ini beserta lampirannya?')) event.preventDefault();
        });
    });
    return {
        search(query) {
            form.elements.namedItem('q').value = query;
            form.requestSubmit();
        },
    };
}

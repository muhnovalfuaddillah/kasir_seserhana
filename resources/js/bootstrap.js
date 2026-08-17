// Native lightweight fetch helper with Laravel CSRF support
window.csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

window.http = {
    async get(url, headers = {}) {
        const res = await fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                ...headers,
            }
        });
        return res.json();
    },
    async post(url, body = {}, headers = {}) {
        const res = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': window.csrfToken,
                ...headers,
            },
            body: JSON.stringify(body)
        });
        return res.json();
    }
};

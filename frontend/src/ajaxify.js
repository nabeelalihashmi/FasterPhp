function ajaxify(form) {
    form.addEventListener('submit', async function (e) {
        e.preventDefault();
        form.dispatchEvent(new CustomEvent('form-submitting', {event: e}));
        try {    
            const res = await fetch(form.getAttribute('action'), {
                method: form.getAttribute('method'),
                body: new FormData(form),
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            await form.dispatchEvent(new CustomEvent('form-submitted', {detail: {res}}));
        } catch (ex) {
            form.dispatchEvent(new CustomEvent('form-error', {detail: {exception: ex}}))
        }
    })
}

export default ajaxify;
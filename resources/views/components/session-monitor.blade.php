@if(auth()->check() || session()->has('solicitante_id'))
<script>
(() => {
    let lastActivity = Date.now();
    let sentActivity = lastActivity;
    let exiting = false;
    const expire = () => {
        if (exiting) return;
        exiting = true;
        window.location.replace(@json(session()->has('solicitante_id') ? route('solicitud.logout') : url('/logout')));
    };
    const check = () => {
        if (Date.now() - lastActivity >= 30 * 60 * 1000) expire();
    };
    ['pointerdown', 'keydown', 'scroll', 'touchstart'].forEach(name => {
        document.addEventListener(name, () => { check(); if (!exiting) lastActivity = Date.now(); }, {passive: true});
    });
    document.addEventListener('visibilitychange', check);
    setInterval(async () => {
        check();
        if (exiting || lastActivity <= sentActivity) return;
        sentActivity = lastActivity;
        try {
            const response = await fetch(@json(route('sesion.actividad')), {
                method: 'POST',
                headers: {'X-CSRF-TOKEN': @json(csrf_token()), 'Accept': 'application/json'}
            });
            if ([401, 419].includes(response.status)) expire();
        } catch (_) { /* Retry activity on the next user interaction. */ }
    }, 60000);
})();
</script>
@endif

@if(auth()->check() || session()->has('solicitante_id'))
<script>
(() => {
    let lastActivity = Date.now();
    let sentActivity = lastActivity;
    let exiting = false;
    const expire = () => {
        if (exiting) return;
        exiting = true;
        @if(session()->has('solicitante_id'))
            window.location.replace("{{ route('solicitud.logout') }}");
        @else
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route('logout') }}';
            const csrf = document.createElement('input');
            csrf.type = 'hidden';
            csrf.name = '_token';
            csrf.value = '{{ csrf_token() }}';
            form.appendChild(csrf);
            document.body.appendChild(form);
            form.submit();
        @endif
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

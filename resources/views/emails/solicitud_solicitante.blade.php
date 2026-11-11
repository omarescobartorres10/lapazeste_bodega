<!-- resources/views/emails/solicitud_solicitante.blade.php -->
<p><strong>Hola {{ $solicitud->solicitante }},</strong></p>

<p>Hemos recibido tu solicitud de productos. Pronto será revisada.</p>

<p><strong>Bodega:</strong> {{ $solicitud->bodega->nombre ?? 'N/D' }}</p>

<p><strong>Detalle solicitado:</strong></p>
<ul>
@foreach($solicitud->detalles as $d)
    <li>{{ $d->producto->nombre }} - Cantidad: {{ $d->cantidad }} ({{ $d->observacion }})</li>
@endforeach
</ul>

<p>Gracias.</p>

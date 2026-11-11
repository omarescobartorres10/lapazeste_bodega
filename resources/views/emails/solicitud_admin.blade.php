<!-- resources/views/emails/solicitud_admin.blade.php -->
<p><strong>Hola administrador,</strong></p>
<p>Se ha recibido una nueva solicitud de productos.</p>

<p><strong>Solicitante:</strong> {{ $solicitud->solicitante }}</p>
<p><strong>Teléfono:</strong> {{ $solicitud->telefono }}</p>
<p><strong>Correo:</strong> {{ $solicitud->correo }}</p>
<p><strong>Departamento:</strong> {{ $solicitud->departamento_solicitante }}</p>
<p><strong>Bodega:</strong> {{ $solicitud->bodega->nombre ?? 'N/D' }}</p>

<p><strong>Detalle:</strong></p>
<ul>
@foreach($solicitud->detalles as $d)
    <li>{{ $d->producto->nombre }} - Cantidad: {{ $d->cantidad }} ({{ $d->observacion }})</li>
@endforeach
</ul>

<p><strong>Comentario del solicitante:</strong></p>
<p>{{ $solicitud->comentario }}</p>

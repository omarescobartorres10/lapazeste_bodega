<table>
    <thead>
        <tr>
            <th colspan="10" style="text-align:center; font-weight:bold; font-size:16px;">
                REPORTE DE PRODUCTOS {{ isset($user->bodega) ? ' - ' . strtoupper($user->bodega->nombre) : '' }}
            </th>
        </tr>
        <tr>
            <th>Código</th>
            <th>Nombre</th>
            <th>Descripción</th>
            <th>Bodega</th>
            <th>Stock</th>
            <th>Unidad</th>
            <th>Categoría</th>
            <th>Departamento</th>
            <th>Tipo</th>
            <th>Estado</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($productos as $p)
            <tr>
                <td>{{ $p->codigo }}</td>
                <td>{{ $p->nombre }}</td>
                <td>{{ $p->descripcion }}</td>
                <td>{{ $p->bodega->nombre ?? '—' }}</td>
                <td>{{ $p->stock }}</td>
                <td>{{ $p->unidades->nombre ?? '—' }}</td>
                <td>{{ $p->categoria->nombre ?? '—' }}</td>
                <td>{{ $p->departamento->nombre ?? '—' }}</td>
                <td>{{ $p->tipoProducto->nombre ?? '—' }}</td>
                <td>{{ $p->estado ? 'Activo' : 'Inactivo' }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

<table>
    <thead>
        <tr>
            <th colspan="10">
                REPORTE DE PRODUCTOS {{ isset($user->bodega) ? ' - ' . strtoupper($user->bodega->nombre) : ' - TODAS LAS BODEGAS' }}
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
        @foreach ($productos as $producto)
            <tr>
                <td>{{ $producto->codigo }}</td>
                <td>{{ $producto->nombre }}</td>
                <td>{{ $producto->descripcion ?? '—' }}</td>
                <td>{{ $producto->bodega->nombre ?? '—' }}</td>
                <td>{{ $producto->stock }}</td>
                <td>{{ $producto->unidades->nombre ?? '—' }}</td>
                <td>{{ $producto->categoria->nombre ?? '—' }}</td>
                <td>{{ $producto->departamento->nombre ?? '—' }}</td>
                <td>{{ $producto->tipoProducto->nombre ?? '—' }}</td>
                <td>{{ $producto->estado ? 'Activo' : 'Inactivo' }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

@extends('layouts.admin')

@section('title', 'Admin • Opiniones de clientes')

@section('content')
<div class="py-3">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0">Opiniones de clientes</h1>
    <a href="{{ route('admin.testimonials.create') }}" class="btn btn-primary">
      <i class="bi bi-plus-circle me-1"></i>Añadir testimonio
    </a>
  </div>

  @if(session('status'))
    <div class="alert alert-success">{{ session('status') }}</div>
  @endif

  <div class="card">
    <div class="table-responsive">
      <table class="table table-striped align-middle mb-0">
        <thead>
          <tr>
            <th>Orden</th>
            <th>Autor</th>
            <th>Ubicación</th>
            <th>Cita</th>
            <th>Activo</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          @foreach($testimonials as $t)
          <tr>
            <td>{{ $t->order_index }}</td>
            <td>{{ $t->author_name }}</td>
            <td class="text-muted small">{{ $t->author_location }}</td>
            <td class="small" style="max-width: 500px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $t->quote }}</td>
            <td>
              @if($t->is_active)
                <span class="badge bg-success">Sí</span>
              @else
                <span class="badge bg-secondary">No</span>
              @endif
            </td>
            <td class="text-end">
              <a href="{{ route('admin.testimonials.edit', $t) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
              <form action="{{ route('admin.testimonials.destroy', $t) }}" method="POST" class="d-inline" onsubmit="return confirm('Eliminar testimonio?')">
                @csrf
                @method('DELETE')
                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
              </form>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
    <div class="card-footer">
      {{ $testimonials->links('custom-pagination') }}
    </div>
  </div>
</div>
@endsection



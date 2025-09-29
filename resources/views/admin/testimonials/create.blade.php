@extends('layouts.admin')

@section('title', 'Añadir testimonio')

@section('content')
<div class="py-3">
  <h1 class="h4 mb-3">Añadir testimonio</h1>

  @if ($errors->any())
    <div class="alert alert-danger">
      <ul class="mb-0">
        @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <div class="card">
    <div class="card-body">
      <form method="POST" action="{{ route('admin.testimonials.store') }}" enctype="multipart/form-data" class="row g-3">
        @csrf
        <div class="col-md-6">
          <label class="form-label">Autor *</label>
          <input type="text" name="author_name" class="form-control" required>
        </div>
        <div class="col-md-6">
          <label class="form-label">Ubicación</label>
          <input type="text" name="author_location" class="form-control" placeholder="Madrid, ES">
        </div>
        <div class="col-12">
          <label class="form-label">Cita *</label>
          <textarea name="quote" class="form-control" rows="3" required></textarea>
        </div>
        <div class="col-md-6">
          <label class="form-label">Imagen</label>
          <input type="file" name="image" class="form-control" accept="image/*">
        </div>
        <div class="col-md-3">
          <label class="form-label">Orden</label>
          <input type="number" name="order_index" class="form-control" min="0" value="0">
        </div>
        <div class="col-md-3 d-flex align-items-end">
          <div class="form-check">
            <input class="form-check-input" type="checkbox" name="is_active" id="is_active" checked>
            <label class="form-check-label" for="is_active">Activo</label>
          </div>
        </div>
        <div class="col-12">
          <button class="btn btn-primary" type="submit">Guardar</button>
          <a href="{{ route('admin.testimonials.index') }}" class="btn btn-outline-secondary">Cancelar</a>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection



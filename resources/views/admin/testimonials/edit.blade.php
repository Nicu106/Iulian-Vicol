@extends('layouts.admin')

@section('title', 'Editar testimonio')

@section('content')
<div class="py-3">
  <h1 class="h4 mb-3">Editar testimonio</h1>

  <div class="card">
    <div class="card-body">
      <form method="POST" action="{{ route('admin.testimonials.update', $testimonial) }}" enctype="multipart/form-data" class="row g-3">
        @csrf
        @method('PUT')
        <div class="col-md-6">
          <label class="form-label">Autor *</label>
          <input type="text" name="author_name" class="form-control" value="{{ old('author_name', $testimonial->author_name) }}" required>
        </div>
        <div class="col-md-6">
          <label class="form-label">Ubicación</label>
          <input type="text" name="author_location" class="form-control" value="{{ old('author_location', $testimonial->author_location) }}">
        </div>
        <div class="col-12">
          <label class="form-label">Cita *</label>
          <textarea name="quote" class="form-control" rows="3" required>{{ old('quote', $testimonial->quote) }}</textarea>
        </div>
        <div class="col-md-6">
          <label class="form-label">Imagen</label>
          <input type="file" name="image" class="form-control" accept="image/*">
          @if($testimonial->image_path)
            <div class="mt-2"><img src="{{ $testimonial->image_path }}" alt="" style="height:80px;border-radius:8px"></div>
          @endif
        </div>
        <div class="col-md-3">
          <label class="form-label">Orden</label>
          <input type="number" name="order_index" class="form-control" min="0" value="{{ old('order_index', $testimonial->order_index) }}">
        </div>
        <div class="col-md-3 d-flex align-items-end">
          <div class="form-check">
            <input class="form-check-input" type="checkbox" name="is_active" id="is_active" {{ old('is_active', $testimonial->is_active) ? 'checked' : '' }}>
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



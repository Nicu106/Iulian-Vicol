@extends('layouts.admin')

@section('title','Admin • Mensajes de Contacto')

@section('content')
<div class="py-3">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0">Mensajes de contacto</h1>
  </div>

  <div class="card">
    <div class="table-responsive">
      <table class="table table-striped align-middle mb-0">
        <thead>
          <tr>
            <th>Fecha</th>
            <th>Nombre</th>
            <th>Telefon</th>
            <th>Email</th>
            <th>Asunto</th>
            <th>Mensaje</th>
          </tr>
        </thead>
        <tbody>
          @foreach($messages as $msg)
          <tr>
            <td>{{ $msg->created_at->format('d.m.Y H:i') }}</td>
            <td>{{ $msg->name }}</td>
            <td><a href="tel:{{ $msg->phone }}">{{ $msg->phone }}</a></td>
            <td>{{ $msg->email }}</td>
            <td>{{ $msg->subject }}</td>
            <td class="small text-muted" style="max-width: 420px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $msg->message }}</td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
    <div class="card-footer">
      {{ $messages->links('custom-pagination') }}
    </div>
  </div>
</div>
@endsection



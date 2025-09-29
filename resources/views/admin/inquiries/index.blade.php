@extends('layouts.admin')

@section('title','Admin • Solicitudes')

@section('content')
<div class="py-3">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0">Solicitudes de oferta / test drive</h1>
  </div>

  <div class="card mb-4">
    <div class="table-responsive">
      <table class="table table-striped align-middle mb-0">
        <thead>
          <tr>
            <th>Fecha</th>
            <th>Vehículo</th>
            <th>Cliente</th>
            <th>Teléfono</th>
            <th>Mensaje</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          @foreach($inquiries as $inq)
          <tr>
            <td>{{ $inq->created_at->format('d.m.Y H:i') }}</td>
            <td>
              <div class="fw-semibold">{{ $inq->vehicle_title }}</div>
              <a href="{{ $inq->vehicle_link }}" target="_blank">Ver página</a>
            </td>
            <td>{{ $inq->name }}</td>
            <td><a href="tel:{{ $inq->phone }}">{{ $inq->phone }}</a></td>
            <td class="small text-muted" style="max-width: 360px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
              {{ $inq->message }}
            </td>
            <td>
              <span class="badge bg-secondary text-uppercase">{{ $inq->status }}</span>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
    <div class="card-footer">
      {{ $inquiries->links('custom-pagination') }}
    </div>
  </div>

  <div class="card">
    <div class="card-header">Mensajes desde Contacto</div>
    <div class="table-responsive">
      <table class="table table-striped align-middle mb-0">
        <thead>
          <tr>
            <th>Data</th>
            <th>Nume</th>
            <th>Telefon</th>
            <th>Email</th>
            <th>Subiect</th>
            <th>Mesaj</th>
          </tr>
        </thead>
        <tbody>
          @foreach(($contactMessages ?? []) as $msg)
          <tr>
            <td>{{ $msg->created_at->format('d.m.Y H:i') }}</td>
            <td>{{ $msg->name }}</td>
            <td><a href="tel:{{ $msg->phone }}">{{ $msg->phone }}</a></td>
            <td>{{ $msg->email }}</td>
            <td>{{ $msg->subject }}</td>
            <td class="small text-muted" style="max-width: 360px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $msg->message }}</td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
    <div class="card-footer">
      {{ ($contactMessages ?? collect())->links('custom-pagination') }}
    </div>
  </div>
</div>
@endsection



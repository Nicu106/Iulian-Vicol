@extends('layouts.site')

@section('title', 'Términos y condiciones — IV MOTORCLASS')
@section('description', 'Quién está detrás de IV MOTORCLASS, para qué sirve esta web, precios, garantía, opiniones y reclamaciones.')
@section('current', '')
@php \App\Support\Schema::page('WebPage', [['Términos y condiciones', '/terminos']]); @endphp

@push('css')
<link rel="stylesheet" href="{{ asset('css/legal.css') }}">
@endpush

@section('content')
{{-- The site's legal notice (LSSI art. 10) and conditions of use — LegalController.
     Plain words, the reader as "tú", the business in the third person (it may be
     a person or a company). Every duty that applies is here; nothing promised
     that the business does not do. --}}
<main class="lg">
  <div class="cat-wrap">
    <article class="lg-in" aria-labelledby="lg-h">
      <h1 class="lg-h1" id="lg-h">Términos y condiciones</h1>
      <p class="lg-date">Actualizado el {{ $updated }}</p>

      <section class="lg-sec" aria-labelledby="t-who">
        <h2 id="t-who">Quién está detrás de esta web</h2>
        <p>Esta web, ivmotorclass.com, es de:</p>
        <dl class="lg-id">
          @if($holder)<div><dt>Titular</dt><dd>{{ $holder }}</dd></div>@endif
          <div><dt>Nombre comercial</dt><dd>IV MOTORCLASS</dd></div>
          @if($nif)<div><dt>NIF</dt><dd>{{ $nif }}</dd></div>@endif
          @if($address)<div><dt>Domicilio</dt><dd>{{ $address }}</dd></div>@endif
          @if($registry)<div><dt>Registro</dt><dd>{{ $registry }}</dd></div>@endif
          <div><dt>Actividad</dt><dd>Compraventa de coches de ocasión</dd></div>
          <div><dt>Teléfono y WhatsApp</dt><dd><a href="tel:{{ $tel }}">{{ $phone }}</a></dd></div>
          <div><dt>Email</dt><dd><a href="mailto:{{ $email }}">{{ $email }}</a></dd></div>
        </dl>
        <p>IV MOTORCLASS trabaja en Málaga sin exposición abierta al público: los coches
          se ven con cita previa, donde esté el coche.</p>
      </section>

      <section class="lg-sec" aria-labelledby="t-what">
        <h2 id="t-what">Para qué sirve esta web</h2>
        <p>Es un escaparate. Enseña los coches que están a la venta y cómo ponerte en
          contacto. En esta web no se compra ni se paga nada: la compra de un coche se hace
          siempre con un contrato por escrito, y lo que vale es lo que dice ese contrato.</p>
        <p>Usarla es gratis y no hace falta registrarse. Al usarla aceptas estas condiciones;
          si no estás de acuerdo con ellas, no la uses.</p>
      </section>

      <section class="lg-sec" aria-labelledby="t-price">
        <h2 id="t-price">Precios y datos de los coches</h2>
        <p>Los precios que ves son precios finales, con los impuestos incluidos. Los coches
          de ocasión se venden normalmente en el régimen especial de bienes usados (REBU):
          el IVA va incluido en el precio y no aparece desglosado en la factura. Si compras
          como empresa y necesitas el IVA deducible, pregúntalo antes.</p>
        <p>Antes de cerrar nada te digo por escrito el precio total, con la transferencia y
          cualquier otro gasto que haya.</p>
        <p>Los datos de cada coche (kilómetros, año, equipamiento, fotos) se ponen con
          cuidado, pero puede haber errores. Los que valen son los de la documentación del
          coche y los del contrato. Un coche puede venderse o reservarse antes de que la web
          lo diga.</p>
        <p class="lg-legend">Este establecimiento comercial posee, a disposición del
          consumidor que lo solicite, un documento individualizado de cada vehículo expuesto,
          en el que se indican, entre otros datos, las características del mismo, su
          titularidad, la garantía que, en su caso, posea y su precio.</p>
      </section>

      <section class="lg-sec" aria-labelledby="t-warranty">
        <h2 id="t-warranty">Garantía</h2>
        <p>Si compras como consumidor, el coche tiene la garantía que da la ley. En un coche
          de segunda mano, el contrato puede fijar un plazo más corto que el general de tres
          años, pero nunca inferior a un año. El plazo de cada coche aparece en su ficha y en
          el contrato.</p>
        <p>Si quieres más tiempo, la garantía se puede ampliar. Las condiciones de la
          ampliación (qué cubre, dónde y cuánto cuesta) te las doy por escrito antes de que
          la contrates.</p>
      </section>

      <section class="lg-sec" aria-labelledby="t-book">
        <h2 id="t-book">Reservas</h2>
        <p>Si reservas un coche con una señal, se pone por escrito cuánto es, hasta cuándo
          dura la reserva y qué pasa con el dinero si una de las dos partes no sigue
          adelante.</p>
      </section>

      <section class="lg-sec" aria-labelledby="t-rights">
        <h2 id="t-rights">Tus derechos como consumidor</h2>
        <p>Nada de lo que dice esta web limita los derechos que te da la ley si compras como
          consumidor: la garantía, el derecho a desistir del contrato cuando la ley lo
          reconoce y el de reclamar.</p>
      </section>

      <section class="lg-sec" aria-labelledby="t-reviews">
        <h2 id="t-reviews">Opiniones de clientes</h2>
        <p>Las opiniones que salen en la web son de personas que han comprado o vendido un
          coche a IV MOTORCLASS. Llegan en persona o por un enlace personal que se manda al
          cliente después de la entrega. Se leen antes de publicarlas y se publican con las
          palabras de quien las escribe, con su nombre y, si quiere, con su foto. No se
          publican opiniones de personas que no han sido clientes.</p>
      </section>

      <section class="lg-sec" aria-labelledby="t-refer">
        <h2 id="t-refer">Recomienda a un amigo</h2>
        <p>Recomendar es gratis y voluntario. Si en algún momento hay una recompensa por
          recomendar, sus condiciones (quién la recibe, por qué compra, cuándo y cómo) se
          publicarán en la página <a href="/recomienda">Recomienda a un amigo</a> antes de
          aplicarse. Mientras esa página no diga nada, no hay ninguna recompensa
          prometida.</p>
      </section>

      <section class="lg-sec" aria-labelledby="t-claims">
        <h2 id="t-claims">Si algo no ha ido bien</h2>
        <p>Escríbeme primero: por WhatsApp o por teléfono al <a href="tel:{{ $tel }}">{{ $phone }}</a>,
          por email a <a href="mailto:{{ $email }}">{{ $email }}</a>@if($address) o por correo
          postal a {{ $address }}@endif. Contesto a cualquier reclamación en un plazo máximo
          de quince días.</p>
        <p class="lg-legend">Existen hojas de quejas y reclamaciones a disposición de las
          personas consumidoras y usuarias, en papel y en formato electrónico.</p>
        <p>Si no llegamos a un acuerdo, puedes acudir a los servicios de consumo de la Junta
          de Andalucía (<a href="https://www.consumoresponde.es" rel="noopener" target="_blank">Consumo Responde</a>,
          900 21 50 80) o a la oficina municipal de información al consumidor de tu
          ayuntamiento.</p>
      </section>

      <section class="lg-sec" aria-labelledby="t-ip">
        <h2 id="t-ip">Fotos, textos y diseño</h2>
        <p>Las fotos, los vídeos, los textos y el diseño de esta web son de IV MOTORCLASS o
          se usan con permiso de sus autores. Puedes compartir los enlaces todo lo que
          quieras; lo que no puedes es copiar las fotos o los textos para usarlos en otro
          sitio sin permiso.</p>
      </section>

      <section class="lg-sec" aria-labelledby="t-links">
        <h2 id="t-links">Enlaces a otros sitios</h2>
        <p>La web enlaza con WhatsApp, Google Maps, YouTube, Instagram, TikTok y Facebook.
          Al abrirlos sales de ivmotorclass.com y se aplican las condiciones de esos
          servicios.</p>
      </section>

      <section class="lg-sec" aria-labelledby="t-law">
        <h2 id="t-law">Ley aplicable</h2>
        <p>Estas condiciones se rigen por la ley española. Si eres consumidor, puedes
          reclamar ante los juzgados que la ley te reconoce, entre ellos los de tu
          domicilio.</p>
        <p>Cómo se tratan tus datos personales y qué cookies usa la web está en la
          <a href="/privacidad">política de privacidad</a>.</p>
      </section>
    </article>
  </div>
</main>
@endsection

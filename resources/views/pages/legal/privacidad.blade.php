@extends('layouts.site')

@section('title', 'Política de privacidad y cookies — IV MOTORCLASS')
@section('description', 'Qué datos personales trata IV MOTORCLASS, para qué, cuánto tiempo, con quién y qué cookies usa la web.')
@section('current', '')
@php \App\Support\Schema::page('WebPage', [['Política de privacidad', '/privacidad']]); @endphp

@push('css')
<link rel="stylesheet" href="{{ asset('css/legal.css') }}">
@endpush

@section('content')
{{-- Privacy (GDPR art. 13, LOPDGDD art. 11: a short first layer, then the rest)
     and cookies (LSSI art. 22.2) — LegalController. Each processing below was
     read from the code that does it, 2026-10-29: the forms (SellCarRequest,
     ReferralRequest, OpinionController), App\Support\Stats, Referral, Journey,
     the session config and nginx's log rotation. If one of those changes, this
     page changes with it. --}}
<main class="lg">
  <div class="cat-wrap">
    <article class="lg-in" aria-labelledby="lg-h">
      <h1 class="lg-h1" id="lg-h">Política de privacidad y cookies</h1>
      <p class="lg-date">Actualizada el {{ $updated }}</p>

      <section class="lg-sum" aria-labelledby="p-short">
        <h2 id="p-short">En pocas palabras</h2>
        <dl class="lg-id">
          <div><dt>Quién</dt><dd>{{ $who }}. <a href="mailto:{{ $email }}">{{ $email }}</a> · <a href="tel:{{ $tel }}">{{ $phone }}</a></dd></div>
          <div><dt>Para qué</dt><dd>Contestarte, valorar el coche que quieres vender, preparar la compra o la venta, publicar tu opinión si tú quieres y contar visitas sin saber quién eres.</dd></div>
          <div><dt>Por qué puedo</dt><dd>Porque tú lo pides, por el contrato, porque la ley obliga a guardar algunos datos, por tu consentimiento (las opiniones) y por interés legítimo (las estadísticas y la seguridad).</dd></div>
          <div><dt>Con quién</dt><dd>Solo con quien hace falta para dar el servicio: el proveedor del servidor, el del correo y, si hay compraventa, la gestoría y las administraciones. Nunca para publicidad y nunca se venden.</dd></div>
          <div><dt>Tus derechos</dt><dd>Ver tus datos, corregirlos, borrarlos, oponerte, limitar su uso y llevártelos. Y reclamar a la Agencia Española de Protección de Datos.</dd></div>
        </dl>
      </section>

      <section class="lg-sec" aria-labelledby="p-who">
        <h2 id="p-who">Quién trata tus datos</h2>
        <p>El responsable es {{ $who }}{{ $nif ? ', con NIF ' . $nif : '' }}{{ $address ? ', con domicilio en ' . $address : '' }}.
          Para cualquier cosa sobre tus datos: <a href="mailto:{{ $email }}">{{ $email }}</a>
          o el <a href="tel:{{ $tel }}">{{ $phone }}</a>, también por WhatsApp.</p>
      </section>

      <section class="lg-sec" aria-labelledby="p-what">
        <h2 id="p-what">Qué datos, para qué y cuánto tiempo</h2>

        <h3>Si me escribes o me llamas</h3>
        <p>Lo que me cuentes por WhatsApp, por teléfono o por email: tu nombre, tu número o
          tu dirección de correo y lo que me preguntas. Lo uso para contestarte y, si quieres
          un coche, para enseñártelo y preparar la compra. La base es tu propia petición
          (medidas previas a un contrato). Se guarda mientras hablamos; si no hay compra, lo
          borro cuando ya no hace falta.</p>

        <h3>Si me ofreces tu coche («Vende tu coche»)</h3>
        <p>Tu nombre, tu teléfono, tu email si lo das, los datos del coche y sus fotos. Los
          uso para valorar el coche y contestarte. La base es tu petición. Si no llegamos a
          un acuerdo, borro los datos y las fotos cuando ya no hacen falta para contestarte.
          Si compro el coche, puedo usar esas fotos para anunciarlo.</p>

        <h3>Si pides un enlace para recomendar («Recomienda a un amigo»)</h3>
        <p>Tu nombre y tu teléfono, para darte tu enlace personal y saber que alguien llegó de
          tu parte. La base es tu petición de participar. Se guardan mientras quieras
          participar: pídeme la baja cuando quieras. Lo que pasa cuando alguien abre tu
          enlace está más abajo, en las cookies.</p>

        <h3>Si escribes tu opinión</h3>
        <p>Tu nombre, lo que escribes y la foto si la mandas. Se publican en esta web porque
          tú lo aceptas al enviarla, y siguen publicados hasta que me pidas quitarlos, cosa
          que puedes hacer en cualquier momento sin dar explicaciones. La foto se guarda sin
          los datos internos que añade el móvil (por ejemplo, la ubicación).</p>

        <h3>Si compras o me vendes un coche</h3>
        <p>Los datos del contrato, de la factura y de la transferencia del coche. La base es
          el contrato y las obligaciones legales. Se guardan el tiempo que exige la ley: seis
          años los documentos del negocio (Código de Comercio) y cuatro los fiscales; y hasta
          cinco años mientras pueda haber reclamaciones.</p>

        <h3>Estadísticas, sin cookies</h3>
        <p>La web cuenta cuántas personas la visitan, qué páginas y coches miran, cuánto
          tiempo, hasta dónde bajan, qué fotos ven, desde qué tipo de dispositivo, de qué
          ciudad aproximada y cuántas pulsan WhatsApp, llamar o escribir. Lo hace sin cookies
          y sin guardar tu dirección IP: con la IP se calcula en el momento la ciudad (con una
          base de datos propia, sin consultar a nadie) y un código anónimo que cambia cada
          día y se borra a los dos días, solo para no contar dos veces a la misma persona el
          mismo día. Nadie puede saber quién eres ni seguirte de un día a otro. El detalle de
          cada visita se borra a los 30 días; quedan solo los totales. La base es el interés
          legítimo en saber qué funciona en la web. Nunca se usa para publicidad.</p>

        <h3>Seguridad</h3>
        <p>El servidor anota cada petición (dirección IP, fecha, página pedida y navegador)
          para defender la web de abusos. Esos registros se borran solos a los 14 días. La
          base es el interés legítimo en la seguridad.</p>
      </section>

      <section class="lg-sec" aria-labelledby="p-whom">
        <h2 id="p-whom">Con quién se comparten</h2>
        <ul>
          <li><b>Contabo GmbH</b> (Alemania), el servidor donde vive la web y sus datos, como
            encargado del tratamiento.</li>
          <li><b>Google</b>, el servicio de correo electrónico, si me escribes por email.</li>
          <li><b>WhatsApp (Meta)</b>, si me escribes por WhatsApp: la conversación pasa por su
            servicio, con sus propias condiciones.</li>
          <li><b>YouTube (Google)</b>: si un coche tiene vídeo, su imagen de portada se carga
            desde YouTube, y el vídeo solo se reproduce, desde youtube-nocookie.com, si
            pulsas para verlo.</li>
          <li>Si hay compraventa: la gestoría que hace la transferencia, la DGT, la Agencia
            Tributaria y, si financias, la entidad financiera que elijas.</li>
        </ul>
        <p>Google y Meta pueden tratar datos en Estados Unidos. Lo hacen al amparo del Marco
          de Privacidad de Datos UE-EE. UU. (decisión de adecuación de la Comisión Europea de
          10 de julio de 2023) y, en su caso, de las cláusulas contractuales tipo.</p>
        <p>Tus datos no se venden ni se ceden a nadie para publicidad.</p>
      </section>

      <section class="lg-sec" aria-labelledby="p-rights">
        <h2 id="p-rights">Tus derechos</h2>
        <p>Puedes pedir ver los datos que tengo de ti, corregirlos, borrarlos, oponerte a que
          los use, limitar su uso, llevártelos y retirar tu consentimiento cuando quieras (sin
          que eso afecte a lo hecho antes). Escríbeme a <a href="mailto:{{ $email }}">{{ $email }}</a>
          o por WhatsApp diciendo qué quieres; si hace falta comprobar que eres tú, te lo
          diré. Contesto en un mes como máximo.</p>
        <p>Si crees que no he tratado bien tus datos, puedes reclamar ante la Agencia
          Española de Protección de Datos, en <a href="https://www.aepd.es" rel="noopener" target="_blank">aepd.es</a>.</p>
        <p>Esta web no está pensada para menores de 14 años y no se toman decisiones
          automáticas sobre nadie.</p>
      </section>

      <section class="lg-sec" id="cookies" aria-labelledby="p-cookies">
        <h2 id="p-cookies">Cookies</h2>
        <p>Una cookie es un pequeño archivo que la web guarda en tu navegador. Esta web no
          usa cookies de publicidad ni de terceros. Usa estas, todas propias:</p>
        <div class="lg-cookies">
          <dl>
            <dt>motorclass_v2_session</dt>
            <dd>Hace que funcionen los formularios mientras navegas. Técnica. Dura 2 horas.</dd>
          </dl>
          <dl>
            <dt>XSRF-TOKEN</dt>
            <dd>Protege los formularios frente a envíos falsos. Técnica. Dura 2 horas.</dd>
          </dl>
          <dl>
            <dt>mc_ref</dt>
            <dd>Solo si abres el enlace con el que alguien te recomienda: guarda su código para
              que, si me escribes por WhatsApp, sepa de parte de quién vienes. Dura 90 días.</dd>
          </dl>
          <dl>
            <dt>mc_rv</dt>
            <dd>Solo si abres un enlace de recomendación: un número al azar, sin tu nombre ni tu
              IP, para saber qué páginas y coches mira quien llegó por ese enlace. Dura 90
              días y lo anotado se borra a los 90 días de la última visita.</dd>
          </dl>
          <dl>
            <dt>mc_staff</dt>
            <dd>Solo en los dispositivos del equipo de IV MOTORCLASS, para que sus visitas no
              se cuenten en las estadísticas. Dura 5 años.</dd>
          </dl>
        </div>
        <p>Puedes ver y borrar las cookies desde la configuración de tu navegador; si borras
          las técnicas, puede que algún formulario no funcione hasta que vuelvas a cargar la
          página.</p>
      </section>

      <section class="lg-sec" aria-labelledby="p-changes">
        <h2 id="p-changes">Cambios</h2>
        <p>Si cambia algo de lo que dice esta página, se cambia aquí y se pone la fecha
          arriba. Las condiciones de uso de la web están en
          <a href="/terminos">términos y condiciones</a>.</p>
      </section>
    </article>
  </div>
</main>
@endsection

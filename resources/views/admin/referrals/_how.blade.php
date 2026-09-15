{{-- How recommendations work, in the owner's words. Shown in the "Cómo
     funciona" popup and on /admin/recomendaciones/como-funciona. --}}
<ol class="ad-steps">
  <li><span><b>Se crea un enlace para una persona.</b> Tú, desde aquí, con «Crear enlace»
    — lo normal es después de venderle un coche. O la propia persona, en la web, en
    «Recomienda a un amigo». Sólo se guardan su nombre y su teléfono.</span></li>
  <li><span><b>La persona se lo manda a quien quiera.</b> Por WhatsApp, normalmente.
    El enlace lleva un código, por ejemplo <b>MARIA7K2</b>, y ningún dato personal.</span></li>
  <li><span><b>El amigo abre el enlace.</b> La web lo recuerda durante
    {{ \App\Support\Referral::days() }} días. Si te escribe por WhatsApp desde la web, el
    mensaje ya lleva el código y lo verás en la conversación.</span></li>
  <li><span><b>Cuando le vendes un coche,</b> abre la ficha del coche y elige a la persona
    en «Vino de parte de». Se crea un premio pendiente.</span></li>
  <li><span><b>Tú decides.</b> Apruebas el premio y, cuando lo das, lo marcas como
    pagado.</span></li>
</ol>

<h3 class="ad-h2">Reglas</h3>
<ul class="ad-rules">
  <li>Cuenta el primer enlace que abrió el amigo. Uno abierto después no lo cambia.</li>
  <li>Un premio por coche vendido. Nunca por un clic o por rellenar un formulario.</li>
  <li>No hay premio por la compra de la propia persona.</li>
  <li>No se piden ni se guardan los datos del amigo.</li>
  <li>Un enlace que ya tiene premios no se puede borrar, para no perder el registro.</li>
</ul>

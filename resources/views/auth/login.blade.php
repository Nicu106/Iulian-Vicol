<x-guest-layout>
  <form method="POST" action="{{ route('login') }}" class="ad-form" style="gap:var(--s-5)">
    @csrf

    @if($errors->any())
      <p class="ad-flash ad-flash--bad" style="margin:0">{{ $errors->first() }}</p>
    @elseif(session('status'))
      {{-- e.g. the car form's session ran out (bootstrap/app.php, the 419 answer) --}}
      <p class="ad-flash" style="margin:0">{{ session('status') }}</p>
    @endif

    <label class="ad-field">
      <span>Correo</span>
      <input class="ad-in {{ $errors->has('email') ? 'is-bad' : '' }}" id="email" type="email" name="email"
             value="{{ old('email') }}" required autofocus autocomplete="username">
    </label>

    <label class="ad-field">
      <span>Contraseña</span>
      <input class="ad-in {{ $errors->has('password') ? 'is-bad' : '' }}" id="password" type="password"
             name="password" required autocomplete="current-password">
    </label>

    <label class="ad-check">
      <input id="remember_me" type="checkbox" name="remember">
      <span>No cerrar la sesión</span>
    </label>

    <button class="ad-btn" type="submit" style="width:100%">Entrar</button>
  </form>
</x-guest-layout>

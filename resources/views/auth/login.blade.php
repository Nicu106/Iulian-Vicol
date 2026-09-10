<x-guest-layout>
  <form method="POST" action="{{ route('login') }}" class="ad-form" style="gap:var(--s-5)">
    @csrf

    @if($errors->any())
      <p class="ad-flash ad-flash--bad" style="margin:0">{{ $errors->first() }}</p>
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

    @if (Route::has('password.request'))
      <a class="ad-gate__lost" href="{{ route('password.request') }}">He olvidado la contraseña</a>
    @endif
  </form>
</x-guest-layout>

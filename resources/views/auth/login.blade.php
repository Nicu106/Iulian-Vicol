<x-guest-layout>
  <form method="POST" action="{{ route('login') }}" class="">
    @csrf

    <div class="mb-3">
      <label for="email" class="form-label fw-semibold">Email</label>
      <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" class="form-control">
      @if($errors->has('email'))
        <div class="text-danger small mt-1">{{ $errors->first('email') }}</div>
      @endif
    </div>

    <div class="mb-3">
      <label for="password" class="form-label fw-semibold">Contraseña</label>
      <input id="password" type="password" name="password" required autocomplete="current-password" class="form-control">
      @if($errors->has('password'))
        <div class="text-danger small mt-1">{{ $errors->first('password') }}</div>
      @endif
    </div>

    <div class="d-flex justify-content-between align-items-center mb-3">
      <div class="form-check">
        <input id="remember_me" type="checkbox" name="remember" class="form-check-input">
        <label class="form-check-label" for="remember_me">Recordarme</label>
      </div>
      @if (Route::has('password.request'))
        <a class="small" href="{{ route('password.request') }}">¿Olvidaste tu contraseña?</a>
      @endif
    </div>

    <button class="btn btn-primary w-100 py-2">Acceder</button>
  </form>
</x-guest-layout>

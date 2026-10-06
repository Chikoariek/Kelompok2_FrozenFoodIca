<x-guest-layout>
    <!-- Session Status -->
    @if (session('status'))
        <div class="alert-error">{{ session('status') }}</div>
    @endif

    <!-- Validation Errors -->
    @if ($errors->any())
        <div class="alert-error">
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email -->
        <div class="form-group">
            <label class="form-label" for="email">Email</label>
            <div class="form-input-wrap">
                <input id="email" type="email" name="email" value="{{ old('email') }}" class="form-input"
                    placeholder="Masukan email anda" required autofocus autocomplete="username">
                <span class="form-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                        stroke="#677D9E" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2" />
                        <circle cx="12" cy="7" r="4" />
                    </svg>
                </span>
            </div>
        </div>

        <!-- Password -->
        <div class="form-group">
            <label class="form-label" for="password">Password</label>
            <div class="form-input-wrap">
                <input id="password" type="password" name="password" class="form-input"
                    placeholder="Masukan password anda" required autocomplete="current-password">
                <button type="button" id="togglePassword" class="form-toggle-btn" title="Lihat / Sembunyikan Password"
                    aria-label="Lihat / Sembunyikan Password">
                    <!-- Eye Icon (default saat password tersembunyi) -->
                    <svg id="eyeIcon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                        fill="none" stroke="#677D9E" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z" />
                        <circle cx="12" cy="12" r="3" />
                    </svg>
                    <!-- Eye Off Icon (saat password terlihat) -->
                    <svg id="eyeOffIcon" style="display: none;" xmlns="http://www.w3.org/2000/svg" width="18"
                        height="18" viewBox="0 0 24 24" fill="none" stroke="#677D9E" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9.88 9.88a3 3 0 1 0 4.24 4.24" />
                        <path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68" />
                        <path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61" />
                        <line x1="2" x2="22" y1="2" y2="22" />
                    </svg>
                </button>
            </div>
        </div>



        <!-- Submit Button -->
        <button type="submit" class="btn-login">
            <span>Login</span>
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"
                style="display: inline-block; vertical-align: middle; margin-left: 6px;">
                <line x1="5" y1="12" x2="19" y2="12" />
                <polyline points="12 5 19 12 12 19" />
            </svg>
        </button>

        <!-- Register Link -->
        <p class="login-register">
            Belum punya akun? <a href="{{ route('register') }}">Daftar di sini</a>
        </p>
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const toggleBtn = document.getElementById('togglePassword');
            const passwordInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eyeIcon');
            const eyeOffIcon = document.getElementById('eyeOffIcon');

            if (toggleBtn && passwordInput && eyeIcon && eyeOffIcon) {
                toggleBtn.addEventListener('click', function (e) {
                    e.preventDefault();
                    const isPassword = passwordInput.getAttribute('type') === 'password';
                    passwordInput.setAttribute('type', isPassword ? 'text' : 'password');
                    if (isPassword) {
                        eyeIcon.style.display = 'none';
                        eyeOffIcon.style.display = 'block';
                    } else {
                        eyeIcon.style.display = 'block';
                        eyeOffIcon.style.display = 'none';
                    }
                });
            }
        });
    </script>
</x-guest-layout>
<x-guest-layout title="Daftar Akun Baru" subtitle="Buat akun untuk mulai berbelanja di Ica Frozen Food">
    <!-- Validation Errors -->
    @if ($errors->any())
        <div class="alert-error">
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <!-- Name -->
        <div class="form-group">
            <label class="form-label" for="name">Nama Lengkap</label>
            <div class="form-input-wrap">
                <input
                    id="name"
                    type="text"
                    name="name"
                    value="{{ old('name') }}"
                    class="form-input"
                    placeholder="Masukan nama lengkap anda"
                    required
                    autofocus
                    autocomplete="name"
                >
                <span class="form-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#677D9E" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/>
                        <circle cx="12" cy="7" r="4"/>
                    </svg>
                </span>
            </div>
            <x-input-error :messages="$errors->get('name')" class="mt-1.5" />
        </div>

        <!-- Email -->
        <div class="form-group">
            <label class="form-label" for="email">Email</label>
            <div class="form-input-wrap">
                <input
                    id="email"
                    type="email"
                    name="email"
                    value="{{ old('email') }}"
                    class="form-input"
                    placeholder="Masukan email anda"
                    required
                    autocomplete="username"
                >
                <span class="form-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#677D9E" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect width="20" height="16" x="2" y="4" rx="2"/>
                        <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>
                    </svg>
                </span>
            </div>
            <x-input-error :messages="$errors->get('email')" class="mt-1.5" />
        </div>

        <!-- Password -->
        <div class="form-group">
            <label class="form-label" for="password">Password</label>
            <div class="form-input-wrap">
                <input
                    id="password"
                    type="password"
                    name="password"
                    class="form-input"
                    placeholder="Masukan password anda (min. 8 karakter)"
                    required
                    autocomplete="new-password"
                >
                <button
                    type="button"
                    id="togglePassword"
                    class="form-toggle-btn"
                    title="Lihat / Sembunyikan Password"
                    aria-label="Lihat / Sembunyikan Password"
                >
                    <svg id="eyeIcon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#677D9E" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/>
                        <circle cx="12" cy="12" r="3"/>
                    </svg>
                    <svg id="eyeOffIcon" style="display: none;" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#677D9E" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/>
                        <path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/>
                        <path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/>
                        <line x1="2" x2="22" y1="2" y2="22"/>
                    </svg>
                </button>
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-1.5" />
        </div>

        <!-- Confirm Password -->
        <div class="form-group">
            <label class="form-label" for="password_confirmation">Konfirmasi Password</label>
            <div class="form-input-wrap">
                <input
                    id="password_confirmation"
                    type="password"
                    name="password_confirmation"
                    class="form-input"
                    placeholder="Ulangi password anda"
                    required
                    autocomplete="new-password"
                >
                <button
                    type="button"
                    id="toggleConfirmPassword"
                    class="form-toggle-btn"
                    title="Lihat / Sembunyikan Password"
                    aria-label="Lihat / Sembunyikan Password"
                >
                    <svg id="eyeIconConfirm" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#677D9E" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/>
                        <circle cx="12" cy="12" r="3"/>
                    </svg>
                    <svg id="eyeOffIconConfirm" style="display: none;" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#677D9E" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/>
                        <path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/>
                        <path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/>
                        <line x1="2" x2="22" y1="2" y2="22"/>
                    </svg>
                </button>
            </div>
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1.5" />
        </div>

        <!-- Submit Button -->
        <button type="submit" class="btn-login" style="margin-top: 1.25rem;">Daftar Akun Baru</button>

        <!-- Login Link -->
        <p class="login-register">
            Sudah punya akun? <a href="{{ route('login') }}">Masuk di sini</a>
        </p>
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            function setupPasswordToggle(buttonId, inputId, eyeId, eyeOffId) {
                const btn = document.getElementById(buttonId);
                const input = document.getElementById(inputId);
                const eye = document.getElementById(eyeId);
                const eyeOff = document.getElementById(eyeOffId);

                if (btn && input && eye && eyeOff) {
                    btn.addEventListener('click', function (e) {
                        e.preventDefault();
                        if (input.type === 'password') {
                            input.type = 'text';
                            eye.style.display = 'none';
                            eyeOff.style.display = 'block';
                        } else {
                            input.type = 'password';
                            eye.style.display = 'block';
                            eyeOff.style.display = 'none';
                        }
                    });
                }
            }

            setupPasswordToggle('togglePassword', 'password', 'eyeIcon', 'eyeOffIcon');
            setupPasswordToggle('toggleConfirmPassword', 'password_confirmation', 'eyeIconConfirm', 'eyeOffIconConfirm');
        });
    </script>
</x-guest-layout>

{{-- overlay gelap di belakang modal --}}
<div
    id="modal-login-overlay"
    class="hidden fixed inset-0 bg-black/40 z-50 flex items-center justify-center"
    onclick="handleOverlayClick(event)"
>
    {{-- modal --}}
    <div class="bg-white rounded-2xl w-full max-w-md mx-4 overflow-hidden shadow-2xl">

        {{-- header --}}
        <div class="flex items-center justify-between px-6 py-2 border-b border-gray-300">
            <div class="flex items-center">
                <img
                    src="{{ asset('images/logo-ict.png') }}"
                    alt="Logo ICT"
                    class="h-10 w-35 object-contain"
                >
            </div>

            <button
                type="button"
                onclick="closeModal()"
                class="text-abumuda hover:text-gray-600 text-2xl leading-none"
            >
                &times;
            </button>
        </div>

        {{-- body --}}
        <div class="px-8 py-8">
            <h2 class="text-3xl font-bold text-birutua text-center mb-8 tracking-wide">
                LOGIN
            </h2>

            {{-- gausah pake ginian lh. udah disediain juga yg lbih keren --}}
            {{-- @if ($errors->any())
                <div class="mb-4 rounded-lg bg-red-100 px-4 py-2 text-sm text-failed">
                    Username atau password tidak sesuai.
                </div>
            @endif --}}

            @if (session('status'))
                <div class="mb-4 rounded-lg bg-green-100 px-4 py-3 text-sm text-green-700">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" data-loading="true">
                @csrf

                {{-- usn --}}
                <div class="mb-5">
                    <div class="flex items-center border border-gray-300 rounded-xl px-4 py-2 gap-3 focus-within:border-birumuda transition">
                        <svg class="w-5 h-5 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="1.5"
                                d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"
                            />
                        </svg>

                        <input
                            type="text"
                            name="username"
                            value="{{ old('username') }}"
                            placeholder="Enter your username..."
                            class="flex-1 outline-none text-gray-700 bg-transparent text-md"
                            required
                            autofocus
                            autocomplete="username"
                        />
                    </div>

                    @error('username')
                        <p class="text-failed text-xs mt-1 ml-1">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- pw --}}
                <div class="mb-8">
                    <div class="flex items-center border border-gray-300 rounded-xl px-4 py-2 gap-3 focus-within:border-birumuda transition">
                        <svg class="w-5 h-5 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="1.5"
                                d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"
                            />
                        </svg>

                        <input
                            id="password-input"
                            type="password"
                            name="password"
                            placeholder="Enter your password..."
                            class="flex-1 outline-none text-gray-700 bg-transparent text-md"
                            required
                            autocomplete="current-password"
                        />

                        <button
                            type="button"
                            onclick="togglePassword()"
                            class="text-gray-400 hover:text-gray-600 transition"
                        >
                            {{-- icon eye: password sedang hidden --}}
                            <svg id="eye-open-icon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="1.5"
                                    d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.964-7.178z"
                                />
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="1.5"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                                />
                            </svg>

                            {{-- icon eye slash: password sedang terlihat --}}
                            <svg id="eye-slash-icon" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="1.5"
                                    d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c1.657 0 3.223-.381 4.617-1.06M6.228 6.228A10.451 10.451 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88"
                                />
                            </svg>
                        </button>
                    </div>

                    @error('password')
                        <p class="text-failed text-xs mt-1 ml-1">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- btn Cancel + Login --}}
                <div class="flex gap-3">
                    <button
                        type="button"
                        onclick="closeModal()"
                        class="flex-1 border border-gray-300 text-gray-500 py-2 px-10 rounded-lg font-medium hover:bg-gray-100 transition"
                    >
                        Cancel
                    </button>

                    <button
                        type="submit"
                        class="flex-1 bg-birutua text-white py-2 px-10 rounded-lg font-medium hover:bg-gradient transition"
                    >
                        Login
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function openModal() {
        document.getElementById('modal-login-overlay').classList.remove('hidden');
    }

    function closeModal() {
        document.getElementById('modal-login-overlay').classList.add('hidden');
    }

    function handleOverlayClick(event) {
        const overlay = document.getElementById('modal-login-overlay');

        if (event.target === overlay) {
            closeModal();
        }
    }

    function togglePassword() {
        const input = document.getElementById('password-input');
        const eyeOpenIcon = document.getElementById('eye-open-icon');
        const eyeSlashIcon = document.getElementById('eye-slash-icon');

        const isPasswordHidden = input.type === 'password';

        input.type = isPasswordHidden ? 'text' : 'password';

        eyeOpenIcon.classList.toggle('hidden', isPasswordHidden);
        eyeSlashIcon.classList.toggle('hidden', !isPasswordHidden);
    }
</script>
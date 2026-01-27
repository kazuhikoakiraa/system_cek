<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - {{ config('app.name') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .password-strength-bar {
            height: 4px;
            transition: all 0.3s ease;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-blue-50 to-indigo-100">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full">
            <!-- Logo atau Icon -->
            <div class="text-center mb-8">
                <div class="mx-auto h-16 w-16 bg-indigo-600 rounded-full flex items-center justify-center">
                    <svg class="h-10 w-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                    </svg>
                </div>
            </div>

            <div class="bg-white py-8 px-6 shadow-xl rounded-lg sm:px-10">
                <div class="mb-6">
                    <h2 class="text-center text-3xl font-extrabold text-gray-900">
                        Reset Password
                    </h2>
                    <p class="mt-2 text-center text-sm text-gray-600">
                        Buat password baru untuk akun Anda
                    </p>
                </div>

                @if (session('status'))
                    <div class="mb-4 p-4 bg-green-50 border-l-4 border-green-400 rounded">
                        <p class="text-sm text-green-700">{{ session('status') }}</p>
                    </div>
                @endif

                @if ($errors->any() && !$errors->has('email') && !$errors->has('password'))
                    <div class="mb-4 p-4 bg-red-50 border-l-4 border-red-400 rounded">
                        <p class="text-sm text-red-700">Link reset password tidak valid atau sudah kedaluwarsa. Silakan minta link baru.</p>
                    </div>
                @endif

                <form class="space-y-6" action="{{ route('password.update') }}" method="POST" id="resetForm">
                    @csrf
                    <input type="hidden" name="token" value="{{ $token }}">
                    
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                            Email
                        </label>
                        <input id="email" name="email" type="email" required readonly
                            class="appearance-none block w-full px-3 py-2.5 border border-gray-300 rounded-lg bg-gray-50 text-gray-700 text-sm focus:outline-none"
                            value="{{ old('email', request('email')) }}">
                        @error('email')
                            <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
                            Password Baru
                        </label>
                        <div class="relative">
                            <input id="password" name="password" type="password" required 
                                class="appearance-none block w-full px-3 py-2.5 pr-10 border border-gray-300 rounded-lg placeholder-gray-400 text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent sm:text-sm"
                                placeholder="Minimal 8 karakter"
                                oninput="checkPasswordStrength()">
                            <button type="button" onclick="togglePassword('password')" 
                                class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                <svg id="password-eye-icon" class="h-5 w-5 text-gray-400 hover:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </button>
                        </div>
                        
                        <!-- Password Strength Indicator -->
                        <div class="mt-2">
                            <div class="password-strength-bar rounded-full bg-gray-200" id="strengthBar"></div>
                            <p class="mt-1 text-xs text-gray-600" id="strengthText">Kekuatan password</p>
                        </div>

                        <div class="mt-2 text-xs text-gray-500 space-y-1">
                            <p>Password harus mengandung:</p>
                            <ul class="list-disc list-inside space-y-0.5 ml-2">
                                <li id="length-check" class="text-gray-400">Minimal 8 karakter</li>
                                <li id="uppercase-check" class="text-gray-400">Huruf besar (A-Z)</li>
                                <li id="lowercase-check" class="text-gray-400">Huruf kecil (a-z)</li>
                                <li id="number-check" class="text-gray-400">Angka (0-9)</li>
                            </ul>
                        </div>

                        @error('password')
                            <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">
                            Konfirmasi Password
                        </label>
                        <div class="relative">
                            <input id="password_confirmation" name="password_confirmation" type="password" required 
                                class="appearance-none block w-full px-3 py-2.5 pr-10 border border-gray-300 rounded-lg placeholder-gray-400 text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent sm:text-sm"
                                placeholder="Ketik ulang password"
                                oninput="checkPasswordMatch()">
                            <button type="button" onclick="togglePassword('password_confirmation')" 
                                class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                <svg id="password_confirmation-eye-icon" class="h-5 w-5 text-gray-400 hover:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </button>
                        </div>
                        <p class="mt-1.5 text-xs text-gray-600" id="matchText"></p>
                    </div>

                    <div class="pt-2">
                        <button type="submit" id="submitBtn"
                            class="group relative w-full flex justify-center py-2.5 px-4 border border-transparent text-sm font-medium rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200">
                            <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                            </svg>
                            Reset Password
                        </button>
                    </div>

                    <div class="text-center">
                        <a href="{{ route('filament.admin.auth.login') }}" class="text-sm text-indigo-600 hover:text-indigo-500 font-medium">
                            ← Kembali ke Login
                        </a>
                    </div>
                </form>
            </div>

            <p class="mt-6 text-center text-xs text-gray-600">
                Link reset password akan kedaluwarsa dalam 60 menit
            </p>
        </div>
    </div>

    <script>
        function togglePassword(fieldId) {
            const field = document.getElementById(fieldId);
            const icon = document.getElementById(fieldId + '-eye-icon');
            
            if (field.type === 'password') {
                field.type = 'text';
                icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />';
            } else {
                field.type = 'password';
                icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />';
            }
        }

        function checkPasswordStrength() {
            const password = document.getElementById('password').value;
            const strengthBar = document.getElementById('strengthBar');
            const strengthText = document.getElementById('strengthText');
            
            let strength = 0;
            let tips = [];

            // Check length
            const lengthCheck = document.getElementById('length-check');
            if (password.length >= 8) {
                strength += 25;
                lengthCheck.classList.remove('text-gray-400');
                lengthCheck.classList.add('text-green-600');
            } else {
                lengthCheck.classList.remove('text-green-600');
                lengthCheck.classList.add('text-gray-400');
            }

            // Check uppercase
            const uppercaseCheck = document.getElementById('uppercase-check');
            if (/[A-Z]/.test(password)) {
                strength += 25;
                uppercaseCheck.classList.remove('text-gray-400');
                uppercaseCheck.classList.add('text-green-600');
            } else {
                uppercaseCheck.classList.remove('text-green-600');
                uppercaseCheck.classList.add('text-gray-400');
            }

            // Check lowercase
            const lowercaseCheck = document.getElementById('lowercase-check');
            if (/[a-z]/.test(password)) {
                strength += 25;
                lowercaseCheck.classList.remove('text-gray-400');
                lowercaseCheck.classList.add('text-green-600');
            } else {
                lowercaseCheck.classList.remove('text-green-600');
                lowercaseCheck.classList.add('text-gray-400');
            }

            // Check number
            const numberCheck = document.getElementById('number-check');
            if (/[0-9]/.test(password)) {
                strength += 25;
                numberCheck.classList.remove('text-gray-400');
                numberCheck.classList.add('text-green-600');
            } else {
                numberCheck.classList.remove('text-green-600');
                numberCheck.classList.add('text-gray-400');
            }

            // Update strength bar
            if (strength === 0) {
                strengthBar.style.width = '0%';
                strengthBar.classList.remove('bg-red-500', 'bg-yellow-500', 'bg-blue-500', 'bg-green-500');
                strengthText.textContent = 'Kekuatan password';
                strengthText.className = 'mt-1 text-xs text-gray-600';
            } else if (strength <= 25) {
                strengthBar.style.width = '25%';
                strengthBar.classList.remove('bg-yellow-500', 'bg-blue-500', 'bg-green-500');
                strengthBar.classList.add('bg-red-500');
                strengthText.textContent = 'Password lemah';
                strengthText.className = 'mt-1 text-xs text-red-600 font-medium';
            } else if (strength <= 50) {
                strengthBar.style.width = '50%';
                strengthBar.classList.remove('bg-red-500', 'bg-blue-500', 'bg-green-500');
                strengthBar.classList.add('bg-yellow-500');
                strengthText.textContent = 'Password cukup';
                strengthText.className = 'mt-1 text-xs text-yellow-600 font-medium';
            } else if (strength <= 75) {
                strengthBar.style.width = '75%';
                strengthBar.classList.remove('bg-red-500', 'bg-yellow-500', 'bg-green-500');
                strengthBar.classList.add('bg-blue-500');
                strengthText.textContent = 'Password kuat';
                strengthText.className = 'mt-1 text-xs text-blue-600 font-medium';
            } else {
                strengthBar.style.width = '100%';
                strengthBar.classList.remove('bg-red-500', 'bg-yellow-500', 'bg-blue-500');
                strengthBar.classList.add('bg-green-500');
                strengthText.textContent = 'Password sangat kuat';
                strengthText.className = 'mt-1 text-xs text-green-600 font-medium';
            }

            checkPasswordMatch();
        }

        function checkPasswordMatch() {
            const password = document.getElementById('password').value;
            const confirmation = document.getElementById('password_confirmation').value;
            const matchText = document.getElementById('matchText');

            if (confirmation.length === 0) {
                matchText.textContent = '';
                return;
            }

            if (password === confirmation) {
                matchText.textContent = '✓ Password cocok';
                matchText.className = 'mt-1.5 text-xs text-green-600 font-medium';
            } else {
                matchText.textContent = '✗ Password tidak cocok';
                matchText.className = 'mt-1.5 text-xs text-red-600 font-medium';
            }
        }

        // Validasi form sebelum submit
        document.getElementById('resetForm').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmation = document.getElementById('password_confirmation').value;

            if (password !== confirmation) {
                e.preventDefault();
                alert('Password dan konfirmasi password tidak cocok!');
                return false;
            }

            if (password.length < 8) {
                e.preventDefault();
                alert('Password harus minimal 8 karakter!');
                return false;
            }

            // Disable tombol submit untuk mencegah double submit
            const submitBtn = document.getElementById('submitBtn');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> <span class="ml-2">Memproses...</span>';
        });
    </script>
</body>
</html>

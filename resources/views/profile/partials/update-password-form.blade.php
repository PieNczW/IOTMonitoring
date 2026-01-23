<section>
    <header class="mb-6">
        <h2 class="text-lg font-bold text-gray-100 flex items-center gap-2">
            <span class="text-cyan-400">#</span> {{ __('Ganti Password') }}
        </h2>
        <p class="mt-1 text-sm text-gray-400">
            {{ __("Pastikan akun Anda aman dengan menggunakan password yang panjang dan acak.") }}
        </p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('put')

        <div>
            <x-input-label for="update_password_current_password" :value="__('Password Saat Ini')" class="text-gray-300" />
            <div class="relative mt-1">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fas fa-lock text-gray-500"></i>
                </div>
                <x-text-input id="update_password_current_password" name="current_password" type="password" 
                    class="pl-10 block w-full bg-gray-900 border-gray-600 text-gray-100 focus:border-cyan-500 focus:ring-cyan-500 rounded-xl" 
                    autocomplete="current-password" />
            </div>
            <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="update_password_password" :value="__('Password Baru')" class="text-gray-300" />
            <div class="relative mt-1">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fas fa-key text-gray-500"></i>
                </div>
                <x-text-input id="update_password_password" name="password" type="password" 
                    class="pl-10 block w-full bg-gray-900 border-gray-600 text-gray-100 focus:border-cyan-500 focus:ring-cyan-500 rounded-xl" 
                    autocomplete="new-password" />
            </div>
            <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="update_password_password_confirmation" :value="__('Konfirmasi Password')" class="text-gray-300" />
            <div class="relative mt-1">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fas fa-check-double text-gray-500"></i>
                </div>
                <x-text-input id="update_password_password_confirmation" name="password_confirmation" type="password" 
                    class="pl-10 block w-full bg-gray-900 border-gray-600 text-gray-100 focus:border-cyan-500 focus:ring-cyan-500 rounded-xl" 
                    autocomplete="new-password" />
            </div>
            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center gap-4 pt-2">
            <x-primary-button class="bg-gradient-to-r from-cyan-600 to-cyan-500 hover:from-cyan-500 hover:to-cyan-400 border-0 shadow-lg shadow-cyan-500/30 px-6 py-2.5">
                <i class="fas fa-shield-alt me-2"></i> {{ __('Update Password') }}
            </x-primary-button>

            @if (session('status') === 'password-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-green-400 font-bold flex items-center gap-1"
                ><i class="fas fa-check-circle"></i> {{ __('Berhasil.') }}</p>
            @endif
        </div>
    </form>
</section>
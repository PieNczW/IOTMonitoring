<section>
    <header class="mb-6">
        <h2 class="text-lg font-bold text-gray-100 flex items-center gap-2">
            <span class="text-blue-400">#</span> {{ __('Informasi Profil') }}
        </h2>
        <p class="mt-1 text-sm text-gray-400">
            {{ __("Perbarui nama tampilan akun dan alamat email Anda.") }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <div>
            <x-input-label for="name" :value="__('Nama Lengkap')" class="text-gray-300" />
            <div class="relative mt-1">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fas fa-user text-gray-500"></i>
                </div>
                <x-text-input id="name" name="name" type="text" 
                    class="pl-10 block w-full bg-gray-900 border-gray-600 text-gray-100 focus:border-blue-500 focus:ring-blue-500 rounded-xl" 
                    :value="old('name', $user->name)" required autofocus autocomplete="name" />
            </div>
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="email" :value="__('Email Address')" class="text-gray-300" />
            <div class="relative mt-1">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fas fa-envelope text-gray-500"></i>
                </div>
                <x-text-input id="email" name="email" type="email" 
                    class="pl-10 block w-full bg-gray-900 border-gray-600 text-gray-100 focus:border-blue-500 focus:ring-blue-500 rounded-xl" 
                    :value="old('email', $user->email)" required autocomplete="username" />
            </div>
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div class="mt-2 p-3 bg-yellow-900/20 border border-yellow-700/50 rounded-lg">
                    <p class="text-sm text-yellow-200">
                        {{ __('Alamat email Anda belum diverifikasi.') }}
                        <button form="send-verification" class="underline text-sm text-yellow-400 hover:text-yellow-300 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 focus:ring-offset-gray-800">
                            {{ __('Klik di sini untuk mengirim ulang email verifikasi.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-medium text-sm text-green-400">
                            {{ __('Link verifikasi baru telah dikirim ke alamat email Anda.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div class="flex items-center gap-4 pt-2">
            <x-primary-button class="bg-gradient-to-r from-blue-600 to-blue-500 hover:from-blue-500 hover:to-blue-400 border-0 shadow-lg shadow-blue-500/30 px-6 py-2.5">
                <i class="fas fa-save me-2"></i> {{ __('Simpan Perubahan') }}
            </x-primary-button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-green-400 font-bold flex items-center gap-1"
                ><i class="fas fa-check-circle"></i> {{ __('Tersimpan.') }}</p>
            @endif
        </div>
    </form>
</section>
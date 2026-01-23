<section class="space-y-6">
    <header>
        <h2 class="text-lg font-bold text-red-500 flex items-center gap-2">
            <i class="fas fa-radiation"></i> {{ __('Hapus Akun') }}
        </h2>

        <p class="mt-1 text-sm text-gray-400">
            {{ __("Setelah akun Anda dihapus, semua sumber daya dan data akan dihapus secara permanen. Sebelum menghapus akun, harap unduh data atau informasi apa pun yang ingin Anda simpan.") }}
        </p>
    </header>

    <x-danger-button
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
        class="bg-red-600 hover:bg-red-500 border-0 shadow-lg shadow-red-600/30 px-6 py-3"
    >{{ __('Hapus Akun Saya') }}</x-danger-button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-6 bg-gray-800 border border-gray-700 text-gray-100">
            @csrf
            @method('delete')

            <h2 class="text-lg font-medium text-gray-100">
                {{ __('Apakah Anda yakin ingin menghapus akun ini?') }}
            </h2>

            <p class="mt-1 text-sm text-gray-400">
                {{ __("Setelah akun Anda dihapus, semua data akan hilang permanen. Harap masukkan password Anda untuk mengonfirmasi bahwa Anda ingin menghapus akun Anda secara permanen.") }}
            </p>

            <div class="mt-6">
                <x-input-label for="password" value="{{ __('Password') }}" class="sr-only" />

                <x-text-input
                    id="password"
                    name="password"
                    type="password"
                    class="mt-1 block w-3/4 bg-gray-900 border-gray-600 text-white placeholder-gray-500 focus:border-red-500 focus:ring-red-500"
                    placeholder="{{ __('Masukkan Password Anda') }}"
                />

                <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
            </div>

            <div class="mt-6 flex justify-end">
                <x-secondary-button x-on:click="$dispatch('close')" class="bg-gray-700 text-gray-300 border-gray-600 hover:bg-gray-600">
                    {{ __('Batal') }}
                </x-secondary-button>

                <x-danger-button class="ms-3 bg-red-600 hover:bg-red-500">
                    {{ __('Ya, Hapus Akun') }}
                </x-danger-button>
            </div>
        </form>
    </x-modal>
</section>
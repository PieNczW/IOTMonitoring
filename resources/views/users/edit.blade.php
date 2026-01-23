<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Edit User: {{ $user->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg border dark:border-gray-700 p-6">
                
                <form action="{{ route('users.update', $user->id) }}" method="POST" class="space-y-6">
                    @csrf
                    @method('PUT')
                    
                    <div>
                        <x-input-label for="name" :value="__('Nama Lengkap')" />
                        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="$user->name" required />
                    </div>

                    <div>
                        <x-input-label for="email" :value="__('Email Address')" />
                        <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="$user->email" required />
                    </div>

                    <div>
                        <x-input-label for="role" :value="__('Role')" />
                        <select name="role" id="role" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                            <option value="user" {{ $user->role == 'user' ? 'selected' : '' }}>User Biasa</option>
                            <option value="admin" {{ $user->role == 'admin' ? 'selected' : '' }}>Admin</option>
                        </select>
                    </div>

                    <div class="pt-4 border-t border-gray-700">
                        <h3 class="text-lg font-medium text-gray-200 mb-4">Ganti Password (Opsional)</h3>
                        <p class="text-sm text-gray-400 mb-4">Kosongkan jika tidak ingin mengganti password.</p>
                        
                        <div class="space-y-4">
                            <div>
                                <x-input-label for="password" :value="__('Password Baru')" />
                                <x-text-input id="password" name="password" type="password" class="mt-1 block w-full" />
                            </div>
                            <div>
                                <x-input-label for="password_confirmation" :value="__('Konfirmasi Password Baru')" />
                                <x-text-input id="password_confirmation" name="password_confirmation" type="password" class="mt-1 block w-full" />
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-4 mt-6">
                        <x-primary-button>{{ __('Update User') }}</x-primary-button>
                        <a href="{{ route('users.index') }}" class="text-gray-400 hover:text-gray-200">Batal</a>
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>
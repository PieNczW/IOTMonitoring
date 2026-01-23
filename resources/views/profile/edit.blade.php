<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <div class="p-2 bg-blue-600/20 rounded-lg text-blue-400">
                <i class="fas fa-user-cog fa-lg"></i>
            </div>
            <h2 class="font-bold text-xl text-gray-100 leading-tight tracking-wide">
                {{ __('Pengaturan Akun') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                
                <div class="p-6 sm:p-8 bg-gray-800/50 backdrop-blur-xl border border-gray-700 shadow-xl sm:rounded-2xl relative overflow-hidden group hover:border-blue-500/30 transition-all duration-300">
                    <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                        <i class="fas fa-id-card fa-4x text-blue-500"></i>
                    </div>
                    <div class="relative z-10">
                        @include('profile.partials.update-profile-information-form')
                    </div>
                </div>

                <div class="p-6 sm:p-8 bg-gray-800/50 backdrop-blur-xl border border-gray-700 shadow-xl sm:rounded-2xl relative overflow-hidden group hover:border-cyan-500/30 transition-all duration-300">
                    <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                        <i class="fas fa-key fa-4x text-cyan-500"></i>
                    </div>
                    <div class="relative z-10">
                        @include('profile.partials.update-password-form')
                    </div>
                </div>

            </div>

            <div class="p-6 sm:p-8 bg-red-900/10 border border-red-900/30 shadow-xl sm:rounded-2xl relative overflow-hidden group hover:border-red-500/50 transition-all duration-300">
                <div class="absolute top-0 right-0 p-4 opacity-5 group-hover:opacity-10 transition-opacity">
                    <i class="fas fa-exclamation-triangle fa-4x text-red-500"></i>
                </div>
                <div class="relative z-10">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
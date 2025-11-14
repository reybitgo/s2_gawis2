@extends('layouts.app')

@section('content')
<div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div>
            <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                Two-Factor Authentication
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                Please confirm access to your account by entering the authentication code provided by your authenticator application.
            </p>
        </div>

        @if ($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form class="mt-8 space-y-6" action="{{ url('/two-factor-challenge') }}" method="POST">
            @csrf
            <div class="space-y-4">
                <div>
                    <label for="code" class="block text-sm font-medium text-gray-700">Authentication Code</label>
                    <input id="code" name="code" type="text" autocomplete="one-time-code" required
                           class="mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm text-center text-2xl tracking-widest"
                           placeholder="000000" maxlength="6">
                    <p class="mt-1 text-xs text-gray-500">
                        Enter the 6-digit code from your authenticator app
                    </p>
                </div>
            </div>

            <div>
                <button type="submit"
                        class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Verify
                </button>
            </div>

            <div class="text-center">
                <p class="text-sm text-gray-600 mb-2">Or use a recovery code</p>
                <button type="button" id="toggle-recovery"
                        class="text-sm font-medium text-indigo-600 hover:text-indigo-500">
                    Use recovery code instead
                </button>
            </div>
        </form>

        <form class="mt-8 space-y-6 hidden" id="recovery-form" action="{{ url('/two-factor-challenge') }}" method="POST">
            @csrf
            <div class="space-y-4">
                <div>
                    <label for="recovery_code" class="block text-sm font-medium text-gray-700">Recovery Code</label>
                    <input id="recovery_code" name="recovery_code" type="text" autocomplete="one-time-code"
                           class="mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                           placeholder="Enter recovery code">
                    <p class="mt-1 text-xs text-gray-500">
                        Enter one of your recovery codes
                    </p>
                </div>
            </div>

            <div>
                <button type="submit"
                        class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Use Recovery Code
                </button>
            </div>

            <div class="text-center">
                <button type="button" id="toggle-code"
                        class="text-sm font-medium text-indigo-600 hover:text-indigo-500">
                    Use authentication code instead
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const toggleRecovery = document.getElementById('toggle-recovery');
    const toggleCode = document.getElementById('toggle-code');
    const codeForm = document.querySelector('form:not(#recovery-form)');
    const recoveryForm = document.getElementById('recovery-form');

    toggleRecovery.addEventListener('click', function() {
        codeForm.classList.add('hidden');
        recoveryForm.classList.remove('hidden');
    });

    toggleCode.addEventListener('click', function() {
        recoveryForm.classList.add('hidden');
        codeForm.classList.remove('hidden');
    });
});
</script>
@endsection
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('mail.license_token_title') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'primary': '#3b82f6',
                        'primary-dark': '#1e40af',
                        'secondary': '#6b7280',
                        'success': '#10b981',
                        'warning': '#f59e0b',
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-50 font-sans">
<div class="max-w-2xl mx-auto bg-white shadow-sm border border-gray-200 rounded-lg overflow-hidden">
    <div class="bg-gradient-to-r from-slate-800 to-slate-700 px-8 py-10 text-center">
        <div class="mb-3">
            <span class="inline-block text-4xl">🔑</span>
        </div>
        <h1 class="text-2xl font-semibold text-white mb-2">
            {{ __('mail.license_token_header') }}
        </h1>
        <p class="text-slate-200 text-sm">
            {{ $appName }}
        </p>
    </div>

    <div class="px-8 py-10">
        <div class="mb-6">
            <p class="text-lg text-gray-800 font-medium">
                {{ __('mail.greeting', ['name' => $userName ?? __('mail.default_user')]) }}
            </p>
        </div>

        <div class="mb-8">
            <p class="text-gray-600 leading-relaxed mb-4">
                {{ __('mail.purchase_confirmation') }}
            </p>
            <p class="text-gray-600 leading-relaxed">
                {{ __('mail.license_instructions') }}
            </p>
        </div>

        <div class="bg-gray-50 border border-gray-200 rounded-lg p-8 text-center mb-8">
            <div class="mb-4">
                <p class="text-xs uppercase tracking-wider text-gray-500 font-semibold">
                    {{ __('mail.license_key_label') }}
                </p>
            </div>

            <div class="bg-white border border-gray-300 rounded-md p-4 mb-6 shadow-sm">
                <code id="licenseKey" class="text-lg font-mono text-gray-900 break-all select-all">
                    {{ $token }}
                </code>
            </div>

            <button onclick="copyLicenseKey()"
                    class="inline-flex items-center px-4 py-2 bg-primary text-white text-sm font-medium rounded-md hover:bg-primary-dark transition-colors duration-200">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                </svg>
                {{ __('mail.copy_license') }}
            </button>
        </div>

        <div class="bg-amber-50 border-l-4 border-amber-400 p-6 mb-8">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-amber-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                              d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                              clip-rule="evenodd"></path>
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-semibold text-amber-800">
                        {{ __('mail.important_info_title') }}
                    </h3>
                    <div class="mt-2 text-sm text-amber-700 space-y-1">
                        <p>• {{ __('mail.license_permanent') }}</p>
                        <p>• {{ __('mail.license_confidential') }}</p>
                        <p>• {{ __('mail.license_support') }}</p>
                        <p>• {{ __('mail.license_backup') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-8">
            <h3 class="text-lg font-semibold text-blue-900 mb-3">
                {{ __('mail.service_info_title') }}
            </h3>
            <div class="text-blue-800 text-sm space-y-2">
                <p><strong>{{ __('mail.service_name') }}:</strong> {{ $serviceName ?? __('mail.default_service') }}</p>
                <p><strong>{{ __('mail.purchase_date') }}:</strong> {{ now()->format('Y. m. d.') }}</p>
                <p><strong>{{ __('mail.license_type') }}:</strong> {{ $licenseType ?? __('mail.standard_license') }}</p>
            </div>
        </div>

        <div class="border-t border-gray-200 my-8"></div>

        <div class="text-center">
            <p class="text-gray-600 mb-4">
                {{ __('mail.support_message') }}
            </p>
            <a href="{{ config('app.url') }}/support"
               class="inline-flex items-center text-primary hover:text-primary-dark font-medium">
                {{ __('mail.contact_support') }}
                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                </svg>
            </a>
        </div>
    </div>

    <div class="bg-gray-50 px-8 py-6 text-center border-t border-gray-100">
        <p class="text-xs text-gray-500 mb-2">
            {{ __('mail.automated_email') }}
        </p>
        <p class="text-xs text-gray-500 mb-3">
            &copy; {{ date('Y') }} {{ $appName }}. {{ __('mail.all_rights_reserved') }}
        </p>
        <div class="flex justify-center space-x-4 text-xs">
            <a href="{{ config('app.url') }}" class="text-gray-400 hover:text-gray-600">
                {{ __('mail.website') }}
            </a>
            <span class="text-gray-300">|</span>
            <a href="{{ config('app.url') }}/privacy" class="text-gray-400 hover:text-gray-600">
                {{ __('mail.privacy_policy') }}
            </a>
            <span class="text-gray-300">|</span>
            <a href="{{ config('app.url') }}/terms" class="text-gray-400 hover:text-gray-600">
                {{ __('mail.terms_of_service') }}
            </a>
        </div>
    </div>
</div>

<script>
    function copyLicenseKey() {
        const licenseElement = document.getElementById('licenseKey');
        const licenseKey = licenseElement.textContent.trim();

        // Modern böngészők esetén
        if (navigator.clipboard) {
            navigator.clipboard.writeText(licenseKey).then(function () {
                // Visual feedback
                const button = event.target.closest('button');
                const originalText = button.innerHTML;

                button.innerHTML = `
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        {{ __('mail.copied') }}
                `;
                button.classList.remove('bg-primary', 'hover:bg-primary-dark');
                button.classList.add('bg-success');

                setTimeout(() => {
                    button.innerHTML = originalText;
                    button.classList.remove('bg-success');
                    button.classList.add('bg-primary', 'hover:bg-primary-dark');
                }, 2000);
            });
        } else {
            // Fallback régebbi böngészőknek
            const textArea = document.createElement('textarea');
            textArea.value = licenseKey;
            document.body.appendChild(textArea);
            textArea.select();
            document.execCommand('copy');
            document.body.removeChild(textArea);

            alert('{{ __('mail.copy_success_fallback') }}');
        }
    }
</script>
</body>
</html>

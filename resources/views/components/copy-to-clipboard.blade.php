<div>
    <div id="copyText" class="hidden">{{ $text }}</div>
    <flux:button
        onclick="copyToClipboard('{{ $type }}', '{{ __($message) }}')"
        icon="clipboard-document"
    >
        {{ __('interface.copy') }}
    </flux:button>
</div>

<script>
    function copyToClipboard(type, message) {
        const text = document.getElementById('copyText').innerText;

        if (!text.trim()) {
            if (window.Livewire) {
                Livewire.dispatch('notify', {
                    type: 'error',
                    message: '{{ __("interface.copy_empty_error") }}'
                });
            }
            return;
        }

        navigator.clipboard.writeText(text)
            .then(() => {
                if (window.Livewire) {
                    Livewire.dispatch('notify', {
                        type: type,
                        message: message
                    });
                }
            })
            .catch(err => {
                console.error("Hiba történt: ", err);
                if (window.Livewire) {
                    Livewire.dispatch('notify', {
                        type: 'error',
                        message: '{{ __("interface.copy_failed") }}'
                    });
                }
            });
    }
</script>
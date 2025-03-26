<div x-data="clipboard()"
     x-init="init()"
     {{ $attributes }}>
     <flux:button @click="copy()" class="{{ $button_class ?? '' }}" icon="clipboard-document-list">
         {{ __('interface.copy') }}
     </flux:button>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('clipboard', () => ({
        ref: @js($ref ?? null),
        text: @js($text ?? null),
        livewireProperty: @js($livewireProperty ?? null),
        notifyEvent: @js($notifyEvent ?? 'notify'),
        notifyType: @js($notifyType ?? 'success'),
        notifyMessage: @js($notifyMessage ?? __('interface.copied_successfully')),

        init() {
            if (this.livewireProperty) {
                this.setupLivewireListener();
            }
            else if (this.ref) {
                this.setupDomObserver();
            }
        },

        setupLivewireListener() {
            Livewire.hook('commit', ({ component, succeed }) => {
                succeed(() => {
                    this.text = this.getLivewirePropertyValue();
                });
            });
        },

        setupDomObserver() {
            if (!this.$refs[this.ref]) return;

            this.observer = new MutationObserver(() => {
                this.text = this.$refs[this.ref]?.value ||
                           this.$refs[this.ref]?.textContent;
            });

            this.observer.observe(this.$refs[this.ref], {
                childList: true,
                subtree: true,
                characterData: true
            });
        },

        getLivewirePropertyValue() {
            if (!this.livewireProperty) return null;

            const component = Alpine.store('livewire').components.find(
                comp => comp.id === @this.__instance.id
            );
            return component?.data[this.livewireProperty];
        },

        copy() {
            let copyText = this.text;

            if (this.livewireProperty) {
                copyText = this.getLivewirePropertyValue();
            }
            else if (this.ref) {
                copyText = this.$refs[this.ref]?.value ||
                          this.$refs[this.ref]?.textContent;
            }

            if (!copyText) {
                this.triggerNotify('error', __('interface.copy_empty_error'));
                return;
            }

            navigator.clipboard.writeText(copyText)
                .then(() => this.triggerNotify(this.notifyType, this.notifyMessage))
                .catch(err => {
                    console.error('Másolási hiba:', err);
                    this.triggerNotify('error', __('interface.copy_failed'));
                });
        },

        triggerNotify(type, message) {
            if (window.Livewire) {
                Livewire.dispatch(this.notifyEvent, { type, message });
            }
            this.$dispatch(this.notifyEvent, { type, message });
        }
    }));
});
</script>
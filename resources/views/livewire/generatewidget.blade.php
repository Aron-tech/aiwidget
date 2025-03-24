<?php

use Livewire\Volt\Component;
use App\Models\Site;

new class extends Component {

    public ?Site $site = null;

    public $style = null;

    public $name = null;

    public $widget_config = null;

    public function mount(Site $site)
    {
        $this->site = $site;
        $this->generateWidgetConfig();
    }

    public function generateWidgetConfig()
    {
        $this->widget_config = "window.widgetConfig = {
            siteId: '{$this->site->uuid}',";

        if ($this->style) {
            $this->widget_config .= " cssUrl: 'https://szakdolgozat.test/css/widget/{$this->style}',";
        }

        if ($this->name) {
            $this->widget_config .= " widgetName: '{$this->name}',";
        }

        $this->widget_config .= "};";
    }

    public function updated()
    {
        $this->generateWidgetConfig();
    }
}; ?>

<div>
    <div class="sm:block hidden mb-4">
        <flux:breadcrumbs>
            <flux:breadcrumbs.item href="{{route('dashboard', $site->uuid)}}" icon="home" />
            <flux:breadcrumbs.item>{{__('interface.generate_widget')}}</flux:breadcrumbs.item>
        </flux:breadcrumbs>
    </div>
    <div class="grid grid-cols-2 gap-6 p-10 justify-items-center">
        <x-widget-style.panel/>
        <div class="space-y-14 w-full">
            <div class="w-full">
                <flux:input wire:model.live.debounce.1250ms="name" label="{{ __('interface.widget_name') }}"/>
            </div>
            <div>
                <flux:radio.group wire:model.live="style" label="{{ __('interface.select_widget_style') }}" variant="segmented" size="lg">
                    <flux:radio value="" checked label="{{ __('interface.default_style') }}" />
                    <flux:radio value="2" label="{{ __('interface.custom_style') }}" />
                    <flux:radio value="1" label="{{ __('interface.custom_style') }}" />
                </flux:radio.group>
            </div>
            <div class="mt-10">
                <flux:textarea readonly resize="none" rows="10">
                    <div id="conversiveai-widget-container"></div>
                    <script>
                        {!! $widget_config !!}
                    </script>
                    <script src="https://szakdolgozat.test/js/widget.js"></script>
                </flux:textarea>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('livewire:init', () => {
        let splideInstance = null;

        const initSplide = () => {
            const splideEl = document.querySelector('.splide.widget-styles');
            if (!splideEl) return;

            // Ha már létezik példány, először töröljük
            if (splideInstance) {
                splideInstance.destroy();
            }

            // Új Splide példány létrehozása
            splideInstance = new Splide(splideEl, {
                type: 'loop',
                perPage: 1,
                gap: '1rem',
                pagination: false,
                arrows: false,
                width: '100%'
            }).mount();

            // Gombok eseménykezelői
            document.querySelector('.widget-style-prev')?.addEventListener('click', () => splideInstance.go('-1'));
            document.querySelector('.widget-style-next')?.addEventListener('click', () => splideInstance.go('+1'));
        };

        // Kezdeti inicializálás
        initSplide();

        // Minden Livewire frissítés után újrainicializáljuk
        Livewire.hook('commit', ({ component, commit, respond, succeed, fail }) => {
            succeed(() => {
                setTimeout(initSplide, 50); // Kis késleltetés a DOM frissüléséhez
            });
        });
    });
</script>
@endpush
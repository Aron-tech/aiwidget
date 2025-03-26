<?php

use Livewire\Volt\Component;
use App\Models\Site;
use App\Models\SiteSelector;
use App\Livewire\Traits\GlobalNotifyEvent;

new class extends Component {
    use GlobalNotifyEvent;

    public ?Site $site = null;
    public $style = null;
    public $name = null;
    public $widget_config = '';

    public function mount(SiteSelector $site_selector)
    {
        if (!$site_selector->hasSite()) {
            return redirect()->route('site.picker')->with('error', __('interface.missing_site'));
        }

        $this->site = $site_selector->getSite();
        $this->generateWidgetConfig();
    }

    public function generateWidgetConfig()
    {
        $config = "window.widgetConfig = {\n";
        $config .= "     siteId: '{$this->site->uuid}',\n";

        if ($this->style) {
            $config .= "     cssUrl: '" . config('app.url') . "/css/widget/{$this->style}.css',\n";
        }

        if ($this->name) {
            $config .= "     widgetName: '{$this->name}',\n";
        }

        $config .= "     };\n";

        $this->widget_config = "<div id=\"conversiveai-widget-container\"></div>\n<script>\n{$config}</script>\n<script src=\"https://szakdolgozat.test/js/widget.js\"></script>";
    }

    public function updated()
    {
        $this->generateWidgetConfig();
    }
};?>

<div>
    <div class="sm:block hidden mb-4">
        <flux:breadcrumbs>
            <flux:breadcrumbs.item href="{{route('dashboard', $site->uuid)}}" icon="home" />
            <flux:breadcrumbs.item>{{__('interface.generate_widget')}}</flux:breadcrumbs.item>
        </flux:breadcrumbs>
    </div>
    <x-notification.panel :notifications="session()->all()"/>
    <div class="grid grid-cols-2 gap-6 p-10 justify-items-center">
        <x-widget-style.panel/>
        <div class="space-y-14 w-full">
            <div class="w-full">
                <flux:input wire:model.live.debounce.750ms="name" label="{{ __('interface.widget_name') }}"/>
            </div>
            <div>
                <flux:radio.group wire:model.live="style" label="{{ __('interface.select_widget_style') }}" variant="segmented" size="lg">
                    <flux:radio value="" checked label="{{ __('interface.default_style') }}" />
                    <flux:radio value="2" label="{{ __('interface.custom_style') }}" />
                    <flux:radio value="1" label="{{ __('interface.custom_style') }}" />
                </flux:radio.group>
            </div>
            <div class="mt-10 flex flex-col gap-4">
                <flux:textarea x-ref="widgetTextarea" readonly resize="none" rows="10">
                    {{ $widget_config }}
                </flux:textarea>

                <x-copy-to-clipboard
                    :text="$widget_config"
                    :type="'info'"
                    :message="'interface.widget_config_copied_to_clipboard'"
                />
            </div>
            </div>
        </div>
    </div>
</div>
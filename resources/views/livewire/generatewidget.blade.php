<?php

use Livewire\Volt\Component;
use App\Models\Site;
use App\Models\SiteSelector;
use App\Livewire\Traits\GlobalNotifyEvent;

new class extends Component {
    use GlobalNotifyEvent;

    public ?Site $site = null;
    public $style = null;
    public ?string $name = null;
    public string $widget_config = '';

    public array $selected_databases = [];

    public function mount(SiteSelector $site_selector)
    {
        if (!$site_selector->hasSite()) {
            return redirect()->route('site.picker')->with('error', __('interface.missing_site'));
        }

        $this->site = $site_selector->getSite();
        $this->selected_databases = getJsonValue($this->site, 'settings', 'knowledge-databases', []);
        $this->generateWidgetConfig();
    }

    public function updatedSelectedDatabases(): void
    {
        try {
            if($this->selected_databases !== []){
                setJsonValue($this->site, 'settings', 'knowledge-databases', $this->selected_databases);
                $this->dispatch('notify', type: 'success', message: __('interface.save_changes'));
            }else{
                $this->selected_databases = getJsonValue($this->site, 'settings', 'knowledge-databases', []);
                $this->dispatch('notify', type: 'warning', message: __('interface.must_select_one'));
            }
        } catch (Throwable $e) {
            $this->dispatch('notify', type: 'danger', message: __('interface.save_failed'));
            report($e);
        }
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
}; ?>

<div>
    <div class="sm:block hidden mb-4">
        <flux:breadcrumbs>
            <flux:breadcrumbs.item href="{{route('dashboard', $site->uuid)}}" icon="home"/>
            <flux:breadcrumbs.item>{{__('interface.generate_widget')}}</flux:breadcrumbs.item>
        </flux:breadcrumbs>
    </div>
    <x-notification.panel :notifications="session()->all()"/>
    <div class="grid grid-cols-2 gap-6 p-10 justify-items-center">
        <!--x-widget-style.panel/-->
        <div class="space-y-14 w-full">
            <div class="w-full">
                <flux:input wire:model.live.debounce.750ms="name" label="{{ __('interface.widget_name') }}"/>
            </div>
            <div>
                <flux:radio.group wire:model.live="style" label="{{ __('interface.select_widget_style') }}"
                                  variant="segmented" size="lg">
                    <flux:radio value="" checked label="{{ __('interface.default_style') }}"/>
                    <flux:radio value="2" label="{{ __('interface.custom_style') }}"/>
                    <flux:radio value="1" label="{{ __('interface.custom_style') }}"/>
                </flux:radio.group>
            </div>
            <div class="mt-10 flex flex-col gap-4">
                @if(empty($selected_databases))
                    <flux:heading>{{__('interface.must_select_one')}}</flux:heading>
                @else
                    <flux:textarea x-ref="widgetTextarea" readonly resize="none" rows="10">
                        {{ $widget_config }}
                    </flux:textarea>
                    <x-copy-to-clipboard
                        :text="$widget_config"
                        :type="'info'"
                        :message="'interface.widget_config_copied_to_clipboard'"
                    />
                @endif
            </div>
            <flux:separator/>
            <flux:fieldset>
                <flux:legend>{{__('interface.knowledge_base')}}</flux:legend>
                <flux:description>{{__('interface.knowledge_base_description')}}</flux:description>
                <div class="flex gap-4 *:gap-x-2">
                    <flux:checkbox.group wire:model.live="selected_databases">
                        <flux:checkbox checked value="document" :label="__('interface.document_database')"/>
                        <flux:checkbox checked value="question" :label="__('interface.question_database')"/>
                    </flux:checkbox.group>
                </div>
            </flux:fieldset>
        </div>
    </div>
</div>
</div>

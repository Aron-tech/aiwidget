<?php

use App\Livewire\Traits\ImageHandlerTrait;
use Illuminate\Validation\ValidationException;
use Livewire\Volt\Component;
use App\Models\Site;
use App\Models\SiteSelector;
use App\Livewire\Traits\GlobalNotifyEvent;

new class extends Component {
    use GlobalNotifyEvent;
    use ImageHandlerTrait;

    public ?Site $site = null;
    public $style = null;
    public ?string $name = null;
    public string $widget_config = '';

    public ?string $image_path = null;
    public array $selected_databases = [];

    #[Validate('required|image|mimes:jpg,png,jpeg,webp|max:5120')]
    public $uploaded_widget_image;

    public function mount(SiteSelector $site_selector)
    {
        if (!$site_selector->hasSite()) {
            return redirect()->route('site.picker')->with('error', __('interface.missing_site'));
        }
        $this->site = $site_selector->getSite();
        $this->image_path = getJsonValue($this->site, 'settings', 'widget_icon_path', null);
        $this->selected_databases = getJsonValue($this->site, 'settings', 'knowledge-databases', []);
        $this->name = getJsonValue($this->site, 'settings', 'widget-name', '');
        $this->generateWidgetConfig();
    }

    public function updatedSelectedDatabases(): void
    {
        try {
            if ($this->selected_databases !== []) {
                setJsonValue($this->site, 'settings', 'knowledge-databases', $this->selected_databases);
                $this->dispatch('notify', type: 'success', message: __('interface.save_changes'));
            } else {
                $this->selected_databases = getJsonValue($this->site, 'settings', 'knowledge-databases', []);
                $this->dispatch('notify', type: 'warning', message: __('interface.must_select_one'));
            }
        } catch (Throwable $e) {
            $this->dispatch('notify', type: 'danger', message: __('interface.save_failed'));
            report($e);
        }
    }

    public function generateWidgetConfig(): void
    {
        $config = "window.widgetConfig = {\n";
        $config .= "     siteId: '{$this->site->uuid}',\n";

        if ($this->style) {
            $config .= "     cssUrl: '" . config('app.url') . "/css/widget/{$this->style}.css',\n";
        }

        if ($this->name) {
            $config .= "     widgetName: '{$this->name}',\n";
        }

        if ($this->image_path) {
            $config .= "     widgetIconUrl: '" . url(route('view-file', ['path' => $this->image_path])) . "',\n";
        }

        $config .= "};\n";

        $this->widget_config = "<div id=\"conversiveai-widget-container\"></div>\n<script>\n{$config}</script>\n<script src=\"https://szakdolgozat.test/js/widget.js\"></script>";
    }

    public function updatedStyle(): void
    {
        $this->generateWidgetConfig();
    }

    public function updatedName(): void
    {
        try {
            DB::transaction(function () {
                setJsonValue($this->site, 'settings', 'widget-name', $this->name);
            });
            $this->generateWidgetConfig();
        } catch (Throwable $e) {
            report($e);
            $this->dispatch('notify', type: 'danger', message: __('interface.save_failed'));
        }
    }

    public function updatedUploadWidgetImage(): void
    {
        try {
            $this->validate();
        } catch (ValidationException $e) {
            $this->uploaded_widget_image = null;
            Session::flash('status', __('interface.invalid_file_type'));
        }
    }

    public function saveWidgetImage(): void
    {
        if (empty($this->uploaded_widget_image)) return;
        $this->saveImage($this->site, 'settings', $this->uploaded_widget_image, 'uploads/' . $this->site->id.'/widget-icon', json_param: 'widget_icon_path');
        $this->image_path = getJsonValue($this->site, 'settings', 'widget_icon_path', null);
        $this->generateWidgetConfig();
        $this->profile_image = null;
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
        </div>
        <div class="space-y-14 w-full">
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
            <flux:separator/>
            <div class="flex lg:flex-row flex-col my-10 gap-8">
                <img
                    src="{{ $uploaded_widget_image?->temporaryUrl() ?? route('view-file', ['path' => getJsonValue($this->site, 'settings', 'widget_icon_path', 'default.svg')]) }}"
                    class="rounded-lg size-32" alt="{{$this->site->name}}">
                <flux:input type="file" wire:model="uploaded_widget_image"
                            label="{{__('interface.change_site_wiget_icon')}}"/>
            </div>
            <div class="text-right">
                <flux:button variant="primary" wire:click="saveWidgetImage()">{{ __('Save') }}</flux:button>
            </div>
        </div>
    </div>
</div>

<div>
    <x-notification.panel :notifications="session()->all()"/>
    <div class="sm:block hidden mb-4">
        <flux:breadcrumbs>
            <flux:breadcrumbs.item href="{{route('dashboard', $site->uuid)}}" icon="home" />
            <flux:breadcrumbs.item>{{__('interface.question_manager')}}</flux:breadcrumbs.item>
        </flux:breadcrumbs>
    </div>
    <div class="flex space-x-2">
        <div class="flex w-1/2 mb-12 mr-4" x-data @keydown.window.prevent.meta.k="$refs.searchInput.focus()" @keydown.window.prevent.ctrl.k="$refs.searchInput.focus()">
            <flux:input
                wire:model.live='search'
                x-ref="searchInput"
                kbd="⌘K"
                icon="magnifying-glass"
                placeholder="{{ __('interface.search') }}"
            />
        </div>
        <div class="flex space-x-4 justify-end w-full">
            <flux:button wire:click='downloadFolder()' icon="arrow-down-tray">{{__('interface.download')}}</flux:button>
            <flux:button wire:click='reloadDocuments' icon="arrow-path" variant="filled"></flux:button>
            <flux:button.group>
                <flux:modal.trigger name="add-document">
                    <flux:button icon="plus">{{__('interface.add')}}</flux:button>
                </flux:modal.trigger>
                <flux:dropdown>
                    <flux:button icon-trailing="chevron-down"></flux:button>

                    <flux:menu>
                        <flux:menu.item wire:click='import()' icon="table-cells">{{__('interface.write_text')}}</flux:menu.item>
                    </flux:menu>
                </flux:dropdown>
            </flux:button.group>
        </div>
    </div>
    <div class=" grid grid-cols-3 gap-6">
        <!--Táblázat fejléc-->
        <div class="pb-3 dark:border-white/20 border-b-zinc-900/20 border-b-2 cursor-pointer" wire:click="sort('title')">
            <div class="flex items-center space-x-2">
                <flux:heading size="lg">{{__('interface.file_name')}}</flux:heading>
                @if ($sort_by === 'title')
                    <x-heroicon-s-arrow-small-up class="w-4 h-4" x-show="$wire.sort_direction === 'asc'" />
                    <x-heroicon-s-arrow-small-down class="w-4 h-4" x-show="$wire.sort_direction === 'desc'" />
                @endif
            </div>
        </div>
        <div class="pb-3 dark:border-white/20 border-b-zinc-900/20 border-b-2 cursor-pointer" wire:click="sort('type')">
            <div class="flex items-center space-x-2">
                <flux:heading size="lg">{{__('interface.file_type')}}</flux:heading>
                @if ($sort_by === 'type')
                    <x-heroicon-s-arrow-small-up class="w-4 h-4" x-show="$wire.sort_direction === 'asc'" />
                    <x-heroicon-s-arrow-small-down class="w-4 h-4" x-show="$wire.sort_direction === 'desc'" />
                @endif
            </div>
        </div>
        <div class="pb-3 dark:border-white/20 border-b-zinc-900/20 border-b-2">
            <flux:heading size="lg">{{__('interface.actions')}}</flux:heading>
        </div>

        <!--Táblázat adatok-->
        @foreach ($documents as $document)
            <div>
                <flux:heading>{{$document->title}}</flux:heading>
            </div>
            <div>
                <flux:heading>{{$document->type}}</flux:heading>
            </div>
            <div class="flex justify-center space-x-4">
                <a href="{{ route('view-file', ['path' => $document->path]) }}" target="_blank">
                    <flux:button variant="filled" icon="eye"/>
                </a>
                <flux:button wire:click="download({{ $document->id }})" icon="arrow-down-tray"/>
                <flux:button wire:click='destroy({{ $document->id }})' icon="trash" variant="danger"/>
            </div>
        @endforeach
        <div class="col-span-full">
            {{ $documents->links() }}
        </div>
    </div>

    <flux:modal name="add-document" class="md:w-96">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{__('interface.upload_file')}}</flux:heading>
            </div>

            <form wire:submit.prevent="save" class="rounded-lg p-6 space-y-4">
                <div>
                    <flux:input wire:model.lazy="title" :label="__('interface.title')" />
                </div>
                <div>
                    <flux:input type="file" wire:model.lazy="file" :label="__('interface.file_selection')"/>
                </div>
                <div class="flex space-x-4">
                    <flux:button type="submit" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="save">{{__('interface.upload')}}</span>
                        <span wire:loading wire:target="save">{{__('interface.processing')}}</span>
                    </flux:button>
                </div>
            </form>
        </div>
    </flux:modal>
</div>

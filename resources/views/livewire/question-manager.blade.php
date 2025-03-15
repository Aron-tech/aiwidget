<div>
    <!-- Értesítés megjelenítése -->
    <div wire:poll.5000ms class="fixed top-3 sm:top-5 w-2/3 sm:left-1/2 transform sm:-translate-x-1/2 sm:w-1/4 z-50">
        @if(session()->has('success'))
            <flux:callout icon="check-circle" variant="success" inline x-data="{ visible: true }" x-show="visible">
                <flux:callout.heading class="flex gap-2 @max-md:flex-col items-start">{{ session('success') }}</flux:callout.heading>
                <x-slot name="controls">
                    <flux:button icon="x-mark" variant="ghost" x-on:click="visible = false" />
                </x-slot>
            </flux:callout>
        @elseif(session()->has('warning'))
            <flux:callout icon="exclamation-triangle" variant="warning" inline x-data="{ visible: true }" x-show="visible">
                <flux:callout.heading class="flex gap-2 @max-md:flex-col items-start">{{ session('warning') }}</flux:callout.heading>
                <x-slot name="controls">
                    <flux:button icon="x-mark" variant="ghost" x-on:click="visible = false" />
                </x-slot>
            </flux:callout>
        @elseif(session()->has('danger'))
            <flux:callout icon="exclamation-triangle" variant="danger" inline x-data="{ visible: true }" x-show="visible">
                <flux:callout.heading class="flex gap-2 @max-md:flex-col items-start">{{ session('danger') }}</flux:callout.heading>
                <x-slot name="controls">
                    <flux:button icon="x-mark" variant="ghost" x-on:click="visible = false" />
                </x-slot>
            </flux:callout>
        @elseif(session()->has('info'))
            <flux:callout icon="exclamation-triangle" variant="info" inline x-data="{ visible: true }" x-show="visible">
                <flux:callout.heading class="flex gap-2 @max-md:flex-col items-start">{{ session('info') }}</flux:callout.heading>
                <x-slot name="controls">
                    <flux:button icon="x-mark" variant="ghost" x-on:click="visible = false" />
                </x-slot>
            </flux:callout>
        @endif
    </div>
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
            <flux:button wire:click='export()' icon="arrow-down-tray">{{__('interface.download')}}</flux:button>
            <flux:button wire:click='reloadQuestions()' icon="arrow-path" variant="filled"></flux:button>
                <flux:button.group>
                    <flux:modal.trigger name="create-question">
                        <flux:button icon="plus">{{__('interface.add')}}</flux:button>
                    </flux:modal.trigger>
                    <flux:dropdown>
                        <flux:button icon-trailing="chevron-down"></flux:button>

                        <flux:menu>
                            <flux:modal.trigger name="import-question">
                                <flux:menu.item icon="table-cells">{{__('interface.import_excel')}}</flux:menu.item>
                            </flux:modal.trigger>
                        </flux:menu>
                    </flux:dropdown>
                </flux:button.group>
        </div>
    </div>
    <div class=" grid grid-cols-3 gap-6">
        <!--Táblázat fejléc-->
        <div class="pb-3 dark:border-white/20 border-b-zinc-900/20 border-b-2 cursor-pointer" wire:click="sort('question')">
            <div class="flex items-center space-x-2">
                <flux:heading size="lg">{{__('interface.question')}}</flux:heading>
                @if ($sort_by === 'question')
                    <x-heroicon-s-arrow-small-up class="w-4 h-4" x-show="$wire.sort_direction === 'asc'" />
                    <x-heroicon-s-arrow-small-down class="w-4 h-4" x-show="$wire.sort_direction === 'desc'" />
                @endif
            </div>
        </div>
        <div class="pb-3 dark:border-white/20 border-b-zinc-900/20 border-b-2 cursor-pointer" wire:click="sort('answer')">
            <div class="flex items-center space-x-2">
                <flux:heading size="lg">{{__('interface.answer')}}</flux:heading>
                @if ($sort_by === 'answer')
                    <x-heroicon-s-arrow-small-up class="w-4 h-4" x-show="$wire.sort_direction === 'asc'" />
                    <x-heroicon-s-arrow-small-down class="w-4 h-4" x-show="$wire.sort_direction === 'desc'" />
                @endif
            </div>
        </div>
        <div class="pb-3 dark:border-white/20 border-b-zinc-900/20 border-b-2">
            <flux:heading size="lg">{{__('interface.actions')}}</flux:heading>
        </div>

        <!--Táblázat adatok-->
        @foreach ($questions as $question)
            <div>
                <flux:heading>{{$question->question}}</flux:heading>
            </div>
            <div>
                <flux:heading>{{$question->answer}}</flux:heading>
            </div>
            <div class="flex justify-center space-x-4">
                <flux:button wire:click='edit({{ $question->id }})' icon="pencil-square" variant="filled"></flux:button>
                <flux:button wire:click='delete({{ $question->id }})' icon="trash" variant="danger"></flux:button>
            </div>
        @endforeach
        <div class="col-span-full">
            {{ $questions->links() }}
        </div>
    </div>

    <!--Livewire komponensek-->
    @livewire('question.import', ['site' => $site])
    @livewire('question.create', ['site' => $site])
    @livewire('question.edit')
    @livewire('question.delete')
</div>

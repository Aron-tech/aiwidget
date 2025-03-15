<?php

use Livewire\Volt\Component;
use App\Models\Site;
use Livewire\Attributes\Validate;

new class extends Component {

    public ?Site $site = null;

    #[Validate]
    public $new_question = '';
    public $new_answer = '';

    protected function rules()
    {
        return [
            'new_question' => 'required|min:5|max:200|string',
            'new_answer' => 'required|min:1|max:200|string',
        ];
    }


    public function mount(Site $site)
    {
        $this->site = $site;
    }

    private  function resetForm()
    {
        $this->new_question = '';
        $this->new_answer = '';
    }

    public function createQuestion()
    {

        $validated = $this->validate();

        $this->site->questionAnswers()->create([
            'question' => $validated['new_question'],
            'answer' => $validated['new_answer'],
        ]);

        Flux::modal('create-question')->close();

        $this->resetForm();

        $this->dispatch('reloadQuestions');

        $this->dispatch('notify', 'success', __('interface.create_success'));
    }
}; ?>

<div>
    <flux:modal name="create-question" class="md:w-96">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{__('interface.create_question_title')}}</flux:heading>
                <flux:subheading>{{ __('interface.question_subheading')}}</flux:subheading>
            </div>

            <div class="mt-4">
                <flux:input wire:model="new_question" id="new_question" label="{{ __('interface.question') }}" type="text" name="new_question" required autofocus autocomplete="new_question" placeholder="How many?" clearable />
            </div>

            <div class="mt-4">
                <flux:input wire:model="new_answer" id="new_answer" label="{{ __('interface.answer') }}" type="text" name="new_answer" required autofocus autocomplete="new_answer" placeholder="My website name" clearable />
            </div>

            <div class="flex">
                <flux:spacer />

                <flux:button wire:click='createQuestion()' type="submit" variant="primary">{{ __('interface.add_question') }}</flux:button>
            </div>
        </div>
    </flux:modal>
</div>

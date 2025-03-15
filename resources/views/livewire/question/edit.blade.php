<?php

use Livewire\Volt\Component;
use App\Models\Site;
use Livewire\Attributes\On;
use App\Models\QuestionAnswer;
use Livewire\Attributes\Validate;


new class extends Component {

    public ?Site $site = null;

    public ?QuestionAnswer $question_answer = null;

    #[Validate]
    public $question = '';
    public $answer = '';

    protected function rules()
    {
        return [
            'question' => 'required|min:5|max:200|string',
            'answer' => 'required|min:1|max:200|string',
        ];
    }

    private function resetForm()
    {
        $this->question = '';
        $this->answer = '';
    }


    #[On("editQuestion")]
    public function editQuestion($question_id, $site_id)
    {
        $this->question_answer = QuestionAnswer::where('site_id', $site_id)->findOrFail($question_id);

        $this->question = $this->question_answer->question;
        $this->answer = $this->question_answer->answer;

        Flux::modal('edit-question')->show();
    }

    public function updateQuestion()
    {
        $validated = $this->validate();

        $this->question_answer->update($validated);

        Flux::modal('edit-question')->close();

        $this->resetForm();

        $this->dispatch('reloadQuestions');

        $this->dispatch('notify', 'success', __('interface.edit_success'));
    }


};
?>

<div>
    <flux:modal name="edit-question" class="md:w-96">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{__('interface.edit_question_title')}}</flux:heading>
                <flux:subheading>{{ __('interface.question_subheading')}}</flux:subheading>
            </div>

            <div class="mt-4">
                <flux:input wire:model="question" id="question" label="{{ __('interface.question') }}" type="text" name="question" required autofocus autocomplete="question" placeholder="How many?" clearable />
            </div>

            <div class="mt-4">
                <flux:input wire:model="answer" id="answer" label="{{ __('interface.answer') }}" type="text" name="answer" required autofocus autocomplete="answer" placeholder="My website name" clearable />
            </div>

            <div class="flex">
                <flux:spacer />

                <flux:button wire:click='updateQuestion()' type="submit" variant="primary">{{ __('interface.save') }}</flux:button>
            </div>
        </div>
    </flux:modal>
</div>

<?php

use Livewire\Volt\Component;
use App\Models\QuestionAnswer;
use Livewire\Attributes\On;
use App\Models\User;
use App\Livewire\Traits\RequiresPermission;
use App\Enums\PermissionTypesEnum;

new class extends Component {

    use RequiresPermission;

    public ?User $auth_user = null;
    public ?QuestionAnswer $question_answer = null;

    #[On("deleteQuestionAnswer")]
    public function delete($question_id, $site_id)
    {
        $this->question_answer = QuestionAnswer::where('site_id', $site_id)->findOrFail($question_id);

        if(!$this->hasPermission(PermissionTypesEnum::DELETE_QUESTIONS))
            return;

        Flux::modal('delete-question-answer')->show();
    }

    public function destroy()
    {
        $this->question_answer->delete();

        Flux::modal('delete-question-answer')->close();

        $this->dispatch('reloadQuestions');

        $this->dispatch('notify', 'success', __('interface.delete_success'));
    }
};
?>

<div>
    <flux:modal name="delete-question-answer" class="min-w-[22rem]">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{__('interface.question_answer_delete_title')}}</flux:heading>
                <flux:subheading>
                    <p>{{__('interface.question_answer_delete_message') . $question_answer?->question}}</p>
                    <p class="font-medium">{{__('interface.irreversible')}}</p>
                </flux:subheading>
            </div>

            <div class="flex gap-2">
                <flux:spacer />

                <flux:modal.close>
                    <flux:button variant="ghost">{{__('interface.cancel')}}</flux:button>
                </flux:modal.close>

                <flux:button wire:click='destroy()' type="submit" variant="danger">
                    {{__('interface.delete')}}
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>
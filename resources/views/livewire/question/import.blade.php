<?php

use Livewire\Volt\Component;
use App\Models\Site;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\QuestionAnswerImport;
use App\Livewire\Traits\RequiresPermission;
use App\Enums\PermissionTypesEnum;
use Livewire\Attributes\On;


new class extends Component {

    use WithFileUploads, RequiresPermission;

    public ?Site $site = null;

    #[Validate]
    public $question_file;

    protected function rules()
    {
        return [
            'question_file' => 'required|mimes:xlsx,xls,csv|max:102400', // 100MB max
        ];
    }

    #[On("importQuestions")]
    public function openImportQuestionModal($site_id)
    {
        $this->site = Site::find($site_id);

        if(empty($this->site))
            $this->dispatch('notify', 'warning', __('interface.missing_site'));
        else if(!$this->hasPermission(PermissionTypesEnum::IMPORT_QUESTIONS))
            return;
        else
            Flux::modal('import-question')->show();
    }

    public function import()
    {
        $validated = $this->validate();

        Excel::import(new QuestionAnswerImport($this->site->id), $validated['question_file']->getRealPath());

        $this->question_file = null;

        Flux::modal('import-question')->close();

        $this->dispatch('reloadQuestions');

        $this->dispatch('notify', 'success', __('interface.import_success'));
    }
}; ?>

<div>
    <flux:modal name="import-question" class="md:w-96">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{__('interface.import')}}</flux:heading>
            </div>

            <div class="mt-4">
                <flux:input type="file" wire:model="question_file" label="{{ __('interface.excel_file') }}"/>
            </div>

            <div class="flex">
                <flux:spacer />
                <flux:button type="submit" wire:click='import()' variant="primary">{{__('interface.import')}}</flux:button>
            </div>
        </div>
    </flux:modal>
</div>

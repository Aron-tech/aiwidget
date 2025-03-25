<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Site;
use Livewire\Attributes\On;
use Livewire\WithoutUrlPagination;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\QuestionAnswerExport;
use App\Livewire\Traits\GlobalNotifyEvent;

class QuestionManager extends Component
{
    use WithPagination, WithoutUrlPagination, GlobalNotifyEvent;

    public $site;

    public $search = '';

    public $sort_by = 'question';
    public $sort_direction = 'asc';

    public function mount(Site $site)
    {
        $this->site = $site;
    }

    public function edit($question_id)
    {
        $this->dispatch('editQuestion', $question_id, $this->site->id);
    }

    public function delete($question_id)
    {
        $this->dispatch('deleteQuestionAnswer', $question_id, $this->site->id);
    }

    public function export()
    {
        if(empty($this->site->id))
            $this->notify('danger', __('interface.missing_site'));

        return Excel::download(new QuestionAnswerExport($this->site->id), 'question_answers.xlsx');
    }

    #[On("reloadQuestions")]
    public function reloadQuestions()
    {
        $this->resetPage();
    }

    public function sort($column)
    {
        if ($this->sort_by === $column) {
            $this->sort_direction = $this->sort_direction === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sort_by = $column;
            $this->sort_direction = 'asc';
        }
    }

    public function render()
    {
        $questions = $this->site->questionAnswers()
            ->select('id', 'question', 'answer', 'embedding')
            ->where(function ($query) {
                $query->where('question', 'like', '%' . $this->search . '%')
                      ->orWhere('answer', 'like', '%' . $this->search . '%');
            })
            ->orderBy($this->sort_by, $this->sort_direction)
            ->paginate(10);

        return view('livewire.question-manager', [
            'questions' => $questions,
        ]);
    }
}
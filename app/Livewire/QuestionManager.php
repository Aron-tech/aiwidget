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
use App\Models\SiteSelector;
class QuestionManager extends Component
{
    use WithPagination, WithoutUrlPagination, GlobalNotifyEvent;

    public ?Site $site;

    public $search = '';

    public $sort_by = 'question';
    public $sort_direction = 'asc';

    public function mount(SiteSelector $site_selector)
    {
        if (!$site_selector->hasSite()) {
            return redirect()->route('site.picker')->with('error', __('interface.missing_site'));
        }
        $this->site = $site_selector->getSite();

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

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $questions = $this->site->questionAnswers()
            ->when($this->search, function ($query) {
                $query->search($this->search);
            })
            ->select('id', 'question', 'answer', 'embedding')
            ->orderBy($this->sort_by, $this->sort_direction)
            ->paginate(10);

        return view('livewire.question-manager', [
            'questions' => $questions,
        ]);
    }
}
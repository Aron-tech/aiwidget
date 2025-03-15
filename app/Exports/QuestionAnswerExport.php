<?php

namespace App\Exports;

use App\Models\QuestionAnswer;
use Maatwebsite\Excel\Concerns\FromCollection;

class QuestionAnswerExport implements FromCollection
{
    protected $site_id;

    public function __construct($site_id)
    {
        $this->site_id = $site_id;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return QuestionAnswer::where('site_id', $this->site_id)->select('question', 'answer')->get();
    }

}

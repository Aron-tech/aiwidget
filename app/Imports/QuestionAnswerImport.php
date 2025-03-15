<?php

namespace App\Imports;

use App\Models\QuestionAnswer;
use Maatwebsite\Excel\Concerns\ToModel;

class QuestionAnswerImport implements ToModel
{
    protected $site_id;

    public function __construct($site_id)
    {
        $this->site_id = $site_id;
    }

    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new QuestionAnswer([
            'site_id'  => $this->site_id,
            'question' => $row[0],
            'answer'   => $row[1],
        ]);
    }
}

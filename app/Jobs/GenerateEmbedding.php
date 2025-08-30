<?php

namespace App\Jobs;

use App\Actions\GenerateEmbeddingAction;
use App\Models\QuestionAnswer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateEmbedding implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected QuestionAnswer $question_answer;

    public function __construct(QuestionAnswer $question_answer)
    {
        $this->question_answer = $question_answer;
    }

    public function handle(): void
    {
        $embedding_response = GenerateEmbeddingAction::run($this->question_answer->question);
        $this->question_answer->embedding = json_encode($embedding_response->embenddings);
        $this->question_answer->token_count = $embedding_response->usage->tokens;
        $this->question_answer->save();
    }
}

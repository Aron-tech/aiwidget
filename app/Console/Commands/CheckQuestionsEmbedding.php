<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\GenerateEmbedding;
use App\Models\QuestionAnswer;

class CheckQuestionsEmbedding extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:questions-embedding';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ellenőrzi a kérdések embedding értékét, és hiányzó esetén futtatja az Embendding jobot';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $embendding_null_questions = QuestionAnswer::whereNull('embedding')->get();

        if ($embendding_null_questions->isEmpty()) {
            $this->info('Minden kérdés rendelkezik embedding értékkel.');
            return;
        }

        foreach ($embendding_null_questions as $question) {
            $this->info('Job futtatva a következő kérdésre: ' . $question->id);
            GenerateEmbedding::dispatch($question);
        }

        $this->info('Embedding ellenőrzés és frissítés befejeződött.');
    }
}

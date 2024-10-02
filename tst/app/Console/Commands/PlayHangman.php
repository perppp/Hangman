<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class PlayHangman extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'play:hangman';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Play hangman';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        do {
            $word = trim($this->ask('Player 1, Sheiyvanet sityva romelic unda gamoicnon!'));
            $hiddenWord = strtoupper($word);
            $this->line('Player 2, Gamoicanit asoebi sityvashi, tqven gaqvt 6 shecdomis dashvebis ufleba.');
            
            $guessedLetters = [];
            $incorrectGuesses = 0;
            $maxAttempts = 6;
            $displayWord = str_repeat('_', strlen($hiddenWord));

            while ($incorrectGuesses < $maxAttempts && $displayWord !== strtoupper($hiddenWord)) {
                $this->line("Sityva: $displayWord");
                $guess = trim($this->ask('Chaweret aso:'));
                
                if (strlen($guess) !== 1 || !ctype_alpha($guess)) {
                    $this->error('Sheiyvanet mxolod erti aso');
                    continue;
                }

                $guess = strtoupper($guess);
                if (in_array($guess, $guessedLetters)) {
                    $this->error('Es aso ukve gamoiyenet, scadet sxva aso');
                    continue;
                }

                $guessedLetters[] = $guess;

                if (strpos($hiddenWord, $guess) !== false) {
                    $this->line("Sworia!");
                    $displayWord = $this->updateDisplayWord($hiddenWord, $guessedLetters);
                } else {
                    $incorrectGuesses++;
                    $this->line("Arasworia! Cdebis darchenili raodenobaa: " . ($maxAttempts - $incorrectGuesses));
                }
            }

            if ($displayWord === strtoupper($hiddenWord)) {
                $this->info("Gilocavt! Tqven gamoicanit sityva: $hiddenWord");
            } else {
                $this->error("Cdebis raodenoba gagitavda! Sityva iyo: $hiddenWord");
            }

            $this->logResult($hiddenWord, $guessedLetters, $incorrectGuesses);
            $playAgain = $this->confirm('Kidev ginda tamashi?', true);

        } while ($playAgain);
    }

    protected function updateDisplayWord($hiddenWord, $guessedLetters)
    {
        return implode('', array_map(function($letter) use ($guessedLetters) {
            return in_array($letter, $guessedLetters) ? $letter : '_';
        }, str_split($hiddenWord)));
    }

    protected function logResult($word, $guessedLetters, $incorrectGuesses)
    {
        $log = [
            'word' => $word,
            'guessed_letters' => implode(', ', $guessedLetters),
            'incorrect_guesses' => $incorrectGuesses,
            'timestamp' => now(),
        ];

        file_put_contents(storage_path('logs/hangman.log'), json_encode($log) . PHP_EOL, FILE_APPEND);
    }
}

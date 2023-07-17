<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class GameController extends Controller
{
    public function create(Request $request)
    {
        // Generate a new game
        $game = Game::create([
            'score' => 0,
   //         'user_id' => $request->user()->id, // Assuming you have authentication set up
            'user_id' => 1
        ]);

        // Get 10 random questions with choices
        $questions = Question::inRandomOrder()->limit(10)->with('choices')->get();
        // Append publicly accessible URLs for the question files
        foreach ($questions as $question) {
            $question->file_url = Storage::url($question->file);
        }

        // Prepare the response data
        $responseData = [
            'game' => $game,
            'questions' => $questions,
        ];

        // Return the JSON response
        return response()->json($responseData);
    }

    public function updateScore(Request $request, Game $game)
    {
        $game->score = $request->input('score');
        $game->save();

        // Return a success response
        return response()->json(['message' => 'Score updated successfully']);
    }
}

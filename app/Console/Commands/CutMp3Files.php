<?php

namespace App\Console\Commands;

use App\Models\Choice;
use App\Models\Question;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class CutMp3Files extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:cut-mp3-files';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $songsDirectory = base_path('songs');
        $questionsDirectory = 'question_songs';
        Storage::disk('public')->makeDirectory($questionsDirectory);

        // Get all .mp3 files in the songs directory
        $files = glob($songsDirectory . '/*.mp3');

        foreach ($files as $file) {
            // Extract the song name and artist from the file name
            $fileName = pathinfo($file, PATHINFO_FILENAME);
            $fileNameParts = explode(' - ', $fileName);
            var_dump($file);
            var_dump($fileName);

            $artist = $fileNameParts[0] ?? 'Unknown Artist';
            $songName = $fileNameParts[1] ?? 'Unknown Song';

            var_dump($artist);
            var_dump($songName);

            // Define the output file names for the cut parts
            $outputFiles = [];

            // Cut the file into 15-second parts with specific modifications
            for ($i = 1; $i <= 6; $i++) {
                $outputFile = "'{$artist} - {$songName}__part{$i}.mp3'";
                $outputBasePath = base_path('storage');
                $outputPath = "{$outputBasePath}/{$questionsDirectory}/{$outputFile}";
                $outputFiles[] = $outputFile;

                var_dump($outputFile);

                // Apply different modifications based on the cut number
                switch ($i) {
                    case 1:
                        // Keep as it is
                        shell_exec("ffmpeg -ss " . (($i - 1) * 15) . " -t 15 -i {$file} -acodec copy {$outputPath}");
                        break;
                    case 2:
                        // Speed up 2x
                        shell_exec("ffmpeg -ss " . (($i - 1) * 15 * 2) . " -t 15 -i {$file} -filter:a \"atempo=2\" {$outputPath}");
                        break;
                    case 3:
                        // Slow down 2x
                        shell_exec("ffmpeg -ss " . (($i - 1) * 15) . " -t 15 -i {$file} -filter:a \"atempo=0.5\" {$outputPath}");
                        break;
                    case 4:
                        // Reverse
                        shell_exec("ffmpeg -ss " . (($i - 1) * 15) . " -t 15 -i {$file} -af \"aeval='-val(0)':c=same\" {$outputPath}");
                        break;
                    case 5:
                        // Add modulated effect (high pitched)
                        shell_exec("ffmpeg -ss " . (($i - 1) * 15 * 7) . " -t 15 -i {$file} -af \"asetrate=48000*1.5,atempo=2\" {$outputPath}");
                        break;
                    case 6:
                        // Keep as it is
                        shell_exec("ffmpeg -ss " . (($i - 1) * 15) . " -t 15 -i {$file} -acodec copy {$outputPath}");
                        break;
                }

                Storage::disk('public')->put($outputPath, file_get_contents(str_replace("'", '', $outputPath)));
            }

            // Process the cut parts
            foreach ($outputFiles as $index => $outputFile) {
                // Create a new Question instance
                $question = new Question();
                $question->title = "Who sings this song?";
                $question->file = Storage::disk('public')->url($questionsDirectory . '/' . $outputFile);;
                $question->category_id = 1;
                // Set other properties of the question if needed
                $question->save();

                $choices = [];
                $choices[] = new Choice([
                    'text' => $artist, // Correct choice
                    'is_correct' => true,
                ]);

                // Get similar artists
                $similarArtists = $this->getSimilarArtists($artist);

                // Shuffle and take three random similar artists
                $randomSimilarArtists = collect($similarArtists)->shuffle()->take(3);

                foreach ($randomSimilarArtists as $similarArtist) {
                    $choices[] = new Choice([
                        'text' => $similarArtist,
                        'is_correct' => false,
                    ]);
                }

                // Save the choices for the question
                $question->choices()->saveMany($choices);
            }

            // Clean up the generated cut parts
            /*foreach ($outputFiles as $outputFile) {
                unlink($outputFile);
            } */
            break;
        }
        $this->info('All .mp3 files have been cut into 15-second parts.');

        return 0;
    }

    /**
     * Get similar artists from Last.fm API.
     *
     * @param string $artist
     * @return array
     */
    private function getSimilarArtists($artist)
    {
        $apiKey = '765598fbd76e66de46ac270912a7bf03'; // Replace with your Last.fm API key
        $limit = 5; // Number of similar artists to retrieve

        $client = new Client([
            'base_uri' => 'http://ws.audioscrobbler.com/2.0/',
            'timeout' => 2.0,
            'headers' => [
                'Accept' => 'application/json',
            ],
        ]);

        $response = $client->get('', [
            'query' => [
                'method' => 'artist.getsimilar',
                'artist' => $artist,
                'api_key' => $apiKey,
                'format' => 'json',
                'limit' => $limit,
            ],
        ]);

        $data = json_decode($response->getBody(), true);

        $similarArtists = [];

        if (isset($data['similarartists']['artist'])) {
            foreach ($data['similarartists']['artist'] as $similarArtist) {
                $similarArtists[] = $similarArtist['name'];
            }
        }

        return $similarArtists;
    }
}

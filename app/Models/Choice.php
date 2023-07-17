<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
/**
 * Class Choice
 *
 * @property int $id
 * @property int $question_id
 * @property string $text
 * @property bool $is_correct
 *
 * @package App\Models
 */
class Choice extends Model
{
    use HasFactory;

    protected $fillable = ['text', 'is_correct'];

    // Relationships
    public function question()
    {
        return $this->belongsTo(Question::class);
    }
}

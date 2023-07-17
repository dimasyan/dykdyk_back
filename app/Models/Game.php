<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
/**
 * Class Game
 *
 * @property int $id
 * @property int $score
 * @property int $user_id
 *
 * @package App\Models
 */
class Game extends Model
{
    use HasFactory;

    protected $fillable = ['score', 'user_id'];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

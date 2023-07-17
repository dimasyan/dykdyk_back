<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Question
 *
 * @property int $id
 * @property int $category_id
 * @property string $title
 * @property string $file
 * @property string $song_name
 * @property string $artist
 *
 * @package App\Models
 */
class Question extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'is_active', 'file'];

    // Relationships
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function choices()
    {
        return $this->hasMany(Choice::class);
    }
}

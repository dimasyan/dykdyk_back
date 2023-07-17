<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
/**
 * Class Category
 *
 * @property int $id
 * @property string $name
 *
 * @package App\Models
 */
class Category extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    // Relationships
    public function questions()
    {
        return $this->hasMany(Question::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Chapter extends Model
{
    use HasFactory;

    protected $fillable = ['book_id', 'title', 'position'];

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    public function pages(): HasMany
    {
        return $this->hasMany(Page::class)->orderBy('position');
    }
}

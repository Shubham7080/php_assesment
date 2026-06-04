<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookVersion extends Model
{
    use HasFactory;

    protected $fillable = ['book_id', 'created_by', 'version_number', 'snapshot'];

    protected function casts(): array
    {
        return [
            'snapshot' => 'array',
        ];
    }

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

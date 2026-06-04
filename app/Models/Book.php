<?php

namespace App\Models;

use App\Enums\BookStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Book extends Model
{
    use HasFactory;

    protected $fillable = ['author_id', 'title', 'description', 'genre', 'status', 'moderation_report', 'published_at'];

    protected $attributes = ['status' => BookStatus::Draft->value];

    protected function casts(): array
    {
        return [
            'status' => BookStatus::class,
            'moderation_report' => 'array',
            'published_at' => 'datetime',
        ];
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function chapters(): HasMany
    {
        return $this->hasMany(Chapter::class)->orderBy('position');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(BookVersion::class)->orderBy('version_number');
    }

    public function isPublished(): bool
    {
        return $this->status === BookStatus::Published;
    }

    public function isEditable(): bool
    {
        return ! in_array($this->status, [BookStatus::Published, BookStatus::Submitted, BookStatus::UnderReview], true);
    }
}

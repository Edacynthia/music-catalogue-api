<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Track extends Model
{
    use HasFactory;

    protected $fillable = [
    'title',
    'artist_name',
    'genre',
    'duration',
    'release_date',
    'publication_status',
];

protected function casts(): array
{
    return [
        'duration' => 'integer',
        'release_date' => 'date',
    ];
}

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
<?php

declare(strict_types=1);

namespace BaseCodeOy\Messageable\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

final class Message extends Model
{
    use SoftDeletes;

    protected $table = 'messages';

    protected $touches = ['thread'];

    protected $guarded = ['id', 'created_at', 'updated_at'];

    protected $dates = ['created_at', 'updated_at', 'deleted_at'];

    public function thread(): BelongsTo
    {
        return $this->belongsTo(Thread::class);
    }

    public function creator(): MorphTo
    {
        return $this->morphTo('creator');
    }

    public function participants(): HasMany
    {
        return $this->hasMany(Participant::class, 'thread_id', 'thread_id');
    }

    public function recipients(): Collection
    {
        return $this->participants()
            ->where('participant_id', '!=', $this->participant_id)
            ->where('participant_type', '!=', $this->participant_type);
    }
}

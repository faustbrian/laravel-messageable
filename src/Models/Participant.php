<?php

declare(strict_types=1);

namespace BaseCodeOy\Messageable\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

final class Participant extends Model
{
    use SoftDeletes;

    protected $table = 'participants';

    protected $guarded = ['id', 'created_at', 'updated_at'];

    protected $dates = ['created_at', 'updated_at', 'deleted_at', 'last_read'];

    public function thread(): BelongsTo
    {
        return $this->belongsTo(Thread::class);
    }

    public function model(): Morphto
    {
        return $this->morphTo('participant');
    }
}

<?php declare(strict_types=1);

/**
 * Copyright (C) BaseCode Oy - All Rights Reserved
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace BaseCodeOy\Messageable\Concerns;

use BaseCodeOy\Messageable\Models\Message;
use BaseCodeOy\Messageable\Models\Participant;
use BaseCodeOy\Messageable\Models\Thread;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasMessages
{
    public function messages(): MorphMany
    {
        return $this->morphMany(Message::class, 'creator');
    }

    public function threads(): BelongsToMany
    {
        return $this->belongsToMany(Thread::class, 'participants', 'participant_id');
    }

    public function newMessagesCount(): int
    {
        return \count($this->threadsWithNewMessages());
    }

    public function threadsWithNewMessages(): array
    {
        $threadsWithNewMessages = [];
        $participants = Participant::where('participant_id', $this->id)
            ->where('participant_type', \get_class($this))
            ->lists('last_read', 'thread_id');

        if ($participants) {
            $threads = Thread::whereIn('id', \array_keys($participants->toArray()))->get();

            foreach ($threads as $thread) {
                if ($thread->updated_at > $participants[$thread->id]) {
                    $threadsWithNewMessages[] = $thread->id;
                }
            }
        }

        return $threadsWithNewMessages;
    }
}

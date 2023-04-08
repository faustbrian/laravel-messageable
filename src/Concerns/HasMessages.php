<?php

declare(strict_types=1);

namespace PreemStudio\Messageable\Concerns;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use PreemStudio\Messageable\Models\Message;
use PreemStudio\Messageable\Models\Participant;
use PreemStudio\Messageable\Models\Thread;

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

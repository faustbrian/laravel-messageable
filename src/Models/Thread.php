<?php

declare(strict_types=1);

namespace BombenProdukt\Messageable\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

final class Thread extends Model
{
    use SoftDeletes;

    protected $table = 'threads';

    protected $guarded = ['id', 'created_at', 'updated_at'];

    protected $dates = ['created_at', 'updated_at', 'deleted_at'];

    public static function getAllLatest(): Collection
    {
        return self::latest('updated_at');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    public function participants(): HasMany
    {
        return $this->hasMany(Participant::class);
    }

    public function creator(): Model
    {
        return $this->messages()->oldest()->first()->creator;
    }

    public function getLatestMessage(): Message
    {
        return $this->messages()->latest()->first();
    }

    public function participantsIdsAndTypes($participant = null): array
    {
        $participants = $this->participants()
            ->withTrashed()
            ->lists('participant_id', 'participant_type');

        if ($participant) {
            $participants[] = $participant;
        }

        return $participants;
    }

    public function scopeForModel($query, $participant)
    {
        return $query->join('participants', 'threads.id', '=', 'participants.thread_id')
            ->where('participants.participant_id', $participant->id)
            ->where('participants.participant_type', $participant::class)
            ->where('participants.deleted_at', null)
            ->select('threads.*');
    }

    public function scopeForModelWithNewMessages($query, $participant)
    {
        return $query->join('participants', 'threads.id', '=', 'participants.thread_id')
            ->where('participants.participant_id', $participant->id)
            ->where('participants.participant_type', $participant::class)
            ->whereNull('participants.deleted_at')
            ->where(function ($query): void {
                $query->where('threads.updated_at', '>', 'participants.last_read')
                    ->orWhereNull('participants.last_read');
            })
            ->select('threads.*');
    }

    public function addMessage($data, Model $creator): bool
    {
        $message = (new Message())->fill(\array_merge($data, [
            'creator_id' => $creator->id,
            'creator_type' => $creator::class,
        ]));

        return (bool) $this->messages()->save($message);
    }

    public function addMessages(array $messages): void
    {
        foreach ($messages as $message) {
            $this->addMessage($message['data'], $message['creator']);
        }
    }

    public function addParticipant(Model $participant): bool
    {
        $participant = (new Participant())->fill([
            'participant_id' => $participant->id,
            'participant_type' => $participant::class,
            'last_read' => new Carbon(),
        ]);

        return (bool) $this->participants()->save($participant);
    }

    public function addParticipants(array $participants): void
    {
        foreach ($participants as $participant) {
            $this->addParticipant($participant);
        }
    }

    public function markAsRead($userId): bool
    {
        try {
            $participant = $this->getParticipantFromModel($userId);
            $participant->last_read = new Carbon();
            $participant->save();

            return true;
        } catch (ModelNotFoundException $e) {
            return false;
        }
    }

    public function isUnread($participant): bool
    {
        try {
            $participant = $this->getParticipantFromModel($participant);

            if ($this->updated_at > $participant->last_read) {
                return true;
            }
        } catch (ModelNotFoundException $e) {
            return false;
        }

        return false;
    }

    public function getParticipantFromModel($participant): Participant
    {
        return $this->participants()
            ->where('participant_id', $participant->id)
            ->where('participant_type', $participant::class)
            ->firstOrFail();
    }

    public function activateAllParticipants(): void
    {
        foreach ($this->participants()->withTrashed()->cursor() as $participant) {
            $participant->restore();
        }
    }

    public function hasParticipant($participant): bool
    {
        return $this->participants()
            ->where('participant_id', '=', $participant->id)
            ->where('participant_type', '=', $participant::class)
            ->count() > 0;
    }
}

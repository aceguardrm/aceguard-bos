<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

class AccessAuditEvent extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'client_id',
        'actor_id',
        'subject_user_id',
        'invitation_id',
        'source',
        'action',
        'previous_role',
        'new_role',
        'created_at',
    ];

    protected function casts(): array
    {
        return ['created_at' => 'immutable_datetime'];
    }

    protected static function booted(): void
    {
        static::updating(function (): void {
            throw new LogicException('Audit events cannot be updated.');
        });

        static::deleting(function (): void {
            throw new LogicException('Audit events cannot be deleted.');
        });
    }

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(User::class, 'subject_user_id');
    }
}

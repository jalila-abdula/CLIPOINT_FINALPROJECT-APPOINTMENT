<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Appointment extends Model
{
    use HasFactory;

    public const STATUS_SCHEDULED = 'Scheduled';
    public const STATUS_CONFIRMED = 'Confirmed';
    public const STATUS_COMPLETED = 'Completed';
    public const STATUS_CANCELLED = 'Cancelled';
    public const STATUS_NO_SHOW = 'No Show';

    public const STATUSES = [
        self::STATUS_SCHEDULED,
        self::STATUS_CONFIRMED,
        self::STATUS_COMPLETED,
        self::STATUS_CANCELLED,
        self::STATUS_NO_SHOW,
    ];

    public const BOOKED_STATUSES = [
        self::STATUS_SCHEDULED,
        self::STATUS_CONFIRMED,
    ];

    public const SERVICE_TYPE_CONSULTATION = 'Consultation';
    public const SERVICE_TYPE_TECHNICAL = 'Technical';
    public const SERVICE_TYPE_TRAINING = 'Training';

    public const SERVICE_TYPES = [
        self::SERVICE_TYPE_CONSULTATION,
        self::SERVICE_TYPE_TECHNICAL,
        self::SERVICE_TYPE_TRAINING,
    ];

    protected $fillable = [
        'client_id',
        'staff_id',
        'service_type',
        'appointment_date',
        'appointment_time',
        'status',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'appointment_date' => 'date',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(User::class, 'staff_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function serviceRecord(): HasOne
    {
        return $this->hasOne(ServiceRecord::class);
    }

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($user->isStaff()) {
            return $query->where('staff_id', $user->id);
        }

        return $query;
    }

    public function scopeBooked(Builder $query): Builder
    {
        return $query->whereIn('status', self::BOOKED_STATUSES);
    }

    public function scopeReadyForNoShow(Builder $query, ?\DateTimeInterface $reference = null): Builder
    {
        $reference ??= now(config('app.timezone'));

        return $query
            ->booked()
            ->whereDate('appointment_date', '<', $reference->format('Y-m-d'));
    }

    public static function markReadyForNoShow(?\DateTimeInterface $reference = null): int
    {
        return static::query()
            ->readyForNoShow($reference)
            ->update(['status' => self::STATUS_NO_SHOW]);
    }
}

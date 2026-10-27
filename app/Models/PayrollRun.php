<?php

namespace App\Models;

use App\Enums\PayrollStatus;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['month', 'status', 'paid_at'])]
class PayrollRun extends Model
{
    public function items(): HasMany
    {
        return $this->hasMany(PayrollItem::class, 'run_id');
    }

    // For example "October 2026".
    public function monthLabel(): string
    {
        return CarbonImmutable::createFromFormat('!Y-m', $this->month)->format('F Y');
    }

    protected function casts(): array
    {
        return [
            'status' => PayrollStatus::class,
            'paid_at' => 'datetime',
        ];
    }
}

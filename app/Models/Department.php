<?php

namespace App\Models;

use App\Enums\DepartmentStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'status'])]
class Department extends Model
{
    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }

    // Only active departments are offered when creating or editing an employee.
    public function scopeActive(Builder $query): void
    {
        $query->where('status', DepartmentStatus::Active);
    }

    public function suspend(): void
    {
        $this->update(['status' => DepartmentStatus::Suspended]);
    }

    public function reactivate(): void
    {
        $this->update(['status' => DepartmentStatus::Active]);
    }

    protected function casts(): array
    {
        return [
            'status' => DepartmentStatus::class,
        ];
    }
}

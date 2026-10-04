<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    protected $fillable = [
        'title',
        'description',
        'amount',
        'expense_date',
        'user_id',
    ];

    protected $casts = [
        'expense_date' => 'date',
        'amount'       => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    protected function createdBy(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->user_id,
            set: fn ($value) => ['user_id' => $value],
        );
    }

    protected function expenseAt(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->expense_date,
            set: fn ($value) => ['expense_date' => $value],
        );
    }
}


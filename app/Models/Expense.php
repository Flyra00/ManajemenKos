<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    //
    protected $fillable = [
        "title",
        "description",
        "amount",
        "expense_at",
        "created_by",
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

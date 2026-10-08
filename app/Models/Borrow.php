<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Borrow extends Model
{
    use HasFactory;

    protected $fillable = [
        'book_id',
        'user_id',
        'borrowed_at',
        'returned_at',
        'status'
    ];

    protected $casts = [
        'borrowed_at' => 'datetime',
        'returned_at' => 'datetime',
    ];

    // 📘 A borrow belongs to one book
    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    // 👤 A borrow belongs to one user
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

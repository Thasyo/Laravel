<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Notifications\Notifiable;

class Produto extends Model
{
    use HasFactory, Notifiable;
    protected $table = "produtos";

    public function user(): BelongsTo {
        return $this->belongsTo(User::class, 'id_user');
    }

    public function categoria(): BelongsTo {
        return $this->belongsTo(Categoria::class, 'id_categoria');
    }
}

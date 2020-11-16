<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Produto extends Model
{
    protected $fillable = [
        'produto',
        'tamanho',
        'marca',
        'preco'
    ];
}

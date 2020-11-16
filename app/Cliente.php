<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    protected $fillable = [
        'id',
        'nome',
        'cpf'
    ];

    /**
     * Obtenha os carrinhos do cliente.
     */
    public function carrinhos()
    {
        return $this->hasMany('App\Carrinho');
    }
}

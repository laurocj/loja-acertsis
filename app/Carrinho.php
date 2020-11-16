<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Carrinho extends Model
{
    protected $fillable = [
        'codigo',
        'data',
        'valorTotal',
        'cliente_id'
    ];

    /**
     * Obtenha os itens do carrinho
     */
    public function itens()
    {
        return $this->hasMany('App\Item');
    }

    /**
     * Obtenha o ciente do carrinho
     */
    public function cliente()
    {
        return $this->hasMany('App\Cliente');
    }
}

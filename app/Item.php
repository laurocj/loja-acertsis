<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    /**
     * Tabela correspondente desse modelo no banco de dados.
     *
     * @var string
     */
    protected $table = 'itens';

    protected $fillable = [
        'carrinho_id',
        'produto_id'
    ];

    /**
     * Obtenha o carrinho deste item
     */
    public function carrinho()
    {
        return $this->hasOne('App\Carrinho');
    }

    /**
     * Obtenha o produto deste item
     */
    public function produto()
    {
        return $this->hasOne('App\Produto');
    }
}

<?php

use App\Carrinho;
use App\Cliente;
use App\Item;
use App\Produto;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


Route::get('/import',function() {

    Cliente::insert(json_decode(file_get_contents('http://www.mocky.io/v2/5de67e9f370000540009242b'), true));

    $carrinhos = json_decode(file_get_contents('http://www.mocky.io/v2/5e960a2d2f0000f33b0257c4'), true);
    foreach($carrinhos as $carrinho_array) {

        $carrinho = new Carrinho();
        $carrinho->codigo = $carrinho_array['codigo'];
        $carrinho->data = date('Y-m-d', strtotime($carrinho_array['data']));
        $carrinho->cliente_id = Cliente::where('cpf', $carrinho_array['cliente'])->get()->first()->id;
        $carrinho->valorTotal = $carrinho_array['valorTotal'];
        $carrinho->save();

        foreach($carrinho_array['itens'] as $item) {
            $produto = new Produto();
            $prod = $produto
                        ->where('nome',$item['produto'])
                        ->where('tamanho',$item['tamanho'])
                        ->where('marca',$item['marca'])
                        ->where('preco',$item['preco'])
                        ->get()->first();
            if($prod) {
                $produto = $prod;
            } else {
                $produto->nome = $item['produto'];
                $produto->tamanho = $item['tamanho'];
                $produto->marca = $item['marca'];
                $produto->preco = $item['preco'];
                $produto->save();
            }

            $item = new Item();
            $item->produto_id = $produto->id;
            $item->carrinho_id = $carrinho->id;
            $item->save();
        }
    }
    return 'ok';
});

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

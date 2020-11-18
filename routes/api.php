<?php

use App\Carrinho;
use App\Cliente;
use App\Item;
use App\Produto;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

/**
 * 1. Liste os clientes ordenados pelo menor valor total em compras
 *   SELECT `clientes`.`nome`, `clientes`.`cpf`
 *   FROM `clientes`
 *   INNER JOIN (
 *       SELECT `cliente_id`, MIN(valorTotal) AS valorTotal
 *       FROM `carrinhos`
 *       GROUP BY `cliente_id`) AS `menores_compras` ON `clientes`.`id` = `menores_compras`.`cliente_id`
 *   ORDER BY `menores_compras`.`valorTotal` ASC
 */
Route::get('/clientes', function () {

    $menoresCompras = Carrinho::select('cliente_id', DB::raw('MIN(valorTotal) AS valorTotal'))->groupBy('cliente_id');

    return Cliente::select('clientes.nome', 'clientes.cpf','valorTotal')
        ->joinSub($menoresCompras, 'menores_compras', function ($join) {
            $join->on('clientes.id', '=', 'menores_compras.cliente_id');
        })
        ->orderBy('menores_compras.valorTotal', 'ASC')
        ->get();
});

/**
 * 2. Mostre o cliente com maior compra única neste ano (2019)
 *
 * SELECT `clientes`.`nome`, `clientes`.`cpf`
 * FROM  `clientes`
 * INNER  JOIN  (
 *    SELECT  `cliente_id`
 *    FROM  `carrinhos`
 *    WHERE  year(`carrinhos.data`) = '2019'
 *    AND `carrinhos`.`id` IN  (
 *           SELECT  `itens`.`carrinho_id`
 *           FROM  `itens`
 *           GROUP  BY  `itens`.`carrinho_id`
 *           HAVING  COUNT(itens.carrinho_id) = 1
 *    )
 *    GROUP  BY  `carrinhos`.`cliente_id`
 * ) AS   `unica_compra` ON  `clientes`.`id` = `unica_compra`.`cliente_id`;
 */
Route::get('/clientes-maior-compra-unica/{ano?}', function ($ano = '2019') {

    $unicaCompra = Carrinho::select('cliente_id')
        ->whereYear('carrinhos.data', $ano)
        ->whereIn('carrinhos.id', function ($query) {
            $query->select('itens.carrinho_id')
                ->from('itens')
                ->groupBy('itens.carrinho_id')
                ->havingRaw('COUNT(itens.carrinho_id) = ?', [1]);
        })
        ->groupBy('carrinhos.cliente_id');

    return Cliente::select('clientes.nome', 'clientes.cpf')
        ->joinSub($unicaCompra, 'unica_compra', function ($join) {
            $join->on('clientes.id', '=', 'unica_compra.cliente_id');
        })
        ->get();
});

/**
 * 3. Liste os clientes que mais realizaram compras no ano passado (2018)
 *
 * SELECT DISTINCT `clientes`.`nome`, `clientes`.`cpf`
 * FROM `clientes`
 * INNER JOIN (
 * 	  SELECT `cliente_id`
 * 	  FROM `carrinhos`
 * 	  INNER JOIN (
 * 		SELECT `itens`.`carrinho_id`, COUNT(itens.carrinho_id) AS quant_carrinho
 * 		FROM `itens`
 * 		GROUP BY `itens`.`carrinho_id`
 * 		HAVING COUNT(itens.carrinho_id) > ?
 * 		ORDER BY `quant_carrinho` desc
 * 	  ) AS `mais_itens` ON `carrinhos`.`id` = `mais_itens`.`carrinho_id`
 * 	 WHERE year(`carrinhos`.`data`) = ?
 * ) AS `carrinho_mais_itens` ON `clientes`.`id` = `carrinho_mais_itens`.`cliente_id`
 */
Route::get('/clientes-com-mais-de/{quant}/{seletor}/{ano?}', function ($quant = 1, $seletor = 'carrinhos', $ano = '2018') {

    if ($seletor == 'itens') {
        $maisItensQue = Item::select('itens.carrinho_id', DB::raw('COUNT(itens.carrinho_id) AS quant_itens'))
            ->groupBy('itens.carrinho_id')
            ->having('quant_itens', '>', $quant)
            ->orderBy('quant_itens', 'DESC');

        $carrinhosSelecionados = Carrinho::select('cliente_id')
            ->whereYear('carrinhos.data', $ano)
            ->joinSub($maisItensQue, 'mais_itens', function ($join) {
                $join->on('carrinhos.id', '=', 'mais_itens.carrinho_id');
            });

    } else {
        $carrinhosSelecionados = Carrinho::select('cliente_id', DB::raw('COUNT(carrinhos.cliente_id) AS quant_carrinho'))
            ->whereYear('carrinhos.data', $ano)
            ->groupBy('carrinhos.cliente_id')
            ->having('quant_carrinho', '>', $quant)
            ->orderBy('quant_carrinho', 'DESC');
    }

    return Cliente::distinct()->select('clientes.nome', 'clientes.cpf')
        ->joinSub($carrinhosSelecionados, 'carrinhos_selecionados', function ($join) {
            $join->on('clientes.id', '=', 'carrinhos_selecionados.cliente_id');
        })
        ->get();
});

/**
 * 4. Recomende uma peça de roupa para um determinado cliente a partir do histórico de compras
 */

Route::get('/recomenda-para/{cliente}', function ($clienteId = 1) {

    $carrinhosSelecionados = Carrinho::select('produto_id')
            ->join('itens','itens.carrinho_id','=','carrinhos.id')
            ->where('carrinhos.cliente_id', $clienteId)
            ->groupBy('itens.produto_id')
            ->orderByRaw('COUNT(produto_id) DESC')
            ->limit(1);

    return Produto::select('produtos.nome', 'produtos.marca', 'produtos.tamanho')
        ->joinSub($carrinhosSelecionados, 'carrinhos_selecionados', function ($join) {
            $join->on('produtos.id', '=', 'carrinhos_selecionados.produto_id');
        })
        ->get();
});

/**
 * Importação
 */
Route::get('/import', function () {

    Cliente::insert(json_decode(file_get_contents('http://www.mocky.io/v2/5de67e9f370000540009242b'), true));

    $carrinhos = json_decode(file_get_contents('http://www.mocky.io/v2/5e960a2d2f0000f33b0257c4'), true);
    foreach ($carrinhos as $carrinho_array) {

        $carrinho = new Carrinho();
        $carrinho->codigo = $carrinho_array['codigo'];
        $carrinho->data = date('Y-m-d', strtotime($carrinho_array['data']));
        $carrinho->cliente_id = Cliente::where('cpf', $carrinho_array['cliente'])->get()->first()->id;
        $carrinho->valorTotal = $carrinho_array['valorTotal'];
        $carrinho->save();

        foreach ($carrinho_array['itens'] as $item) {
            $produto = new Produto();
            $prod = $produto
                ->where('nome', $item['produto'])
                ->where('tamanho', $item['tamanho'])
                ->where('marca', $item['marca'])
                ->where('preco', $item['preco'])
                ->get()->first();
            if ($prod) {
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

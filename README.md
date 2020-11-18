# loja-acertsis
Desafio técnico para Acertsis

## Sobre a aplicação

Utilizei o [Laravel](https://laravel.com) como o framework pelas facidades porporcionadas para a construção de query contra o banco de dados.

## Para rodar o projeto

Clone o projeto:
```sh
git clone https://github.com/laurocj/loja-acertsis.git
```
Navege até a pasta do projeto.

Execute:

```sh
composer install

cp .env.example .env
```
Defina as configurações do banco de dados no .env e rode as migrations para criar o banco:

```sh
php artisan migrate
```

Para podular o banco bata na rota de importação

```sh
http://localhost/loja-acertsis/public/api/import
```

### As rotas 

1. Liste os clientes ordenados pelo menor valor total em compras
```sh
http://localhost/loja-acertsis/public/api/clientes
```

2. Mostre o cliente com maior compra única neste ano (2019)
```sh
http://localhost/loja-acertsis/public/api/clientes-maior-compra-unica/{ano?}
```

3. Liste os clientes que mais realizaram compras no ano passado (2018)
seletor pode ser itens ou carrinhos para ver por quantidade de itens comprados ou quantidade de carrinhos comprados no ano
```sh
/clientes-com-mais-de/{quant}/{seletor}/{ano?}
```

4. Recomende uma peça de roupa para um determinado cliente a partir do histórico de compras
```json
http://localhost/loja-acertsis/public/api/recomenda-para/{clienteId}
```

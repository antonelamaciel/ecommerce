<?php
$cart = [];
$id = 1;
$qty = 1;

function add(&$cart, $id, $qty, $variants) {
    $variants = $variants ? trim($variants) : null;
    if ($variants === '') $variants = null;
    $compositeId = $variants ? $id . '-' . md5($variants) : (string)$id;

    if (empty($cart[$compositeId])) {
        $cart[$compositeId] = [
            'id' => $id,
            'qty' => $qty,
            'variants' => $variants
        ];
    } else {
        $cart[$compositeId]['qty'] += $qty;
    }
}

add($cart, 1, 1, 'Color: Rojo');
add($cart, 1, 1, 'Color: Rojo');
add($cart, 1, 1, 'Color: Azul');
add($cart, 1, 1, '');

print_r($cart);

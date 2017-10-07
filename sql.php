<?php

$mysql = mysql_connect('e1r7p1.42.fr', 'root', 'qwerty123')
or die('Impossible de se connecter : ' . mysql_error());

echo 'connected';

mysql_select_db('my_database') or die('Impossible de sélectionner la base de données');

/*
 * - Utilisateurs
 *   - login
 *   - password (hashed)
 *   - buy_history
 *     - id
 *     - count
 * - Produits
 *   - categories
 *   - products
 *     - id
 *     - image
 *     - price
 *     - discount
 *     - stock
 *     - description
 *   - variants
 *     - main product
 *     - (...)
 * - Statistics
 *   - log of purchases
 *     - product ids
 *     - client id
 *     - price_spent
 *
 * API (GET):
 *    http://.../api.php?method=auth&login=machin&passwd=truc
 *
 *   Methods:
 *    - auth (login, password)
 *      > login, cart
 *      > lougout removes session login
 *    - cart
 *      > products[], count
 *    - edit_cart (pid, change)
 *      > success
 *
 *    - product (id, [variant])
 *      > product info
 *    - category (id)
 *      > products[]
 *
 *    - user (id)
 *      > profile complet
 *   
 */

?>

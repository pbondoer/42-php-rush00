<?PHP
session_start();
$mysql = mysqli_connect('e1r7p1.42.fr', 'root', 'qwerty123')
or die('Impossible de se connecter : ' . mysqli_error($mysql));
mysqli_query($mysql, "USE db;")
or die('Impossible de selectionner db : ' . mysqli_error($mysql));

function auth($mysql, $login, $passwd, $cart)
{
	$_SESSION['login'] = NULL;
	$_SESSION['id_login'] = 0;
	if ($login == NULL || $passwd == NULL)
		return (0);
	$users = mysqli_query($mysql, "SELECT * FROM users", MYSQLI_STORE_RESULT)
	or die ('Impossible de recuperer la tables des users : ' . mysqli_error($mysql));
	$users = mysqli_fetch_all($users);
	foreach ($users as $usr)
	{
		if ($usr['1'] === $login && $usr['2'] === $passwd)
		{
			$_SESSION['login'] = $login;
			$_SESSION['id_login'] = $usr['0'];
			$_SESSION['cart'] = $cart;
			echo "Connected as $login\n"; //debug
			clearStoredResults($mysqli);
			return ($login);
		}
	}
	echo "Login or passwd is wrong\n";   //debug
	clearStoredResults($mysqli);
	return (FALSE);
}

function cart($mysql)
{
	if ($_SESSION['cart'] == NULL)
		$_SESSION['cart'] = serialize(array());
	return (unserialize($_SESSION['cart']));
}

function get_stock($mysql, $pid)
{
	if ($pid === 0)
		return (FALSE);
	$list_products = mysqli_query($mysql, "SELECT * FROM products")
	or die ('Impossible de recuperer la table des products : ' . mysqli_error($mysql));
	$list_products = mysqli_fetch_all($list_products);
	foreach ($list_products as $product)
		if ($product[0] === $pid)
			return ($product[5]);
}

function edit_cart($mysql, $pid, $change)
{
	if ($pid === 0)
		return (FALSE);
	$cart = cart($mysql);
	foreach ($cart as $key => &$product)
	{
		if ($product["id"] === $pid)
		{
			if ($change == 0 || ($product["count"] + $change < 0))
				unset($cart[$key]);
			else if ($product["count"] + $change > ($max_stock = get_stock($mysql, $pid)))
					$product["count"] = $max_stock;
			else
					$product["count"] += $change;
			$change = -1;
			break ;
		}
	}
	if ($change > 0)
		$cart[] = array("id" => $pid, "count" => ($change > ($max_stock = get_stock($mysql, $pid))) ? $max_stock : $change);
	print_r($cart);
	$_SESSION['cart'] = serialize($cart);
}

if (($method = $_GET["method"]) != NULL)
	switch ($method)
	{
		case "auth":
			$ret = auth($mysql, $_GET["login"], $_GET["passwd"], $_GET["cart"]); // Rajouter hash du mot de passe
			break ;
		case "cart":
			$ret = cart($mysql);
			break ;
		case "edit_cart":
			$ret = edit_cart($mysql, $_GET["pid"], $_GET["change"]);
			break ;
		default :
			$ret = FALSE;
			break ;
	}
mysqli_close($mysql);
return ($ret);
?>
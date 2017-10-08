<?PHP
session_start();
$mysql = mysqli_connect('e1r7p2.42.fr', 'root', 'qwerty123')
or die('Impossible de se connecter : ' . mysqli_error($mysql));
mysqli_query($mysql, "USE db;")
or die('Impossible de selectionner db : ' . mysqli_error($mysql));

function encode_ret($error, $result)
{
	$ret = array("error" => $error,
			"result" => $result);
	return ($ret);
}

function array_add($a1, $a2) 
{
	$aRes = $a1;
	foreach (array_slice(func_get_args(), 1) as $aRay) {
		foreach (array_intersect_key($aRay, $aRes) as $key => $val) $aRes[$key] += $val;
			$aRes += $aRay; }
	return $aRes;
}

function init_auth_cart($mysql, $uid)
{
	$no_auth_cart = check_cart();
	$auth_cart_qr = mysqli_query($mysql, "SELECT cart FROM users WHERE id = $uid");
	if ($auth_cart_qr->num_rows === 1)
		$auth_cart = mysqli_fetch_assoc($auth_cart_qr);
	else
		$auth_cart = array();
	return (array_add($no_auth_cart, $auth_cart));
}

function auth($mysql, $login, $passwd)
{
	$_SESSION['login'] = NULL;
	$_SESSION['id_login'] = 0;
	if ($login == NULL || $passwd == NULL)
		return (encode_ret(TRUE, "Login or Password is empty"));
	$users = mysqli_query($mysql, "SELECT login, id FROM users WHERE login = '$login' AND password = '$passwd';")
	or die ('Impossible de recuperer la tables des users : ' . mysqli_error($mysql));
	if ($users->num_rows === 1)
	{
		$usr = mysqli_fetch_assoc($users);
		$_SESSION['login'] = $login;
		$_SESSION['id_login'] = $usr["id"];
		$_SESSION['cart'] = init_auth_cart($mysql, $usr["id"]);
		mysqli_free_result($users);
		return (encode_ret(FALSE, $login));
	}
	return (encode_ret(TRUE, "Login or password is wrong"));
}

function check_cart()
{
	if ($_SESSION['cart'] == NULL)
		$_SESSION['cart'] = serialize(array());
	return (unserialize($_SESSION['cart']));
}

function cart($mysql)
{
	check_cart();
	$cart = unserialize($_SESSION['cart']);
	$cart_with_info = array();
	foreach ($cart as $key => $product)
	{
		$pid = $product["id"];
		$new = mysqli_query($mysql, "SELECT * FROM products WHERE p_id = $pid;");
		$new = mysqli_fetch_assoc($new);
		$new["count"] = $product["count"];
		$cart_with_info[$key] = $new;
	}
	return (encode_ret(FALSE, $cart_with_info));
}

function get_stock($mysql, $pid)
{
	if ($pid === 0 || (is_numeric($pid) === FALSE))
		return (encode_ret(TRUE, "Product id is incorrect"));
	if (($list_products = mysqli_query($mysql, "SELECT stock FROM products WHERE p_id = $pid;")) === FALSE)
		return (encode_ret(TRUE, "Product id is incorrect"));
	$stock = mysqli_fetch_assoc($list_products)["stock"];
	mysqli_free_result($list_products);
	return (encode_ret(FALSE, $stock));
}

function stock_id($mysql, $pid)
{
	if ($pid === 0 || (is_numeric($pid) === FALSE))
		return (FALSE);
	if (($list_products = mysqli_query($mysql, "SELECT stock FROM products WHERE p_id = $pid;")) === FALSE)
		return (FALSE);
	$stock = mysqli_fetch_assoc($list_products)["stock"];
	mysqli_free_result($list_products);
	return ($stock);
}

function edit_cart($mysql, $pid, $change)
{
	if ($pid === -1)
	{
		$_SESSION["cart"] = serialize(array());
		return (encode_ret(FALSE, ""));
	}
	$cart = check_cart();
	foreach ($cart as $key => &$product)
	{
		if ($product["id"] === $pid)
		{
			if ($change == 0 || ($product["count"] + $change < 0))
				unset($cart[$key]);
			else if ($product["count"] + $change > ($max_stock = stock_id($mysql, $pid)))
					$product["count"] = $max_stock;
			else
					$product["count"] += $change;
			$change = -1;
			break ;
		}
	}
	if ($change > 0)
		$cart[] = array("id" => $pid, "count" => ($change > ($max_stock = stock_id($mysql, $pid))) ? $max_stock : $change);
	$cart = serialize($cart);
	$_SESSION['cart'] = $cart;
	if (($id = $_SESSION['id_login']) !== 0)
		mysqli_query($mysql, "UPDATE users SET cart = '$cart' WHERE id = $id");
	return (encode_ret(FALSE, ""));
}

function add_user($mysql, $login, $passwd)
{
	if ($login == NULL || $passwd == NULL)
		return (encode_ret(TRUE, "Login or password is empty"));
	$passwd = hash("whirlpool", $passwd);
	$user_exist = mysqli_query($mysql, "SELECT * FROM users WHERE login = '$login';");
	if ($user_exist->num_rows > 0)
		return (encode_ret(TRUE, "$login already exist"));
	if (mysqli_query($mysql, "INSERT INTO users (login, password) VALUES ('$login', '$passwd');") === TRUE)
		return (encode_ret(FALSE, "$login created"));
	else
		return (encode_ret(TRUE, "Error in creation of user : $login"));
}

function delete_user($mysql, $login, $passwd)
{
	if ($login == NULL || $passwd == NULL)
		return (encode_ret(TRUE, "Login or password is empty"));
	if (mysqli_query($mysql, "DELETE FROM users WHERE login = '$login' AND password = '$passwd';") === TRUE)
		return (encode_ret(FALSE, "User : $login deleted"));
	else
		return (encode_ret(TRUE, "Error in login/password"));
}

function get_pinfo($mysql, $pid)
{
	if ($pid <= 0 || (is_numeric($pid) === FALSE))
		return (encode_ret(TRUE, "Error in product id"));
	if (($pinfo = mysqli_query($mysql, "SELECT * FROM products WHERE p_id = $pid;")) === FALSE)
		return (encode_ret(TRUE, "Error in product id"));
	$info = mysqli_fetch_assoc($pinfo);
	mysqli_free_result($pinfo);
	return (encode_ret(FALSE, $info));
}

function get_uinfo($mysql, $id)
{
	if ($id <= 0 || (is_numeric($id) === FALSE))
		return (encode_ret(TRUE, "Error in id"));
	if (($info = mysqli_query($mysql, "SELECT * FROM users WHERE id = $id;")) === FALSE)
		return (encode_ret(TRUE, "Error in id"));
	$user = mysqli_fetch_assoc($info);
	unset($user["password"]);
	mysqli_free_result($info);
	return (encode_ret(FALSE, $user));
}

if (($method = $_GET["method"]) != NULL)
	switch ($method)
	{
		case "auth":
			$ret = auth($mysql, mysqli_real_escape_string($mysql, $_GET["login"]), hash("whirlpool", $_GET["passwd"])); // Rajouter hash du mot de passe
			break ;
		case "cart":
			$ret = cart($mysql);
			break ;
		case "edit_cart":
			$ret = edit_cart($mysql, $_GET["pid"], $_GET["change"]);
			break ;
		case "add_user":
			$ret = add_user($mysql, mysqli_real_escape_string($mysql, $_GET["login"]), $_GET["passwd"]);
			break ;
		case "del_user":
			$ret = delete_user($mysql, mysqli_real_escape_string($mysql, $_GET["login"]), hash("whirlpool", $_GET["passwd"]));
			break ;
		case "get_stock":
			$ret = get_stock($mysql, $_GET["pid"]);
			break ;
		case "get_pinfo":
			$ret = get_pinfo($mysql, $_GET["pid"]);
			break ;
		case "get_uinfo":
			$ret = get_uinfo($mysql, $_GET["id"]);
			break ;
		default :
			$ret = FALSE;
			break ;
	}
mysqli_close($mysql);
return (json_encode($ret));
?>
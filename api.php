<?PHP
session_start();
$mysql = mysqli_connect('e1r7p2.42.fr', 'root', 'qwerty123')
	or die('Impossible de se connecter : ' . mysqli_error($mysql));
mysqli_query($mysql, "USE db;")
	or die('Impossible de selectionner db : ' . mysqli_error($mysql));
mysqli_set_charset($mysql, "\\");

include("general_function.php");

function check_cart()
{
	if (isset($_SESSION['cart']) === FALSE)
	{
		echo "fuck\n"
;		$_SESSION['cart'] = serialize(array());
	}
	return (unserialize($_SESSION['cart']));
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
	if ($login === NULL || $passwd === NULL)
		return (encode_ret(TRUE, "Login or Password is empty"));
	if (($users = mysqli_query($mysql, "SELECT * FROM users WHERE login = '$login' AND password = '$passwd';")) === FALSE)
		return (encode_ret(TRUE, "Impossible de recuperer la tables des users : ".mysqli_error($mysql)));
	if ($users->num_rows === 1)
	{
		$usr = mysqli_fetch_assoc($users);
		$_SESSION['login'] = $login;
		$_SESSION['id_login'] = $usr["id"];
//		$_SESSION['cart'] = serialize(init_auth_cart($mysql, $usr["id"]));
		unset($usr["password"]);
		mysqli_free_result($users);
		return (encode_ret(FALSE, $usr));
	}
	return (encode_ret(TRUE, "Login or password is wrong"));
}

function cart($mysql)
{
	$cart = check_cart();
	$cart_with_info = array();
	foreach ($cart as $key => $product)
	{
		$pid = $product["id"];
		if (($new = mysqli_query($mysql, "SELECT * FROM products WHERE p_id = $pid;")) !== FALSE)
		{
			$new = mysqli_fetch_assoc($new);
			$new["count"] = $product["count"];
			$cart_with_info[$key] = $new;
		}
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
	//	var_dump($cart);
	foreach ($cart as $key => &$product)
	{
		if ($product["id"] === $pid)
		{
			if ($change == 0 || ($product["count"] + $change <= 0))
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
	return (encode_ret(FALSE, "cart change"));
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
	{
		if (($info = mysqli_query($mysql, "SELECT * FROM users WHERE login = $$login;")) === FALSE)
			return (encode_ret(TRUE, "Error in login"));
		$user = mysqli_fetch_assoc($info);
		unset($user["password"]);
		mysqli_free_result($info);
		return (encode_ret(FALSE, $user));
	}
	else
		return (encode_ret(TRUE, "Error in creation of user : $login"));
}

function delete_user($mysql, $login, $passwd, $id)
{
	if (is_numeric($id) && is_admin($mysql))
	{
		if (mysqli_query($mysql, "DELETE FROM users WHERE id = '$id';") !== FALSE)
			return (encode_ret(FALSE, "User with id : $id deleted"));
		else
			return (encode_ret(TRUE, "Can't delete login : $login"));
	}
	if ($login == NULL)
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

function modify_user($mysql, $id, $new_log, $old_pw, $new_pw)
{
	if ($_SESSION['id_login'] === 0)
		return (encode_ret(TRUE, "You must be logged"));
	$ok = 0;
	if ($id !== 0 && $old_pw == NULL && is_admin($_SESSION['id_login']) === TRUE)
		$ok = 1;
	else if ($old_pw != NULL)
	{
		$id = $_SESSION['id_login'];
		$old_pw = hash("whirlpool", $old_pw);
		$user = mysqli_query($mysql, "SELECT * FROM users WHERE id = $id AND password = '$old_pw';");
		if ($user->num_rows === 1)
			$ok = 1;
		else
			return (encode_ret(TRUE, "Wrong password"));
	}
	else
		return (encode_ret(TRUE, "Password is empty"));
	if ($ok === 1)
	{
		if ($new_log != NULL)
		{
			$new_log = mysqli_real_escape_string($new_log);
			$user = mysqli_query($mysql, "SELECT * FROM users WHERE login = '$new_log';");
			if ($user->num_rows !== 0)
				return (encode_ret(TRUE, "$new_log already exist"));
			else
				mysqli_query($mysql, "UPDATE users SET login = '$new_log' WHERE id = '$id';");
		}
		if ($old_pw != NULL)
		{
			$new_pw = hash("whirlpool", $new_pw);
			mysqli_query($mysql, "UPDATE users SET password = '$new_pw' WHERE id = '$id';");
		}
		return (encode_ret(FALSE, ""));
	}
	return (encode_ret(TRUE, "you can't modify user"));
}

function get_product($mysql, $type, $start, $len)
{
	if (!is_numeric($start) || !is_numeric($len) || $start < 0 || $len < 0)
		return (encode_ret(TRUE, "$start or $len is not an valide number"));
	if ($type == NULL)
	{
		if (($list_products_qr = mysqli_query($mysql, "SELECT * FROM products LIMIT $start, $len;")) === FALSE)
			return (encode_ret(TRUE, "Can't select products"));
	}
	else
	{
		if (($list_products_qr = mysqli_query($mysql, "SELECT t1.p_id, t1.name, t1.path, t1.price, t1.stock, t1.description, t1.variants, t3.type FROM products AS t1 INNER JOIN product_cat AS t2 ON t1.p_id = t2.p_id INNER JOIN products_types AS t3 ON t2.p_types = t3.p_types WHERE t2.p_types = $type;")) === FALSE)
			return (encode_ret(TRUE, "Can't select products with type : $type"));
	}
	$list_products = array();
	while (($products = mysqli_fetch_assoc($list_products_qr)))
		$list_products[] = $products;
	mysqli_free_result($list_products_qr);
	return(encode_ret(FALSE, $list_products));
}

function get_list_type($mysql)
{
	if (($list_type_qr = mysqli_query($mysql, "SELECT * FROM products_types")) === FALSE)
		return (encode_ret(TRUE, "Inpossible de recuperer la liste des categories"));
	$list_type = array();
	while (($type = mysqli_fetch_assoc($list_type_qr)))
		$list_type[] = $type;
	mysqli_free_result($list_type_qr);
	return ($list_type);
}

function get_list_user($mysql)
{
	if (($list_users_qr = mysqli_query($mysql, "SELECT id, login FROM users")) === FALSE)
		return (encode_ret(TRUE, "Inpossible de recuperer la liste des users"));
	$list_users = array();
	while (($user = mysqli_fetch_assoc($list_users_qr)))
		$list_users[] = $user;
	mysqli_free_result($list_users_qr);
	return ($list_users);
}

if (($method = $_GET["method"]) != NULL)
	switch ($method)
	{
	case "auth":
		$ret = auth($mysql, mysqli_real_escape_string($mysql, $_GET["login"]), hash("whirlpool", $_GET["passwd"]));
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
		$ret = delete_user($mysql, mysqli_real_escape_string($mysql, $_GET["login"]), hash("whirlpool", $_GET["passwd"]), $_GET["id"]);
		break ;
	case "mod_user":
		$ret = modify_user($mysql, $_GET["id"], $_GET["new_log"], $_GET["old_pw"], $_GET["new_pw"]);
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
	case "get_product":
		$ret = get_product($mysql, $_GET["type"], $_GET["start"], $_GET["len"]);
		break ;
	case "get_list_type":
		$ret = get_list_type($mysql);
		break ;
	case "get_list_user":
		$ret = get_list_user($mysql);
		break ;
	default :
		$ret = encode_ret(TRUE, "method: $method is unknown");
		break ;
	}
else
	$ret = encode_ret(TRUE, "use method=methode with API");
mysqli_close($mysql);
header('Content-Type: application/json');
echo json_encode($ret);
?>
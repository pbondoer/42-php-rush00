<?PHP
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
	$users = mysqli_query($mysql, "SELECT * FROM USERS", MYSQLI_STORE_RESULT)
	or die ('Impossible de recuperer la tables des users : ' . mysqli_error($mysql));
	$users = mysqli_fetch_all($users);
	foreach ($users as $usr)
	{
		if ($usr['1'] === $login && $usr['2'] === $passwd)
		{
			$_SESSION['login'] = $login;
			$_SESSION['id_login'] = $usr['0'];
			$_SESSION['cart'] = $cart;
			echo "Connected as $login\n";
			return ($login);
		}
	}
	echo "Login or passwd is wrong\n";
	return (FALSE);
}

if (($method = $_GET["method"]) != NULL)
	switch ($method)
	{
		case "auth":
			return (auth($mysql, $_GET["login"], $_GET["passwd"], $_GET["cart"]));
		default :
			return (FALSE);
	}
mysqli_close($mysql);
?>
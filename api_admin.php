<?PHP
session_start();
$mysql = mysqli_connect('e1r7p2.42.fr', 'root', 'qwerty123')
or die('Impossible de se connecter : ' . mysqli_error($mysql));
mysqli_query($mysql, "USE db;")
or die('Impossible de selectionner db : ' . mysqli_error($mysql));
mysqli_set_charset($mysql, "\\");

include("general_function.php");

function modify_cat($mysql, $p_type, $name)
{
	if (!is_numeric($p_type) || $p_type <= 0 || $name == NULL)
		return (encode_ret(TRUE, "$p_type or $name is invalid"));
	if (mysqli_query($mysql, "UPDATE products_types SET type = '$name' WHERE p_types = $p_type") === FALSE)
		return (encode_ret(TRUE, "$p_type is unknow"));
}

function add_cat($mysql, $name)
{
	if ($name == NULL)
		return (encode_ret(TRUE, "Name is empty"));
	if (mysqli_query($mysql, "INSERT INTO products_types (type) VALUES ('$name');") === FALSE)
		return (encode_ret(TRUE, "Can't create type : $name :".mysqli_error($mysql)));
	return (encode_ret(FALSE, "Type $name created"));
}

function del_cat($mysql, $p_type)
{
	if (!is_numeric($p_type) || $p_type <= 0)
		return (encode_ret(TRUE, "Invalid p_type : $p_type"));
	if (mysqli_query($mysql, "DELETE FROM products_types WHERE p_types = $p_type;") === FALSE)
		return (encode_ret(TRUE, "Can't delete type : $p_type :".mysqli_error($mysql)));
	if (mysqli_query($mysql, "DELETE FROM product_cat WHERE p_types = $p_type;") !== TRUE)
		return (encode_ret(TRUE, "Can't delete type in product_cat : $p_type :".mysqli_error($mysql)));
	return (encode_ret(FALSE, "Type $p_type deleted"));
}

function add_type_for_product($mysql, $p_id, $types)
{
	mysqli_query($mysql, "DELETE FROM product_cat WHERE p_id = $p_id;");
	$types = explode(",", $types);
	foreach ($types as $type)
		if (mysqli_query($mysql, "INSERT INTO product_cat (p_id, p_types) VALUES ($p_id, $type);") === FALSE)
			return (encode_ret(TRUE, "Can't add $type for $p_id : ".mysqli_error($mysql)));
}

function mod_product($mysql, $p_id, $name, $path, $price, $discount, $stock, $desc, $variants, $types)
{
	if (!is_numeric($p_id) || $p_id <= 0 || $name == NULL || !is_numeric($price) || !is_numeric($stock))
		return (encode_ret(TRUE, "Invalid data"));
	$name = mysqli_real_escape_string($mysql, $name);
	$path = mysqli_real_escape_string($mysql, $path);
	$desc = mysqli_real_escape_string($mysql, $desc);
	$variants = explode(",", $variants);
	$variants = serialize($variants);
	if (($qr = mysqli_query($mysql, "SELECT * FROM products WHERE p_id = $p_id")) === FALSE)
		return (encode_ret(TRUE, "Can't get id : $p_id : ".mysqli_error($mysql)));
	if ($qr->num_rows !== 1)
		return (encode_ret(TRUE, "Id : $p_id don't exist"));
	if ($types != NULL)
	add_type_for_product($mysql, $p_id, $types);
	if (mysqli_query($mysql, "UPDATE products SET name = '$name', path = '$path', price = $price, discount = $discount, stock = $stock, description = '$desc', variants = '$variants'  WHERE p_id = $p_id") !== TRUE)
		return (encode_ret(TRUE, "Can't update id : $p_id : ".mysqli_error($mysql)));
	return (encode_ret(FALSE, "Update id : $p_id succesfully"));

}

if (($method = $_GET["method"]) != NULL) //Add protection is_admin
	switch ($method)
	{
	case "modify_cat":
		$ret = modify_cat($mysql, $_GET["p_type"], mysqli_real_escape_string($mysql, $_GET["name"]));
		break ;
	case "add_cat":
		$ret = add_cat($mysql, mysqli_real_escape_string($mysql, $_GET["name"]));
		break ;
	case "del_cat":
		$ret = del_cat($mysql, $_GET["p_type"]);
		break ;
	case "mod_prod":
		$ret = mod_product($mysql, $_GET["p_id"], $_GET["name"], $_GET["path"], $_GET["price"], $_GET["discount"], $_GET["stock"], $_GET["desc"], $_GET["variants"], $_GET["types"]);
		break ;
	default :
		$ret = encode_ret(TRUE, "method: $method is unknown");
		break ;
	}

mysqli_close($mysql);
header('Content-Type: application/json');
echo json_encode($ret);
?>
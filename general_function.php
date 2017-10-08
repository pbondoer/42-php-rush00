<?PHP

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

function is_admin($mysql)
{
	if (($id = $_SESSION["id_login"]) === 0)
		return (FALSE);
	$user = mysqli_query($mysql, "SELECT * FROM users WHERE id = $id AND admin = 1;");
	if ($user->num_rows === 1)
		return (TRUE);
	else
		return (FALSE);
}

?>

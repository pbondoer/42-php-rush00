<?PHP
$mysql = mysqli_connect('localhost', 'root', 'qwerty123')
or die('Impossible de se connecter : ' . mysqli_error($mysql));
mysqli_query($mysql, "CREATE DATABASE IF NOT EXISTS db;")
or die('Can\'t create database db : ' . mysqli_error($mysql));
mysqli_query($mysql, "USE db;")
or die('Can\'t use database db : ' . mysqli_error($mysql));
mysqli_query($mysql, "CREATE TABLE IF NOT EXISTS users (
	id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT UNIQUE,
	login VARCHAR(50) NOT NULL UNIQUE,
	cart TEXT,
	admin INT DEFAULT 0,
	password VARCHAR(128) NOT NULL);")
or die('Can\'t create table users : ' . mysqli_error($mysql));
mysqli_query($mysql, "CREATE TABLE IF NOT EXISTS buy_history (
	id_user INT UNSIGNED NOT NULL,
	product_id INT UNSIGNED NOT NULL,
	cart_id INT UNSIGNED UNIQUE PRIMARY KEY AUTO_INCREMENT,
	count INT UNSIGNED NOT NULL);")
or die('Can\'t create table buy_history : ' . mysqli_error($mysql));
mysqli_query($mysql, "CREATE TABLE IF NOT EXISTS products_types (
	p_types INT NOT NULL UNSIGNED PRIMARY KEY AUTO_INCREMENT UNIQUE,
	type VARCHAR(50));")
or die('Can\'t create table product_types : ' . mysqli_error($mysql));
mysqli_query($mysql, "CREATE TABLE IF NOT EXISTS products (
	p_id INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT UNIQUE,
	name VARCHAR(50) NOT NULL,
	path TEXT,
	price DECIMAL UNSIGNED NOT NULL,
	discount DECIMAL,
	stock INT UNSIGNED,
	description TEXT,
	variants TEXT);")
or die('Can\'t create table products : ' . mysqli_error($mysql));
mysqli_query($mysql, "CREATE TABLE IF NOT EXISTS statistics (
	p_id INT UNSIGNED NOT NULL,
	id_users INT UNSIGNED,
	price DECIMAL UNSIGNED);")
or die('Can\'t create table statistics : ' . mysqli_error($mysql));
mysqli_query($mysql, "CREATE TABLE IF NOT EXISTS product_cat (
	p_id INT UNSIGNED NOT NULL,
	p_types INT UNSIGNED NOT NULL);")
or die('Can\'t create table statistics : ' . mysqli_error($mysql));
$admin_pass = hash("whirlpool", "admin");
mysqli_query($mysql, "INSERT INTO users (login, admin, password) VALUES ('admin', 1, '$admin_pass');")
or die('Can\'t create admin user : ' . mysqli_error($mysql));
mysqli_close($mysql);
?>
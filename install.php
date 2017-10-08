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
	p_types INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT UNIQUE,
	type VARCHAR(50));")
or die('Can\'t create table product_types : ' . mysqli_error($mysql));
mysqli_query($mysql, "CREATE TABLE IF NOT EXISTS products (
	p_id INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT UNIQUE,
	name VARCHAR(50) NOT NULL,
	path TEXT,
	price DECIMAL(10,2) UNSIGNED NOT NULL,
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
$name = "Cheveux";
mysqli_query($mysql, "INSERT INTO products_types (type) VALUES ('$name');")
or die ("Can't create type : $name :".mysqli_error($mysql));
$name = "Cheveux soyeux";
mysqli_query($mysql, "INSERT INTO products_types (type) VALUES ('$name');")
or die ("Can't create type : $name :".mysqli_error($mysql));
$name = "Cheveux court";
mysqli_query($mysql, "INSERT INTO products_types (type) VALUES ('$name');")
or die ("Can't create type : $name :".mysqli_error($mysql));
mysqli_query($mysql, "INSERT INTO products (name, price, stock, description) VALUES ('Cheveux en or', 10000, 1000, 'Cheveux en or pas normal');")
or die ("Can't create Cheveux en or".mysqli_error($mysql));
mysqli_query($mysql, "INSERT INTO products (name, price, stock, description, path) VALUES ('Cheveux boucles', 60.2, 5000, 'Cheveux boucles classique', 'https://i.pinimg.com/736x/6a/d4/0d/6ad40de6cd4b9879560e86a16eda2188--naturally-curly-haircuts-long-curly-haircuts.jpg');")
or die ("Can't create Cheveux boucles".mysqli_error($mysql));
mysqli_close($mysql);
?>
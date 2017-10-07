<?PHP
$mysql = mysqli_connect('e1r7p1.42.fr', 'root', 'qwerty123')
or die('Impossible de se connecter : ' . mysqli_error($mysql));
mysqli_query($mysql, "CREATE DATABASE IF NOT EXISTS db;")
or die('Can\'t create database db : ' . mysqli_error($mysql));
mysqli_query($mysql, "USE db;")
or die('Can\'t use database db : ' . mysqli_error($mysql));
mysqli_query($mysql, "CREATE TABLE IF NOT EXISTS users (
	id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT UNIQUE,
	login VARCHAR(50) NOT NULL UNIQUE,
	password VARCHAR(128) NOT NULL);")
or die('Can\'t create table users : ' . mysqli_error($mysql));
mysqli_query($mysql, "CREATE TABLE IF NOT EXISTS buy_history (
	id_user INT UNSIGNED NOT NULL,
	product_id INT UNSIGNED NOT NULL,
	count INT UNSIGNED NOT NULL);")
or die('Can\'t create table buy_history : ' . mysqli_error($mysql));
mysqli_query($mysql, "CREATE TABLE IF NOT EXISTS products_types (
	p_types INT UNSIGNED PRIMARY KEY AUTO_INCREMENT UNIQUE,
	type VARCHAR(50));")
or die('Can\'t create table product_types : ' . mysqli_error($mysql));
mysqli_query($mysql, "CREATE TABLE IF NOT EXISTS products (
	p_id INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT UNIQUE,
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
mysqli_close($mysql);
?>
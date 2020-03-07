<?php
    $pdo = null;

    try{
        $pdo = new PDO('mysql:host=localhost; dbname=family-tree; port=3306', 'root', '', array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
    }

    catch (Exception $e){
        print "Error";
    }
?>
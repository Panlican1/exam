<?php
     session_start();
    include "../../config/database.php";
    //omly for admin
    if(!isset($_SESSION["role"]) || $_SESSION["role"] != "admin"){
        header("location:../../index.php");
     exit;
    }
    $id = isset($_GET['id']) ? intval($_GET ['id']) : 0;

    //Delete sql
    mysqli_query($conn, "DELETE FROM users
    WHERE id=$id AND role='student' ");
    header("Location: index.php");
    exit;
?>
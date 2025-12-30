<?php
include "includes/db.php";

$id = $_POST['id'];
$status = $_POST['status'];

mysqli_query($conn,
    "UPDATE issues SET status='$status' WHERE id=$id"
);

echo $status;

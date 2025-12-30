<?php
include('includes/db.php'); // your DB connection

$title = $_POST['title'];
$description = $_POST['description'];
$province = $_POST['province'];
$district = $_POST['district'];
$municipality = $_POST['municipality'];

// Handle image upload
$image = '';
if(isset($_FILES['image']) && $_FILES['image']['name'] != ''){
    $image = 'uploads/' . basename($_FILES['image']['name']);
    move_uploaded_file($_FILES['image']['tmp_name'], $image);
}

// Insert into DB
$sql = "INSERT INTO issues (title, description, province, district, municipality, image, status)
        VALUES ('$title', '$description', '$province', '$district', '$municipality', '$image', 'Pending')";

if(mysqli_query($conn, $sql)){
    echo "<script>alert('Report submitted successfully'); window.location='index.php';</script>";
}else{
    echo "Error: " . mysqli_error($conn);
}
?>

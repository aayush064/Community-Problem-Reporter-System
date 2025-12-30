<?php
include "includes/db.php";
$data = mysqli_query($conn, "SELECT * FROM issues ORDER BY id DESC");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Panel</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="assets/js/main.js" defer></script>
</head>
<body>

<h2>Admin Dashboard</h2>

<table>
<tr>
    <th>Title</th>
    <th>Status</th>
    <th>Update</th>
</tr>

<?php while($row = mysqli_fetch_assoc($data)) { ?>
<tr>
    <td><?php echo $row['title']; ?></td>
    <td id="status-<?php echo $row['id']; ?>">
        <?php echo $row['status']; ?>
    </td>
    <td>
        <select onchange="updateStatus(<?php echo $row['id']; ?>, this.value)">
            <option>Pending</option>
            <option>In Progress</option>
            <option>Solved</option>
        </select>
    </td>
</tr>
<?php } ?>

</table>

</body>
</html>

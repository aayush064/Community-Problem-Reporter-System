<?php
include('includes/db.php');

$where = [];
$params = [];
$types = "";

$selected_category = $_GET['category'] ?? '';

// Add category filter if selected
if (!empty($selected_category)) {
    $where[] = "category = ?";
    $params[] = $selected_category;
    $types .= "s";
}

if (isset($_GET['province']) && $_GET['province'] !== '') {
    $where[] = "province = ?";
    $params[] = $_GET['province'];
    $types .= "s";

    if (!empty($_GET['district'])) {
        $where[] = "district = ?";
        $params[] = $_GET['district'];
        $types .= "s";
    }
    if (!empty($_GET['municipality'])) {
        $where[] = "municipality = ?";
        $params[] = $_GET['municipality'];
        $types .= "s";
    }
    if (!empty($_GET['ward'])) {
        $where[] = "ward = ?";
        $params[] = (int)$_GET['ward'];
        $types .= "i";
    }

    $sql = "SELECT * FROM issues";
    if (!empty($where)) {
        $sql .= " WHERE " . implode(" AND ", $where);
    }
    $sql .= " ORDER BY id DESC";

    $stmt = $conn->prepare($sql);
    if (!$stmt) die("SQL Error: " . $conn->error);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $issues = $stmt->get_result();
} else {
    $issues = null;
}

// Preserve current GET parameters for links
$current_params = $_GET;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Problems in Selected Area</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<header>
    <h1>Community Problem Reporter</h1>
    <div class="header-buttons">
        <a href="index.php" class="btn">Back to Filter</a>

        <!-- Category Filter Dropdown -->
        <div class="category-filter">
            <select id="category-select" onchange="filterByCategory(this.value)">
                <option value="">All Categories</option>
                <option value="Road" <?= $selected_category === 'Road' ? 'selected' : '' ?>>Road Issues</option>
                <option value="Water" <?= $selected_category === 'Water' ? 'selected' : '' ?>>Water Supply</option>
                <option value="Electricity" <?= $selected_category === 'Electricity' ? 'selected' : '' ?>>Electricity</option>
                <option value="Garbage" <?= $selected_category === 'Garbage' ? 'selected' : '' ?>>Garbage & Sanitation</option>
                <option value="Drainage" <?= $selected_category === 'Drainage' ? 'selected' : '' ?>>Drainage & Flooding</option>
                <option value="Street Light" <?= $selected_category === 'Street Light' ? 'selected' : '' ?>>Street Lighting</option>
                <option value="Public Safety" <?= $selected_category === 'Public Safety' ? 'selected' : '' ?>>Public Safety</option>
                <option value="Health" <?= $selected_category === 'Health' ? 'selected' : '' ?>>Health & Hygiene</option>
                <option value="Other" <?= $selected_category === 'Other' ? 'selected' : '' ?>>Other</option>
            </select>
        </div>
    </div>
</header>

<div class="container">
<?php
if (!$issues) {
    echo "<p style='text-align:center;margin-top:50px;font-size:18px;'>📍 Please select a province from the filter page to view reports.</p>";
} elseif ($issues->num_rows === 0) {
    echo "<p style='text-align:center;margin-top:50px;font-size:18px;'>❌ No problems found for the selected filters.</p>";
} else {
    while ($row = $issues->fetch_assoc()) {
        ?>
        <div class="card">
            <?php if (!empty($row['image'])): ?>
                <img src="<?= htmlspecialchars($row['image']) ?>" alt="Issue Image">
            <?php endif; ?>

            <h3><?= htmlspecialchars($row['title']) ?></h3>
            <p><?= nl2br(htmlspecialchars($row['description'])) ?></p>

            <small>
                <strong>Location:</strong>
                <?= htmlspecialchars($row['province']) ?>
                <?php if (!empty($row['district'])): ?>, <?= htmlspecialchars($row['district']) ?><?php endif; ?>
                <?php if (!empty($row['municipality'])): ?>, <?= htmlspecialchars($row['municipality']) ?><?php endif; ?>
                <?php if (!empty($row['ward'])): ?>, Ward <?= htmlspecialchars($row['ward']) ?><?php endif; ?>
            </small>

            <?php if (!empty($row['category'])): ?>
                <span class="category-tag"><?= htmlspecialchars($row['category']) ?></span>
            <?php endif; ?>

            <span class="status <?= strtolower(str_replace(' ', '-', $row['status'])) ?>">
                <?= htmlspecialchars($row['status']) ?>
            </span>
        </div>
        <?php
    }
}
?>
</div>

<script>
// Preserve all current filters and only change category
function filterByCategory(category) {
    const params = new URLSearchParams(window.location.search);
    if (category === "") {
        params.delete('category');
    } else {
        params.set('category', category);
    }
    window.location.search = params.toString();
}
</script>

</body>
</html>
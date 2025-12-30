<?php
include('includes/db.php');

$issues = null;
$where = [];
$params = [];
$types = "";

/* Build filters only if province is selected */
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

    $sql = "SELECT * FROM issues WHERE " . implode(" AND ", $where) . " ORDER BY id DESC";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        die("SQL Error: " . $conn->error);
    }

    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $issues = $stmt->get_result();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Community Problem Reporter</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<header>
    <h1>Community Problem Reporter</h1>
</header>

<!-- LOCATION FILTER -->
<div class="form">
    <h2>Filter Problems by Location</h2>

 <form method="GET" action="view_reports.php">

        <div class="form-group">
            <label>Province <span class="required">*</span></label>
            <select id="province" name="province" required>
                <option value="">Select Province</option>
            </select>
        </div>

        <div class="form-group">
            <label>District</label>
            <select id="district" name="district">
                <option value="">All Districts</option>
            </select>
        </div>

        <div class="form-group">
            <label>Municipality</label>
            <select id="municipality" name="municipality">
                <option value="">All Municipalities</option>
            </select>
        </div>

        <div class="form-group">
            <label>Ward</label>
            <select id="ward" name="ward">
                <option value="">All Wards</option>
            </select>
        </div>

        <button type="submit" class="submit-btn">View Problems</button>
    </form>
</div>

<!-- PROBLEM LIST -->
<div class="reports-container">

<?php if (!isset($_GET['province']) || $_GET['province'] === ''): ?>

    <p style="text-align:center;color:#666;">
        📍 Please select a province to view problems.
    </p>

<?php elseif ($issues && $issues->num_rows === 0): ?>

    <p style="text-align:center;color:#666;">
        ❌ No problems found for this location.
    </p>

<?php elseif ($issues): ?>

    <?php while ($row = $issues->fetch_assoc()): ?>
        <div class="report-card">
            <h3><?= htmlspecialchars($row['title']) ?></h3>
            <p><?= nl2br(htmlspecialchars($row['description'])) ?></p>

            <?php if (!empty($row['image'])): ?>
                <img src="<?= htmlspecialchars($row['image']) ?>" class="report-img">
            <?php endif; ?>

            <small>
                <?= htmlspecialchars($row['province']) ?>
                <?php if (!empty($row['district'])): ?>,
                    <?= htmlspecialchars($row['district']) ?>
                <?php endif; ?>
                <?php if (!empty($row['municipality'])): ?>,
                    <?= htmlspecialchars($row['municipality']) ?>
                <?php endif; ?>
                <?php if (!empty($row['ward'])): ?>,
                    Ward <?= htmlspecialchars($row['ward']) ?>
                <?php endif; ?>
            </small>

            <span class="status <?= strtolower($row['status']) ?>">
                <?= htmlspecialchars($row['status']) ?>
            </span>
        </div>
    <?php endwhile; ?>

<?php endif; ?>

</div>

<script>
const provinceSelect = document.getElementById('province');
const districtSelect = document.getElementById('district');
const municipalitySelect = document.getElementById('municipality');
const wardSelect = document.getElementById('ward');

fetch('locations.json')
    .then(res => res.json())
    .then(data => {
        window.locationData = data;

        data.forEach(p => {
            let opt = document.createElement('option');
            opt.value = p.province;
            opt.textContent = p.province;
            provinceSelect.appendChild(opt);
        });

        // Restore selection after submit
        provinceSelect.value = "<?= $_GET['province'] ?? '' ?>";
        provinceSelect.dispatchEvent(new Event('change'));
    });

provinceSelect.addEventListener('change', () => {
    districtSelect.innerHTML = '<option value="">All Districts</option>';
    municipalitySelect.innerHTML = '<option value="">All Municipalities</option>';
    wardSelect.innerHTML = '<option value="">All Wards</option>';

    const province = locationData.find(p => p.province === provinceSelect.value);
    if (!province) return;

    province.districts.forEach(d => {
        let opt = document.createElement('option');
        opt.value = d.name;
        opt.textContent = d.name;
        districtSelect.appendChild(opt);
    });

    districtSelect.value = "<?= $_GET['district'] ?? '' ?>";
    districtSelect.dispatchEvent(new Event('change'));
});

districtSelect.addEventListener('change', () => {
    municipalitySelect.innerHTML = '<option value="">All Municipalities</option>';
    wardSelect.innerHTML = '<option value="">All Wards</option>';

    const province = locationData.find(p => p.province === provinceSelect.value);
    if (!province) return;

    const district = province.districts.find(d => d.name === districtSelect.value);
    if (!district) return;

    district.municipalities.forEach(m => {
        let opt = document.createElement('option');
        opt.value = m.name;
        opt.textContent = m.name;
        municipalitySelect.appendChild(opt);
    });

    municipalitySelect.value = "<?= $_GET['municipality'] ?? '' ?>";
    municipalitySelect.dispatchEvent(new Event('change'));
});

municipalitySelect.addEventListener('change', () => {
    wardSelect.innerHTML = '<option value="">All Wards</option>';

    const province = locationData.find(p => p.province === provinceSelect.value);
    const district = province?.districts.find(d => d.name === districtSelect.value);
    const municipality = district?.municipalities.find(m => m.name === municipalitySelect.value);

    if (!municipality || !municipality.wards) return;

    for (let i = 1; i <= municipality.wards; i++) {
        let opt = document.createElement('option');
        opt.value = i;
        opt.textContent = `Ward ${i}`;
        wardSelect.appendChild(opt);
    }

    wardSelect.value = "<?= $_GET['ward'] ?? '' ?>";
});
</script>

</body>
</html>

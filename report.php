<?php
include('includes/db.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report Issue</title>

    <!-- ✅ CORRECT CSS PATH -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<header>
    <h1>Community Problem Reporter</h1>
</header>

<!-- ✅ CLASS FIXED: form (matches CSS) -->
<div class="form">
    <h2>Report an Issue</h2>

    <form action="submit_report.php" method="POST" enctype="multipart/form-data">

        <div class="form-group">
            <label for="province">Province <span class="required">*</span></label>
            <select id="province" name="province" required>
                <option value="">Select Province</option>
            </select>
        </div>

        <div class="form-group">
            <label for="district">District <span class="required">*</span></label>
            <select id="district" name="district" required>
                <option value="">Select District</option>
            </select>
        </div>

        <div class="form-group">
            <label for="municipality">Municipality / Rural Municipality <span class="required">*</span></label>
            <select id="municipality" name="municipality" required>
                <option value="">Select Municipality</option>
            </select>
        </div>

        <div class="form-group">
            <label for="ward">Ward Number <span class="required">*</span></label>
            <select id="ward" name="ward" required>
                <option value="">Select Ward</option>
            </select>
        </div>

        <div class="form-group">
            <label for="title">Issue Title <span class="required">*</span></label>
            <input
                type="text"
                name="title"
                id="title"
                required
                placeholder="e.g. Broken street light near school">
        </div>

        <div class="form-group">
            <label for="description">Description <span class="required">*</span></label>
            <textarea
                name="description"
                id="description"
                rows="8"
                required
                placeholder="Provide detailed description of the issue, location within the ward, and any other relevant information..."></textarea>
        </div>

        <div class="form-group">
            <label for="image">Upload Image (optional)</label>
            <input type="file" name="image" id="image" accept="image/*">
            <img id="preview" class="preview-img" alt="Image Preview">
        </div>

        <button type="submit" class="submit-btn">Submit Report</button>

    </form>
</div>
<script>
/* ===== LOCATION DROPDOWN LOGIC ===== */
const provinceSelect = document.getElementById('province');
const districtSelect = document.getElementById('district');
const municipalitySelect = document.getElementById('municipality');
const wardSelect = document.getElementById('ward');

fetch('locations.json')
    .then(res => res.json())
    .then(data => {
        window.locationData = data;

        data.forEach(p => {
            const option = document.createElement('option');
            option.value = p.province;
            option.textContent = p.province;
            provinceSelect.appendChild(option);
        });
    });

provinceSelect.addEventListener('change', () => {
    districtSelect.innerHTML = '<option value="">Select District</option>';
    municipalitySelect.innerHTML = '<option value="">Select Municipality</option>';
    wardSelect.innerHTML = '<option value="">Select Ward</option>';

    const province = window.locationData.find(p => p.province === provinceSelect.value);
    if (!province) return;

    province.districts.forEach(d => {
        const option = document.createElement('option');
        option.value = d.name;
        option.textContent = d.name;
        districtSelect.appendChild(option);
    });
});

districtSelect.addEventListener('change', () => {
    municipalitySelect.innerHTML = '<option value="">Select Municipality</option>';
    wardSelect.innerHTML = '<option value="">Select Ward</option>';

    const province = window.locationData.find(p => p.province === provinceSelect.value);
    const district = province?.districts.find(d => d.name === districtSelect.value);
    if (!district) return;

    district.municipalities.forEach(m => {
        const option = document.createElement('option');
        option.value = m.name;
        option.textContent = m.name;
        municipalitySelect.appendChild(option);
    });
});

municipalitySelect.addEventListener('change', () => {
    wardSelect.innerHTML = '<option value="">Select Ward</option>';

    const province = window.locationData.find(p => p.province === provinceSelect.value);
    const district = province?.districts.find(d => d.name === districtSelect.value);
    const municipality = district?.municipalities.find(m => m.name === municipalitySelect.value);

    if (!municipality || !municipality.wards) return;

    for (let i = 1; i <= municipality.wards; i++) {
        const option = document.createElement('option');
        option.value = i;
        option.textContent = `Ward ${i}`;
        wardSelect.appendChild(option);
    }
});

/* ===== IMAGE PREVIEW ===== */
document.getElementById('image').addEventListener('change', e => {
    const file = e.target.files[0];
    if (!file) return;

    const reader = new FileReader();
    reader.onload = () => {
        const preview = document.getElementById('preview');
        preview.src = reader.result;
        preview.classList.add('show');
    };
    reader.readAsDataURL(file);
});
</script>

</body>
</html>

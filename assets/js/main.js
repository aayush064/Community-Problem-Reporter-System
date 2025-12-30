function previewImage(event) {
    document.getElementById("preview").src =
        URL.createObjectURL(event.target.files[0]);
}

function updateStatus(id, status) {
    let xhr = new XMLHttpRequest();
    xhr.open("POST", "update_status.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

    xhr.onload = function () {
        document.getElementById("status-" + id).innerHTML = this.responseText;
    };

    xhr.send("id=" + id + "&status=" + status);
}

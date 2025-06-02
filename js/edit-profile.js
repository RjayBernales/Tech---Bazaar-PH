// edit-profile.js - extracted from edit-profile.php
function previewProfileImage(input) {
  if (input.files && input.files[0]) {
    const reader = new FileReader();
    reader.onload = function (e) {
      document.getElementById('profile-image').src = e.target.result;
    };
    reader.readAsDataURL(input.files[0]);
  }
}

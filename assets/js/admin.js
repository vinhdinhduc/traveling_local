document.addEventListener("DOMContentLoaded", function () {
  // === SIDEBAR TOGGLE (Mobile) ===
  const sidebarToggle = document.getElementById("sidebar-toggle");
  const sidebar = document.querySelector(".admin-sidebar");

  if (sidebarToggle && sidebar) {
    sidebarToggle.addEventListener("click", function () {
      sidebar.classList.toggle("active");
    });

    // Đóng sidebar khi click ngoài (mobile)
    document.addEventListener("click", function (e) {
      if (
        window.innerWidth <= 768 &&
        sidebar.classList.contains("active") &&
        !sidebar.contains(e.target) &&
        !sidebarToggle.contains(e.target)
      ) {
        sidebar.classList.remove("active");
      }
    });
  }

  // === CONFIRM DELETE ===
  document.querySelectorAll("[data-confirm]").forEach(function (el) {
    el.addEventListener("click", function (e) {
      const msg =
        this.getAttribute("data-confirm") || "Bạn có chắc chắn muốn xóa?";
      if (!confirm(msg)) {
        e.preventDefault();
      }
    });
  });

  // === FILE INPUT PREVIEW ===
  document.querySelectorAll('input[type="file"]').forEach(function (input) {
    input.addEventListener("change", function () {
      const preview = document.getElementById(this.id + "-preview");
      if (preview && this.files && this.files[0]) {
        const reader = new FileReader();
        reader.onload = function (e) {
          preview.src = e.target.result;
          preview.style.display = "block";
        };
        reader.readAsDataURL(this.files[0]);
      }
    });
  });
});

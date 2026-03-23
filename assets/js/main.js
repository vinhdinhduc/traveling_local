document.addEventListener("DOMContentLoaded", function () {
  // === LOADING ANIMATION ===
  const loader = document.querySelector(".page-loader");
  if (loader) {
    window.addEventListener("load", function () {
      setTimeout(() => loader.classList.add("hidden"), 500);
    });
    // Fallback: ẩn loader sau 3 giây
    setTimeout(() => loader.classList.add("hidden"), 3000);
  }

  // === HEADER SCROLL EFFECT ===
  const header = document.querySelector(".header");
  if (header) {
    window.addEventListener("scroll", function () {
      if (window.scrollY > 50) {
        header.classList.add("scrolled");
      } else {
        header.classList.remove("scrolled");
      }
    });
  }

  // === HAMBURGER MENU (Mobile) ===
  const hamburger = document.querySelector(".hamburger");
  const navMenu = document.querySelector(".nav-menu");
  const overlay = document.querySelector(".mobile-overlay");

  if (hamburger && navMenu) {
    hamburger.addEventListener("click", function () {
      hamburger.classList.toggle("active");
      navMenu.classList.toggle("active");
      if (overlay) overlay.classList.toggle("active");
      document.body.style.overflow = navMenu.classList.contains("active")
        ? "hidden"
        : "";
    });

    if (overlay) {
      overlay.addEventListener("click", function () {
        hamburger.classList.remove("active");
        navMenu.classList.remove("active");
        overlay.classList.remove("active");
        document.body.style.overflow = "";
      });
    }

    // Đóng menu khi click link
    navMenu.querySelectorAll("a").forEach(function (link) {
      link.addEventListener("click", function () {
        hamburger.classList.remove("active");
        navMenu.classList.remove("active");
        if (overlay) overlay.classList.remove("active");
        document.body.style.overflow = "";
      });
    });
  }

  // === HERO SLIDER (Swiper) ===
  if (document.querySelector(".hero-slider")) {
    new Swiper(".hero-slider", {
      effect: "fade",
      fadeEffect: { crossFade: true },
      autoplay: { delay: 4000, disableOnInteraction: false },
      loop: true,
      pagination: {
        el: ".hero-slider .swiper-pagination",
        clickable: true,
      },
      navigation: {
        nextEl: ".hero-slider .swiper-button-next",
        prevEl: ".hero-slider .swiper-button-prev",
      },
      lazy: true,
    });
  }

  // === GALLERY SLIDER (Trang chủ) ===
  if (document.querySelector(".gallery-swiper")) {
    new Swiper(".gallery-swiper", {
      slidesPerView: 1,
      spaceBetween: 20,
      autoplay: { delay: 3000, disableOnInteraction: false },
      loop: true,
      navigation: {
        nextEl: ".gallery-swiper .swiper-button-next",
        prevEl: ".gallery-swiper .swiper-button-prev",
      },
      breakpoints: {
        768: { slidesPerView: 2 },
        1024: { slidesPerView: 3 },
      },
    });
  }

  // === DETAIL GALLERY SLIDER ===
  if (document.querySelector(".detail-gallery-swiper")) {
    new Swiper(".detail-gallery-swiper", {
      slidesPerView: 1,
      spaceBetween: 15,
      navigation: {
        nextEl: ".detail-gallery-swiper .swiper-button-next",
        prevEl: ".detail-gallery-swiper .swiper-button-prev",
      },
      pagination: {
        el: ".detail-gallery-swiper .swiper-pagination",
        clickable: true,
      },
      breakpoints: {
        480: { slidesPerView: 2 },
        768: { slidesPerView: 3 },
        1024: { slidesPerView: 4 },
      },
    });
  }

  // === LIGHTBOX ===
  const lightbox = document.getElementById("lightbox");
  const lightboxImg = document.getElementById("lightbox-img");

  // Mở lightbox khi click ảnh gallery
  document.querySelectorAll("[data-lightbox]").forEach(function (el) {
    el.addEventListener("click", function () {
      const src = this.getAttribute("data-lightbox") || this.src;
      if (lightbox && lightboxImg) {
        lightboxImg.src = src;
        lightbox.classList.add("active");
        document.body.style.overflow = "hidden";
      }
    });
  });

  // Đóng lightbox
  if (lightbox) {
    lightbox.addEventListener("click", function (e) {
      if (
        e.target === lightbox ||
        e.target.classList.contains("lightbox-close")
      ) {
        lightbox.classList.remove("active");
        document.body.style.overflow = "";
      }
    });

    // Đóng bằng phím ESC
    document.addEventListener("keydown", function (e) {
      if (e.key === "Escape" && lightbox.classList.contains("active")) {
        lightbox.classList.remove("active");
        document.body.style.overflow = "";
      }
    });
  }

  // === BACK TO TOP BUTTON ===
  const backToTop = document.querySelector(".back-to-top");
  if (backToTop) {
    window.addEventListener("scroll", function () {
      if (window.scrollY > 300) {
        backToTop.classList.add("show");
      } else {
        backToTop.classList.remove("show");
      }
    });

    backToTop.addEventListener("click", function () {
      window.scrollTo({ top: 0, behavior: "smooth" });
    });
  }

  // === FADE IN ON SCROLL ===
  const fadeElements = document.querySelectorAll(".fade-in");
  if (fadeElements.length > 0) {
    const observer = new IntersectionObserver(
      function (entries) {
        entries.forEach(function (entry) {
          if (entry.isIntersecting) {
            entry.target.classList.add("visible");
            observer.unobserve(entry.target);
          }
        });
      },
      { threshold: 0.1 },
    );

    fadeElements.forEach(function (el) {
      observer.observe(el);
    });
  }

  // === FORM VALIDATION (Liên hệ) ===
  const contactForm = document.getElementById("contact-form");
  if (contactForm) {
    contactForm.addEventListener("submit", function (e) {
      let isValid = true;
      const name = contactForm.querySelector('[name="name"]');
      const email = contactForm.querySelector('[name="email"]');
      const message = contactForm.querySelector('[name="message"]');

      // Reset lỗi
      contactForm.querySelectorAll(".form-error").forEach((el) => el.remove());

      // Validate tên
      if (name && name.value.trim().length < 2) {
        showFieldError(name, "Họ tên phải có ít nhất 2 ký tự");
        isValid = false;
      }

      // Validate email
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      if (email && !emailRegex.test(email.value.trim())) {
        showFieldError(email, "Vui lòng nhập email hợp lệ");
        isValid = false;
      }

      // Validate nội dung
      if (message && message.value.trim().length < 10) {
        showFieldError(message, "Nội dung phải có ít nhất 10 ký tự");
        isValid = false;
      }

      if (!isValid) {
        e.preventDefault();
      }
    });
  }

  /**
   * Hiển thị lỗi validation cho field
   */
  function showFieldError(field, message) {
    const error = document.createElement("div");
    error.className = "form-error";
    error.style.color = "#e74c3c";
    error.style.fontSize = "0.85rem";
    error.style.marginTop = "5px";
    error.textContent = message;
    field.parentNode.appendChild(error);
    field.style.borderColor = "#e74c3c";

    field.addEventListener(
      "input",
      function () {
        field.style.borderColor = "";
        const err = field.parentNode.querySelector(".form-error");
        if (err) err.remove();
      },
      { once: true },
    );
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
});

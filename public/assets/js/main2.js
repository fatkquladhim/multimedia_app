var swiper = new Swiper(".slide-content", {
  slidesPerView: 3,
  spaceBetween: 25,
  loop: true,
  centerSlide: true,
  fade: true,
  grabCursor: true,
  pagination: {
    el: ".swiper-pagination",
    clickable: true,
    dynamicBullets: true,
  },
  navigation: {
    nextEl: ".swiper-button-next",
    prevEl: ".swiper-button-prev",
  },

  breakpoints: {
    0: { slidesPerView: 1 },
    520: { slidesPerView: 2 },
    950: { slidesPerView: 3 },
  },
});

document.addEventListener("DOMContentLoaded", function () {
  let today = new Date();
  let formattedDate = today.toLocaleDateString("id-ID", {
    day: "2-digit",
    month: "long",
    year: "numeric",
  });

  document.querySelectorAll(".dateInput").forEach((input) => {
    input.value = formattedDate;
  });
});
document.addEventListener("DOMContentLoaded", function () {
  const onewayRadio = document.getElementById("oneway");
  const returnRadio = document.getElementById("return");
  const returnDateField = document.getElementById("return-date-field");

  function toggleReturnDate() {
    if (returnRadio.checked) {
      returnDateField.style.display = "block";
    } else {
      returnDateField.style.display = "none";
    }
  }

  onewayRadio.addEventListener("change", toggleReturnDate);
  returnRadio.addEventListener("change", toggleReturnDate);

  toggleReturnDate(); // Run on load
});

// Navbar scroll effect
window.addEventListener('scroll', function() {
  const navbar = document.querySelector('.navbar');
  if (window.scrollY > 50) {
    navbar.classList.add('scrolled');
  } else {
    navbar.classList.remove('scrolled');
  }
});


(function () {
  var toggles = document.querySelectorAll(".gs-dropdown-toggle");

  toggles.forEach(function (toggle) {
    toggle.addEventListener("click", function () {
      var expanded = toggle.getAttribute("aria-expanded") === "true";
      toggle.setAttribute("aria-expanded", String(!expanded));
    });
  });
})();

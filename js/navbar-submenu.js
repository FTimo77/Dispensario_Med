document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('.dropdown-submenu .dropdown-toggle').forEach(function(element) {
    element.addEventListener('click', function (e) {
      e.preventDefault();
      e.stopPropagation();
      let submenu = this.nextElementSibling;
      if (submenu) {
        submenu.classList.toggle('show');
      }
      document.querySelectorAll('.dropdown-submenu .dropdown-menu').forEach(function(menu) {
        if (menu !== submenu) menu.classList.remove('show');
      });
    });
  });

  document.querySelectorAll('.dropdown').forEach(function(drop) {
    drop.addEventListener('hide.bs.dropdown', function () {
      this.querySelectorAll('.dropdown-menu.show').forEach(function(menu) {
        menu.classList.remove('show');
      });
    });
  });
});
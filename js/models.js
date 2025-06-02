function finalizarFormulario() {
  let valido = true;

  // Validar nombre y presentación
  const nombre = document.getElementById("productname");
  const presentacion = document.getElementById("presentacionproducto");
  if (!nombre.value.trim()) {
    nombre.classList.add("is-invalid");
    valido = false;
  } else {
    nombre.classList.remove("is-invalid");
  }
  if (!presentacion.value.trim()) {
    presentacion.classList.add("is-invalid");
    valido = false;
  } else {
    presentacion.classList.remove("is-invalid");
  }

  // Validar fechas
  const fechaElab = document.getElementById("fechaElaboracion");
  const fechaCad = document.getElementById("fechaCaducidad");
  if (!fechaElab.value) {
    fechaElab.classList.add("is-invalid");
    valido = false;
  } else {
    fechaElab.classList.remove("is-invalid");
  }
  if (!fechaCad.value || fechaCad.value <= fechaElab.value) {
    fechaCad.classList.add("is-invalid");
    valido = false;
  } else {
    fechaCad.classList.remove("is-invalid");
  }

  // Validar producto seleccionado (si aplica)
  const producto = document.getElementById("productoSeleccionado");
  if (producto && !producto.value) {
    producto.classList.add("is-invalid");
    valido = false;
  } else if (producto) {
    producto.classList.remove("is-invalid");
  }

  // Validar que haya al menos una categoría
  const lista = document.getElementById("lista-items");
  const categoriaSeleccionada = document.getElementById("categoriaSeleccionada").value;

  if (lista.children.length === 0) {
    document.getElementById("nuevoElemento").classList.add("is-invalid");
    valido = false;
  } else {
    document.getElementById("nuevoElemento").classList.remove("is-invalid");
  }

  // Validar que una categoría esté seleccionada
  if (!categoriaSeleccionada) {
    lista.classList.add("is-invalid");
    valido = false;
  } else {
    lista.classList.remove("is-invalid");
  }

  if (!valido) return;

  alert("Formulario completado correctamente.");
  document.getElementById("formularioProducto").reset();
  lista.innerHTML = "";

  // Limpiar clases de error
  nombre.classList.remove("is-invalid");
  presentacion.classList.remove("is-invalid");
  fechaElab.classList.remove("is-invalid");
  fechaCad.classList.remove("is-invalid");
  if (producto) producto.classList.remove("is-invalid");
}

// Función para agregar una categoría a la lista
function agregarElemento() {
  const lista = document.getElementById("lista-items");
  const nuevoElemento = document.getElementById("nuevoElemento");
  const texto = nuevoElemento.value.trim();
  if (texto === "") return;

  // Crear el elemento de lista
  const li = document.createElement("li");
  li.className = "list-group-item list-group-item-action";
  li.textContent = texto;

  // Hacerlo seleccionable
  li.onclick = function () {
    document.querySelectorAll("#lista-items .list-group-item").forEach((item) => {
      item.classList.remove("active");
    });
    li.classList.add("active");
    // Guardar el valor seleccionado en el campo oculto
    document.getElementById("categoriaSeleccionada").value = li.textContent;
  };

  lista.appendChild(li);
  nuevoElemento.value = "";
}

// Dropdown submenu functionality para Bootstrap 5
function activarSubmenus() {
  document.querySelectorAll(".dropdown-submenu .dropdown-toggle").forEach(function (element) {
    element.addEventListener("click", function (e) {
      e.preventDefault();
      e.stopPropagation();
      let submenu = this.nextElementSibling;
      if (submenu) {
        submenu.classList.toggle("show");
      }
    });
  });
  document.addEventListener("click", function () {
    document.querySelectorAll(".dropdown-submenu .dropdown-menu").forEach(function (submenu) {
      submenu.classList.remove("show");
    });
  });
}

// Cargar el navbar y luego activar submenús
fetch("./includes/navbar.html")
  .then((res) => res.text())
  .then((data) => {
    document.getElementById("navbar").innerHTML = data;
    activarSubmenus();
  });

// Validación del formulario de agregar usuario
const formAgregarUsuario = document.getElementById('fromAgregarUsuario');
if (formAgregarUsuario) {
  formAgregarUsuario.addEventListener('submit', function(event) {
    event.preventDefault();
    const contrasena = document.getElementById('contrasena');
    const confirmarContrasena = document.getElementById('confirmarContrasena');
    if (contrasena.value !== confirmarContrasena.value) {
      alert('Las contraseñas no coinciden. Por favor, inténtelo de nuevo.');
      return;
    }
    // Aquí puedes agregar el envío del formulario si las contraseñas coinciden
    // this.submit();
  });
}

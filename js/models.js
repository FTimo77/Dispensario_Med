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

  // Validar categoría: debe estar seleccionada o escrita una nueva
  const categoriaSelect = document.getElementById("categoriaSeleccionada");
  const nuevaCategoria = document.getElementById("nueva_categoria");
  const categoriaSeleccionada = categoriaSelect.value.trim();
  const nuevaCategoriaValor = nuevaCategoria.value.trim();

  if (!categoriaSeleccionada && !nuevaCategoriaValor) {
    categoriaSelect.classList.add("is-invalid");
    nuevaCategoria.classList.add("is-invalid");
    valido = false;
  } else {
    categoriaSelect.classList.remove("is-invalid");
    nuevaCategoria.classList.remove("is-invalid");
  }

  // Si no es válido, evita el envío
  return valido;
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

// Función para agregar un producto al select en egreso
function agregarProductoSelect() {
  const productosDiv = document.getElementById('productosSeleccionados');
  const nuevoGrupo = document.createElement('div');
  nuevoGrupo.className = 'mb-3 producto-select-group';
  nuevoGrupo.innerHTML = `
    <label class="form-label">Selecciona un producto:</label>
    <select class="form-select" name="productoSeleccionado[]">
      <option value="">-- Selecciona --</option>
      <option value="Paracetamol">Paracetamol</option>
      <option value="Ibuprofeno">Ibuprofeno</option>
      <option value="Amoxicilina">Amoxicilina</option>
      <option value="Omeprazol">Omeprazol</option>
    </select>
    <div class="invalid-feedback">Debes seleccionar un producto.</div>
  `;
  productosDiv.appendChild(nuevoGrupo);
}

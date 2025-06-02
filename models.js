function finalizarFormulario() {
  // Validación de todos los campos
  let valido = true;

  // Paso 1
  const nombre = document.getElementById('productname');
  const presentacion = document.getElementById('presentacionproducto');
  if (!nombre.value.trim()) {
    nombre.classList.add('is-invalid');
    valido = false;
  } else {
    nombre.classList.remove('is-invalid');
  }
  if (!presentacion.value.trim()) {
    presentacion.classList.add('is-invalid');
    valido = false;
  } else {
    presentacion.classList.remove('is-invalid');
  }

  // Paso 2
  const fechaElab = document.getElementById('fechaElaboracion');
  const fechaCad = document.getElementById('fechaCaducidad');
  if (!fechaElab.value) {
    fechaElab.classList.add('is-invalid');
    valido = false;
  } else {
    fechaElab.classList.remove('is-invalid');
  }
  if (!fechaCad.value || fechaCad.value <= fechaElab.value) {
    fechaCad.classList.add('is-invalid');
    valido = false;
  } else {
    fechaCad.classList.remove('is-invalid');
  }

  // Paso 3
  const producto = document.getElementById('productoSeleccionado');
  if (!producto.value) {
    producto.classList.add('is-invalid');
    valido = false;
  } else {
    producto.classList.remove('is-invalid');
  }

  if (!valido) return;

  alert('Formulario completado correctamente.');
  document.getElementById('formularioProducto').reset();
  // Quitar clases de error si las hubiera
  nombre.classList.remove('is-invalid');
  presentacion.classList.remove('is-invalid');
  fechaElab.classList.remove('is-invalid');
  fechaCad.classList.remove('is-invalid');
  producto.classList.remove('is-invalid');
}

function agregarElemento() {
  const input = document.getElementById('nuevoElemento');
  const lista = document.getElementById('lista-items');
  const valor = input.value.trim();

  if (!valor) {
    input.classList.add('is-invalid');
    return;
  }
  input.classList.remove('is-invalid');

  // Evitar duplicados
  const items = lista.getElementsByTagName('li');
  for (let i = 0; i < items.length; i++) {
    if (items[i].firstChild.textContent === valor) {
      input.value = '';
      return;
    }
  }

  const li = document.createElement('li');
  li.className = 'list-group-item d-flex justify-content-between align-items-center';
  li.textContent = valor;
  const btn = document.createElement('button');
  btn.className = 'btn btn-sm btn-danger ms-2';
  btn.textContent = 'Eliminar';
  btn.onclick = function() { li.remove(); };
  li.appendChild(btn);
  lista.appendChild(li);
  input.value = '';
}

function finalizarFormulario() {
  let valido = true;

  // Validar nombre y presentación
  const nombre = document.getElementById('productname');
  const presentacion = document.getElementById('presentacionproducto');
  if (!nombre.value.trim()) {
    nombre.classList.add('is-invalid');
    valido = false;
  } else {
    nombre.classList.remove('is-invalid');
  }
  if (!presentacion.value.trim()) {
    presentacion.classList.add('is-invalid');
    valido = false;
  } else {
    presentacion.classList.remove('is-invalid');
  }

  // Validar que haya al menos una categoría
  const lista = document.getElementById('lista-items');
  if (lista.children.length === 0) {
    document.getElementById('nuevoElemento').classList.add('is-invalid');
    valido = false;
  } else {
    document.getElementById('nuevoElemento').classList.remove('is-invalid');
  }

  if (!valido) return;

  alert('Formulario completado correctamente.');
  document.getElementById('formularioProducto').reset();
  lista.innerHTML = '';
}

// Dropdown submenu functionality
document
        .querySelectorAll(".dropdown-submenu .dropdown-toggle")
        .forEach(function (element) {
          element.addEventListener("click", function (e) {
            e.preventDefault();
            e.stopPropagation();
            let submenu = this.nextElementSibling;
            if (submenu) {
              submenu.classList.toggle("show");
            }
          });
        });
      document.addEventListener("click", function (e) {
        document
          .querySelectorAll(".dropdown-submenu .dropdown-menu")
          .forEach(function (submenu) {
            submenu.classList.remove("show");
          });
      });


// Soporte para submenús en Bootstrap 5
  // Cargar el navbar y luego activar submenús
      fetch('navbar.html')
        .then(res => res.text())
        .then(data => {
          document.getElementById('navbar').innerHTML = data;

          // Activar submenús después de insertar el navbar
          document.querySelectorAll('.dropdown-submenu .dropdown-toggle').forEach(function(element){
            element.addEventListener('click', function (e) {
              e.preventDefault();
              e.stopPropagation();
              let submenu = this.nextElementSibling;
              if(submenu){
                submenu.classList.toggle('show');
              }
            });
          });
          // Cerrar submenús al hacer clic fuera
          document.addEventListener('click', function (e) {
            document.querySelectorAll('.dropdown-submenu .dropdown-menu').forEach(function(submenu){
              submenu.classList.remove('show');
            });
          });
        });
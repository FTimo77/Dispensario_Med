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
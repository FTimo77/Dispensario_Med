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
let egresos = [];

function renderEgresos() {
  const tbody = document.getElementById("tablaEgresos");
  tbody.innerHTML = "";
  egresos.forEach((e, i) => {
    tbody.innerHTML += `
        <tr>
          <td>
            <input type="hidden" name="productoEgreso[]" value="${e.productoId}">
            <input type="hidden" name="cantidadEgreso[]" value="${e.cantidad}">
            <input type="hidden" name="loteEgreso[]" value="${e.loteId}">
            ${i + 1}
          </td>
          <td>${e.productoNombre}</td>
          <td>${e.cantidad}</td>
          <td>${e.loteNombre}</td>
          <td>
            <button class="btn btn-sm btn-outline-danger" title="Eliminar" type="button" onclick="eliminarEgreso(${i})">
              <i class="bi bi-trash"></i>
            </button>
          </td>
        </tr>
      `;
  });
}

function agregarEgreso(event) {
  event.preventDefault();
  const productoSelect = document.getElementById("productoEgreso");
  const cantidadInput = document.getElementById("cantidadEgreso");

  const productoId = productoSelect.value;
  const productoNombre = productoSelect.options[productoSelect.selectedIndex].text.split(' (Stock:')[0];
  const stockDisponible = parseInt(productoSelect.options[productoSelect.selectedIndex].getAttribute('data-stock'), 10);
  const cantidad = parseInt(cantidadInput.value, 10);

  if (!productoId || !cantidad) {
    alert("Por favor, seleccione un producto y especifique la cantidad.");
    return;
  }
  if (cantidad > stockDisponible) {
    alert(`Stock insuficiente. Solo hay ${stockDisponible} unidades disponibles.`);
    return;
  }

  //lote info
  const selectLote = document.getElementById("loteEgreso");

  // Obtener valores
  if (!selectLote || !selectLote.value) {
    alert("Debe seleccionar un lote válido.");
    return;
  }

  const loteId = selectLote.value; // Este es el NUM_LOTE
  const loteNombre = selectLote.options[selectLote.selectedIndex].text;

  egresos.push({ productoId, productoNombre, cantidad, loteId, loteNombre });
  renderEgresos();
  document.getElementById("formAgregarEgreso").reset();
  var modal = bootstrap.Modal.getInstance(document.getElementById("modalAgregarEgreso"));
  modal.hide();
}

function eliminarEgreso(idx) {
  if (confirm("¿Seguro que desea eliminar este egreso?")) {
    egresos.splice(idx, 1);
    renderEgresos();
  }
}

document.getElementById("formEgresos").addEventListener("submit", function (e) {
  if (egresos.length === 0) {
    alert("Agregue al menos un egreso antes de enviar.");
    e.preventDefault();
  }
  if (document.getElementById('motivo').value.trim() === '') {
    alert('El motivo del egreso es obligatorio.');
    e.preventDefault();
  }
  if (document.getElementById('paciente').value.trim() === '') {
    alert('El nombre del paciente es obligatorio.');
    e.preventDefault();
  }
  if (document.getElementById('motivo').value.trim() === '') {
    alert('El motivo del egreso es obligatorio.');
    e.preventDefault();
  }
});

renderEgresos();

//obtiene lote y lo carga dinamicamente segun el producto seleccionado
function cargarLote() {
  document.getElementById("productoEgreso").addEventListener("change", async function () {
    const productoId = this.value;
    const selectLote = document.getElementById("loteEgreso");

    // Resetear el select
    selectLote.innerHTML = '<option value="" disabled selected>Cargando lotes...</option>';
    selectLote.disabled = true;

    if (productoId) {
      try {
        const response = await fetch(`../includes/lote_model.php?id_producto=${productoId}`);
        const lotes = await response.json();

        selectLote.innerHTML = ''; // Limpiar opciones

        if (lotes.length > 0) {
          // Agregar opción por defecto
          const defaultOption = document.createElement("option");
          defaultOption.value = "";
          defaultOption.disabled = true;
          defaultOption.selected = true;
          defaultOption.textContent = "Seleccione un lote";
          selectLote.appendChild(defaultOption);

          // Agregar lotes
          lotes.forEach(lote => {
            const option = document.createElement("option");
            option.value = lote.NUM_LOTE; // Usar NUM_LOTE como valor
            option.textContent = `${lote.NUM_LOTE} stock (${lote.CANTIDAD_LOTE})`;
            selectLote.appendChild(option);
          });
          selectLote.disabled = false;
        } else {
          selectLote.innerHTML = '<option value="" disabled>No hay lotes disponibles</option>';
        }
      } catch (error) {
        console.error("Error:", error);
        selectLote.innerHTML = '<option value="" disabled>Error al cargar lotes</option>';
      }
    } else {
      selectLote.innerHTML = '<option value="" disabled selected>Primero seleccione un producto</option>';
    }
  });
}

// Inicializar
cargarLote();

function mostrarCamposNuevoPaciente(sel) {
  const campos = document.getElementById('campos-nuevo-paciente');
  if (sel.value === 'nuevo') {
    campos.style.display = 'block';
    document.getElementById('nuevo_paciente_nombre').required = true;
    document.getElementById('nuevo_paciente_apellido').required = true;
  } else {
    campos.style.display = 'none';
    document.getElementById('nuevo_paciente_nombre').required = false;
    document.getElementById('nuevo_paciente_apellido').required = false;
  }
}

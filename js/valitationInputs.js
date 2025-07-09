function soloLetras(input) {
  input.value = input.value.replace(/[^A-Za-z]/g, '');
}
function sinEspacios(input) {
input.value = input.value.replace(/\s/g, '');
}
function letrasYEspacios(input) {
  input.value = input.value.replace(/[^A-Za-z\s]/g, '');
}
function mayusculas(input) {
  input.value = input.value.toUpperCase();
}
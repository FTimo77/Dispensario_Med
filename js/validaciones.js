// filepath: c:\xampp\htdocs\Respaldo\js\validaciones.js
function validarPassword(pass, pass2) {
    const regex = /^(?=.*[0-9])(?=.*[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]).{6,}$/;
    if (!regex.test(pass)) {
        return "La contraseña debe tener al menos 6 caracteres, un número y un caracter especial.";
    }
    if (pass !== pass2) {
        return "Las contraseñas no coinciden.";
    }
    return "";
}
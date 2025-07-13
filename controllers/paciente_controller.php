<?php
session_start();
require_once __DIR__ . '/../models/paciente_model.php';
if (!isset($_SESSION['usuario']) && !isset($_SESSION['bodega'])) {
    session_destroy();
    header("../index.php");
    exit;
}
$model = new PacienteModel();
// Eliminar paciente
if (isset($_GET['id_usuario'])) {
    $id_usuario = $_GET['id_usuario'];
    $model->eliminar($id_usuario);
    header("Location: paciente.php");
    exit();
}
// Agregar o editar paciente
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre_p = $_POST['nombrep'];
    $apellido_p = $_POST['apellidop'];
    $empresa = isset($_POST['nuevaEmpresa']) && trim($_POST['nuevaEmpresa']) !== '' ? trim($_POST['nuevaEmpresa']) : $_POST['empresa'];
    $agregarEditar = $_POST['agregarEditar'];
    $estado = '1';
    if ($agregarEditar == "agregar") {
        if ($model->insertar($nombre_p, $apellido_p, $empresa, $estado)) {
            header("Location: paciente.php");
            exit();
        } else {
            echo "<script>alert('Error al agregar el paciente');</script>";
        }
    } elseif ($agregarEditar == "editar") {
        $id_usuario = $_POST['idUsuario'];
        if ($model->editar($id_usuario, $nombre_p, $apellido_p, $empresa, $estado)) {
            header("Location: paciente.php");
            exit();
        } else {
            echo "<script>alert('Error al editar el paciente');</script>";
        }
    }
}
$pacientes = $model->obtenerPacientes();
$empresas = $model->obtenerEmpresas();
$model->close();

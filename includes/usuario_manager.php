<?php
class UsuarioManager {
    private $conn;
    public $mensaje = "";

    public function __construct($conn) {
        $this->conn = $conn;
    }

    // Registro inicial: crea bodega y usuario admin
    public function registrarConBodega($nuevo_usuario, $nueva_clave, $nombre_bodega) {
        $nuevo_usuario = trim($nuevo_usuario);
        $nueva_clave = trim($nueva_clave);
        $nombre_bodega = trim($nombre_bodega);

        if ($nuevo_usuario === "" || $nueva_clave === "" || $nombre_bodega === "") {
            $this->mensaje = "Por favor, complete todos los campos.";
            return false;
        }

        // Crear bodega
        $stmt_bodega = $this->conn->prepare("INSERT INTO bodega (DESCRIPCION, estado_bodega) VALUES (?, '1')");
        $stmt_bodega->bind_param("s", $nombre_bodega);
        if ($stmt_bodega->execute()) {
            $codigo_bodega = $this->conn->insert_id;
            $stmt_bodega->close();

            // Crear usuario (rol admin por defecto)
            $hash = password_hash($nueva_clave, PASSWORD_DEFAULT);
            $stmt_usuario = $this->conn->prepare("INSERT INTO usuario (nombre_usuario, pass_usuario, cod_rol, estado_usuario) VALUES (?, ?, '1', '1')");
            $stmt_usuario->bind_param("ss", $nuevo_usuario, $hash);
            if ($stmt_usuario->execute()) {
                $stmt_usuario->close();
                $this->mensaje = "Usuario y bodega creados correctamente. Ahora puede iniciar sesión.";
                return true;
            } else {
                $this->mensaje = "Error al crear el usuario: " . $stmt_usuario->error;
                $stmt_usuario->close();
                return false;
            }
        } else {
            $this->mensaje = "Error al crear la bodega: " . $stmt_bodega->error;
            $stmt_bodega->close();
            return false;
        }
    }

    // CRUD usuario
    public function insertar($cod_rol, $nombre_usuario, $pass_usuario, $estado) {
        $pass_usuario_hash = password_hash($pass_usuario, PASSWORD_DEFAULT);
        $stmt = $this->conn->prepare("INSERT INTO usuario (COD_ROL, NOMBRE_USUARIO, PASS_USUARIO, ESTADO_USUARIO) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isss", $cod_rol, $nombre_usuario, $pass_usuario_hash, $estado);
        if ($stmt->execute()) {
            $stmt->close();
            return true;
        } else {
            $this->mensaje = "Error al agregar el usuario: " . $stmt->error;
            $stmt->close();
            return false;
        }
    }

    public function editar($id_usuario, $cod_rol, $nombre_usuario, $pass_usuario, $estado) {
        if (!empty($pass_usuario)) {
            $pass_usuario_hash = password_hash($pass_usuario, PASSWORD_DEFAULT);
            $stmt = $this->conn->prepare("UPDATE usuario SET COD_ROL = ?, NOMBRE_USUARIO = ?, PASS_USUARIO = ?, ESTADO_USUARIO = ? WHERE ID_USUARIO = ?");
            $stmt->bind_param("isssi", $cod_rol, $nombre_usuario, $pass_usuario_hash, $estado, $id_usuario);
        } else {
            $stmt = $this->conn->prepare("UPDATE usuario SET COD_ROL = ?, NOMBRE_USUARIO = ?, ESTADO_USUARIO = ? WHERE ID_USUARIO = ?");
            $stmt->bind_param("issi", $cod_rol, $nombre_usuario, $estado, $id_usuario);
        }
        if ($stmt->execute()) {
            $stmt->close();
            return true;
        } else {
            $this->mensaje = "Error al editar el usuario: " . $stmt->error;
            $stmt->close();
            return false;
        }
    }

    public function eliminar($id_usuario) {
        $stmt = $this->conn->prepare("UPDATE usuario SET ESTADO_USUARIO = '0' WHERE ID_USUARIO = ?");
        $stmt->bind_param("i", $id_usuario);
        if ($stmt->execute()) {
            $stmt->close();
            return true;
        } else {
            $this->mensaje = "Error al eliminar el usuario: " . $stmt->error;
            $stmt->close();
            return false;
        }
    }
}

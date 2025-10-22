<?php 
include_once('../Model/ProcesarLogin.php');

header('Content-Type: application/json; charset=utf-8');

function error($mensaje) {
    echo json_encode(['ok' => false, 'message' => $mensaje]);
    exit;
}

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        
        $email = trim($_POST['mail'] ?? "");
        $password = trim($_POST['password'] ?? '');


      
        if (empty($email) || empty($password)) {
            error('Email o contraseña no pueden estar vacíos.');
        }
                
   
        $user = checkCredentials($email, $password);

        if ($user["email"]==$email && password_verify($password, $user['clave'])) {
            session_start();

            $_SESSION['IDusuario'] = $user['IDusuario'];
            $_SESSION['Rol'] = $user['rol_nombre'];
            $_SESSION['Categoria'] = $user['categoria_nombre'];
            $_SESSION['Nombre'] = $user['nombreUsuario'];

            switch($user['rol_id']) {
                case 0:
                    $Ruta = 'DashboardAdmin.php';
                    break;
                case 1:
                    $Ruta = 'DashBoardCliente.php';
                    break;
                case 2:
                    $Ruta = 'DashboardTienda.php';
                    break;
            }
            echo json_encode([
                'ok' => true,
                'message' => 'Inicio de sesión exitoso.',
                'redirect' => '../View/'.$Ruta.'' 
            ]);
        } else {
           
            error('Email o contraseña incorrectos.');
        }
    } else {
        error('Método no permitido.');
    }
} catch (Exception $e) {
    
    error(mensaje: 'Ocurrió un error: ' . $e->getMessage());
}
?>
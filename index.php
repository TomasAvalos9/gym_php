<?php
// Incluir archivo de configuración
require_once 'config.php';

// Inicializar variables
$error = '';

// Procesar el formulario si se ha enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = limpiarDato($_POST['username']);
    $password = $_POST['password'];
    
    if (empty($username) || empty($password)) {
        $error = "Por favor, complete todos los campos";
    } else {
        // Verificar si es un gimnasio
        $stmt = $pdo->prepare("SELECT idgimnasio, gimnasio, password FROM gimnasio WHERE gimnasio = ? AND deleted = 0");
        $stmt->execute([$username]);
        $gimnasio = $stmt->fetch();
        
        if ($gimnasio && password_verify($password, $gimnasio['password'])) {
            // Iniciar sesión como gimnasio
            $_SESSION['user_id'] = $gimnasio['idgimnasio'];
            $_SESSION['gimnasio_id'] = $gimnasio['idgimnasio'];
            $_SESSION['username'] = $gimnasio['gimnasio'];
            $_SESSION['tipo_usuario'] = 1; // 1 = gimnasio
            
            header("Location: gimnasio/dashboard.php");
            exit;
        } else {
            // Verificar si es un miembro
            $stmt = $pdo->prepare("SELECT m.idmiembro, m.miembro, m.password, m.idgimnasio FROM miembros m WHERE m.miembro = ? AND m.deleted = 0");
            $stmt->execute([$username]);
            $miembro = $stmt->fetch();
            
            if ($miembro && password_verify($password, $miembro['password'])) {
                // Iniciar sesión como miembro
                $_SESSION['user_id'] = $miembro['idmiembro'];
                $_SESSION['gimnasio_id'] = $miembro['idgimnasio'];
                $_SESSION['username'] = $miembro['miembro'];
                $_SESSION['tipo_usuario'] = 2; // 2 = miembro
                
                header("Location: miembro/dashboard.php");
                exit;
            } else {
                $error = "Usuario o contraseña incorrectos";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistema de Gestión de Gimnasio</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-container {
            max-width: 400px;
            width: 100%;
            padding: 15px;
        }
        .card {
            border-radius: 10px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }
        .card-header {
            border-radius: 10px 10px 0 0 !important;
            background-color: #343a40;
            color: white;
            text-align: center;
            padding: 20px;
        }
        .logo {
            font-size: 3rem;
            margin-bottom: 10px;
        }
        .btn-primary {
            background-color: #343a40;
            border-color: #343a40;
        }
        .btn-primary:hover {
            background-color: #23272b;
            border-color: #23272b;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="card">
            <div class="card-header">
                <div class="logo">
                    <i class="fas fa-dumbbell"></i>
                </div>
                <h3>GymSystem</h3>
                <p class="mb-0">Sistema de Gestión de Gimnasio</p>
            </div>
            <div class="card-body p-4">
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                    <div class="mb-3">
                        <label for="username" class="form-label">Usuario</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-user"></i></span>
                            <input type="text" class="form-control" id="username" name="username" placeholder="Ingrese su usuario" required>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label for="password" class="form-label">Contraseña</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            <input type="password" class="form-control" id="password" name="password" placeholder="Ingrese su contraseña" required>
                        </div>
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-sign-in-alt"></i> Iniciar Sesión
                        </button>
                    </div>
                </form>
            </div>
            <div class="card-footer text-center bg-light">
                <small class="text-muted">Acceso para gimnasios y miembros</small>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

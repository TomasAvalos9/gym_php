<?php
// Incluir archivo de configuración
require_once 'config.php';

// Verificar si la base de datos ya está configurada
$db_exists = false;
try {
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    if (in_array('gimnasio', $tables) && in_array('miembros', $tables)) {
        $db_exists = true;
    }
} catch (PDOException $e) {
    // La base de datos no existe o hay un error de conexión
}

// Inicializar mensaje
$mensaje = '';

// Procesar la configuración si se envía el formulario
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['setup'])) {
    try {
        // Crear tablas
        $pdo->exec("
            -- Crear tabla localidades
            CREATE TABLE IF NOT EXISTS localidades (
                idlocalidad INT AUTO_INCREMENT PRIMARY KEY,
                localidad VARCHAR(100) NOT NULL,
                deleted TINYINT(1) DEFAULT 0
            );

            -- Crear tabla barrios
            CREATE TABLE IF NOT EXISTS barrios (
                idbarrio INT AUTO_INCREMENT PRIMARY KEY,
                barrio VARCHAR(100) NOT NULL,
                deleted TINYINT(1) DEFAULT 0
            );

            -- Crear tabla gimnasio
            CREATE TABLE IF NOT EXISTS gimnasio (
                idgimnasio INT AUTO_INCREMENT PRIMARY KEY,
                gimnasio VARCHAR(100) NOT NULL,
                direccion VARCHAR(200) NOT NULL,
                telefono VARCHAR(20) NOT NULL,
                correo VARCHAR(100) NOT NULL,
                localidad VARCHAR(100) NOT NULL,
                horario VARCHAR(100) NOT NULL,
                estado INT NOT NULL,
                password VARCHAR(255) NOT NULL,
                deleted TINYINT(1) DEFAULT 0
            );

            -- Crear tabla membresias
            CREATE TABLE IF NOT EXISTS membresias (
                idmembresia INT AUTO_INCREMENT PRIMARY KEY,
                membresia VARCHAR(100) NOT NULL,
                precio DECIMAL(10,2) NOT NULL,
                descripcion TEXT,
                idgimnasio INT NOT NULL,
                deleted TINYINT(1) DEFAULT 0,
                FOREIGN KEY (idgimnasio) REFERENCES gimnasio(idgimnasio)
            );

            -- Crear tabla detalles
            CREATE TABLE IF NOT EXISTS detalles (
                iddetalles INT AUTO_INCREMENT PRIMARY KEY,
                lunes TEXT,
                martes TEXT,
                miercoles TEXT,
                jueves TEXT,
                viernes TEXT,
                sabado TEXT,
                deleted TINYINT(1) DEFAULT 0
            );

            -- Crear tabla rutinas
            CREATE TABLE IF NOT EXISTS rutinas (
                idrutina INT AUTO_INCREMENT PRIMARY KEY,
                rutina VARCHAR(100) NOT NULL,
                iddetalles INT NOT NULL,
                idgimnasio INT NOT NULL,
                deleted TINYINT(1) DEFAULT 0,
                FOREIGN KEY (iddetalles) REFERENCES detalles(iddetalles),
                FOREIGN KEY (idgimnasio) REFERENCES gimnasio(idgimnasio)
            );

            -- Crear tabla miembros
            CREATE TABLE IF NOT EXISTS miembros (
                idmiembro INT AUTO_INCREMENT PRIMARY KEY,
                miembro VARCHAR(100) NOT NULL,
                dni VARCHAR(20) NOT NULL,
                telefono VARCHAR(20) NOT NULL,
                correo VARCHAR(100) NOT NULL,
                idbarrio INT NOT NULL,
                idlocalidad INT NOT NULL,
                estado TINYINT(1) DEFAULT 0,
                idmembresia INT NOT NULL,
                estado_membresia TINYINT(1) DEFAULT 1,
                idgimnasio INT NOT NULL,
                password VARCHAR(255) NOT NULL,
                deleted TINYINT(1) DEFAULT 0,
                FOREIGN KEY (idbarrio) REFERENCES barrios(idbarrio),
                FOREIGN KEY (idlocalidad) REFERENCES localidades(idlocalidad),
                FOREIGN KEY (idmembresia) REFERENCES membresias(idmembresia),
                FOREIGN KEY (idgimnasio) REFERENCES gimnasio(idgimnasio)
            );

            -- Crear tabla rutinas_miembro (relación muchos a muchos)
            CREATE TABLE IF NOT EXISTS rutinas_miembro (
                id INT AUTO_INCREMENT PRIMARY KEY,
                idrutina INT NOT NULL,
                idmiembro INT NOT NULL,
                FOREIGN KEY (idrutina) REFERENCES rutinas(idrutina),
                FOREIGN KEY (idmiembro) REFERENCES miembros(idmiembro)
            );
        ");

        // Insertar datos de ejemplo
        // Localidades
        $pdo->exec("
            INSERT INTO localidades (localidad) VALUES 
            ('Ciudad Capital'),
            ('Villa Norte'),
            ('San Martín'),
            ('Costa Este');
        ");

        // Barrios
        $pdo->exec("
            INSERT INTO barrios (barrio) VALUES 
            ('Centro'),
            ('Norte'),
            ('Sur'),
            ('Este'),
            ('Oeste');
        ");

        // Gimnasios (con contraseñas hasheadas)
        $password_hash_gymfit = password_hash('password', PASSWORD_DEFAULT);
        $password_hash_powergym = password_hash('password', PASSWORD_DEFAULT);
        
        $stmt = $pdo->prepare("
            INSERT INTO gimnasio (gimnasio, direccion, telefono, correo, localidad, horario, estado, password) VALUES 
            ('GymFit', 'Av. Principal 123', '555-1234', 'info@gymfit.com', 'Ciudad Capital', 'Lun-Sáb: 7:00-22:00', 15, ?),
            ('PowerGym', 'Calle Secundaria 456', '555-5678', 'contacto@powergym.com', 'Villa Norte', 'Lun-Vie: 6:00-23:00, Sáb-Dom: 8:00-20:00', 8, ?);
        ");
        $stmt->execute([$password_hash_gymfit, $password_hash_powergym]);

        // Membresías para GymFit
        $pdo->exec("
            INSERT INTO membresias (membresia, precio, descripcion, idgimnasio) VALUES 
            ('Básica GymFit', 30.00, 'Acceso a todas las instalaciones en horario limitado', 1),
            ('Premium GymFit', 50.00, 'Acceso ilimitado a todas las instalaciones y clases', 1);
        ");

        // Membresías para PowerGym
        $pdo->exec("
            INSERT INTO membresias (membresia, precio, descripcion, idgimnasio) VALUES 
            ('Estándar PowerGym', 35.00, 'Acceso a sala de musculación y cardio', 2),
            ('VIP PowerGym', 60.00, 'Acceso completo con entrenador personal', 2);
        ");

        // Detalles de rutinas para GymFit
        $pdo->exec("
            INSERT INTO detalles (lunes, martes, miercoles, jueves, viernes, sabado) VALUES 
            ('Pecho y Bíceps: 4x12 press banca, 3x12 curl bíceps', 'Descanso', 'Espalda y Tríceps: 4x12 dominadas, 3x12 extensiones', 'Descanso', 'Piernas: 4x12 sentadillas, 4x15 extensiones', 'Cardio: 30 min HIIT'),
            ('Cardio: 45 min ritmo moderado', 'Fuerza: Circuito full body', 'Cardio: 30 min HIIT', 'Fuerza: Enfoque en core', 'Cardio: 45 min ritmo moderado', 'Yoga o estiramiento');
        ");

        // Rutinas para GymFit
        $pdo->exec("
            INSERT INTO rutinas (rutina, iddetalles, idgimnasio) VALUES 
            ('Hipertrofia Básica', 1, 1),
            ('Fitness General', 2, 1);
        ");

        // Detalles de rutinas para PowerGym
        $pdo->exec("
            INSERT INTO detalles (lunes, martes, miercoles, jueves, viernes, sabado) VALUES 
            ('Pecho: 5x5 press banca, 4x8 aperturas', 'Espalda: 5x5 peso muerto, 4x8 remo', 'Hombros: 5x5 press militar, 4x8 elevaciones', 'Piernas: 5x5 sentadillas, 4x8 extensiones', 'Brazos: 4x8 curl bíceps, 4x8 extensiones tríceps', 'Descanso activo'),
            ('Cardio intenso: 20 min HIIT', 'Fuerza: Tren superior', 'Cardio moderado: 45 min', 'Fuerza: Tren inferior', 'Cardio intenso: 20 min HIIT', 'Descanso');
        ");

        // Rutinas para PowerGym
        $pdo->exec("
            INSERT INTO rutinas (rutina, iddetalles, idgimnasio) VALUES 
            ('Fuerza Avanzada', 3, 2),
            ('Cardio y Fuerza', 4, 2);
        ");

        // Miembros para GymFit (con contraseñas hasheadas)
        $password_hash_juan = password_hash('password', PASSWORD_DEFAULT);
        $password_hash_maria = password_hash('password', PASSWORD_DEFAULT);
        
        $stmt = $pdo->prepare("
            INSERT INTO miembros (miembro, dni, telefono, correo, idbarrio, idlocalidad, estado, idmembresia, estado_membresia, idgimnasio, password) VALUES 
            ('Juan Pérez', '12345678', '555-1111', 'juan@example.com', 1, 1, 1, 1, 1, 1, ?),
            ('María García', '87654321', '555-2222', 'maria@example.com', 2, 1, 0, 2, 1, 1, ?);
        ");
        $stmt->execute([$password_hash_juan, $password_hash_maria]);

        // Miembros para PowerGym (con contraseñas hasheadas)
        $password_hash_carlos = password_hash('password', PASSWORD_DEFAULT);
        $password_hash_ana = password_hash('password', PASSWORD_DEFAULT);
        
        $stmt = $pdo->prepare("
            INSERT INTO miembros (miembro, dni, telefono, correo, idbarrio, idlocalidad, estado, idmembresia, estado_membresia, idgimnasio, password) VALUES 
            ('Carlos Rodríguez', '23456789', '555-3333', 'carlos@example.com', 3, 2, 1, 3, 1, 2, ?),
            ('Ana Martínez', '98765432', '555-4444', 'ana@example.com', 4, 2, 0, 4, 1, 2, ?);
        ");
        $stmt->execute([$password_hash_carlos, $password_hash_ana]);

        // Asignar rutinas a miembros
        $pdo->exec("
            INSERT INTO rutinas_miembro (idrutina, idmiembro) VALUES 
            (1, 1), -- Hipertrofia Básica para Juan
            (2, 2), -- Fitness General para María
            (3, 3), -- Fuerza Avanzada para Carlos
            (4, 4); -- Cardio y Fuerza para Ana
        ");

        $mensaje = '<div class="alert alert-success">Base de datos configurada correctamente. Ahora puede <a href="index.php" class="alert-link">iniciar sesión</a>.</div>';
        $db_exists = true;
    } catch (PDOException $e) {
        $mensaje = '<div class="alert alert-danger">Error al configurar la base de datos: ' . $e->getMessage() . '</div>';
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración - Sistema de Gestión de Gimnasio</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            padding: 40px 0;
        }
        .setup-container {
            max-width: 800px;
            margin: 0 auto;
        }
        .card {
            border-radius: 10px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }
        .card-header {
            background-color: #343a40;
            color: white;
            text-align: center;
            padding: 20px;
            border-radius: 10px 10px 0 0 !important;
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
    <div class="container setup-container">
        <div class="card">
            <div class="card-header">
                <div class="logo">
                    <i class="fas fa-dumbbell"></i>
                </div>
                <h3>GymSystem</h3>
                <p class="mb-0">Configuración Inicial</p>
            </div>
            <div class="card-body p-4">
                <?php echo $mensaje; ?>
                
                <?php if (!$db_exists): ?>
                    <div class="alert alert-info">
                        <h4 class="alert-heading">Bienvenido a GymSystem</h4>
                        <p>Este asistente configurará la base de datos con las tablas necesarias y datos de ejemplo para comenzar a utilizar el sistema.</p>
                        <hr>
                        <p class="mb-0">Haga clic en el botón "Configurar Base de Datos" para comenzar.</p>
                    </div>
                    
                    <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                        <div class="d-grid gap-2">
                            <button type="submit" name="setup" class="btn btn-primary btn-lg">
                                <i class="fas fa-database"></i> Configurar Base de Datos
                            </button>
                        </div>
                    </form>
                <?php else: ?>
                    <div class="alert alert-success">
                        <h4 class="alert-heading">Base de datos ya configurada</h4>
                        <p>La base de datos ya está configurada y lista para usar.</p>
                        <hr>
                        <p class="mb-0">Puede <a href="index.php" class="alert-link">iniciar sesión</a> para comenzar a utilizar el sistema.</p>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <a href="index.php" class="btn btn-primary btn-lg">
                            <i class="fas fa-sign-in-alt"></i> Ir a Iniciar Sesión
                        </a>
                    </div>
                <?php endif; ?>
                
                <div class="mt-4">
                    <h5>Credenciales de Acceso</h5>
                    <div class="card mb-3">
                        <div class="card-header bg-info text-white">
                            <h6 class="mb-0">Acceso como Gimnasio</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Usuario:</strong> GymFit</p>
                                    <p><strong>Contraseña:</strong> password</p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Usuario:</strong> PowerGym</p>
                                    <p><strong>Contraseña:</strong> password</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card">
                        <div class="card-header bg-success text-white">
                            <h6 class="mb-0">Acceso como Miembro</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Usuario:</strong> Juan Pérez</p>
                                    <p><strong>Contraseña:</strong> password</p>
                                    <p><small class="text-muted">Miembro de GymFit</small></p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Usuario:</strong> Carlos Rodríguez</p>
                                    <p><strong>Contraseña:</strong> password</p>
                                    <p><small class="text-muted">Miembro de PowerGym</small></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer text-center bg-light">
                <small class="text-muted">Sistema de Gestión de Gimnasio v3.0</small>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

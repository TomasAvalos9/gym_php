-- Script SQL para la base de datos del gimnasio con segmentación completa
-- Creado para importar en phpMyAdmin - Versión 3.0

-- Crear tabla gimnasio
CREATE TABLE gimnasio (
    idgimnasio INT AUTO_INCREMENT PRIMARY KEY,
    gimnasio VARCHAR(100) NOT NULL,
    direccion VARCHAR(200) NOT NULL,
    telefono VARCHAR(20) NOT NULL,
    correo VARCHAR(100) NOT NULL,
    localidad VARCHAR(100) NOT NULL,
    horario VARCHAR(100) NOT NULL,
    estado INT NOT NULL,
    deleted TINYINT(1) DEFAULT 0
);

-- Crear tabla localidades
CREATE TABLE localidades (
    idlocalidad INT AUTO_INCREMENT PRIMARY KEY,
    localidad VARCHAR(100) NOT NULL,
    deleted TINYINT(1) DEFAULT 0
);

-- Crear tabla barrios
CREATE TABLE barrios (
    idbarrio INT AUTO_INCREMENT PRIMARY KEY,
    barrio VARCHAR(100) NOT NULL,
    deleted TINYINT(1) DEFAULT 0
);

-- Crear tabla membresias con relación al gimnasio
CREATE TABLE membresias (
    idmembresia INT AUTO_INCREMENT PRIMARY KEY,
    membresia VARCHAR(100) NOT NULL,
    precio DECIMAL(10,2) NOT NULL,
    descripcion TEXT,
    idgimnasio INT NOT NULL,
    deleted TINYINT(1) DEFAULT 0,
    FOREIGN KEY (idgimnasio) REFERENCES gimnasio(idgimnasio)
);

-- Crear tabla detalles
CREATE TABLE detalles (
    iddetalles INT AUTO_INCREMENT PRIMARY KEY,
    lunes TEXT,
    martes TEXT,
    miercoles TEXT,
    jueves TEXT,
    viernes TEXT,
    sabado TEXT,
    idgimnasio INT NOT NULL,
    deleted TINYINT(1) DEFAULT 0,
    FOREIGN KEY (idgimnasio) REFERENCES gimnasio(idgimnasio)
);

-- Crear tabla miembros con relación al gimnasio
CREATE TABLE miembros (
    idmiembro INT AUTO_INCREMENT PRIMARY KEY,
    miembro VARCHAR(100) NOT NULL,
    dni VARCHAR(20) NOT NULL,
    telefono VARCHAR(20) NOT NULL,
    gmail VARCHAR(100) NOT NULL,
    idbarrio INT NOT NULL,
    idlocalidad INT NOT NULL,
    estado TINYINT(1) DEFAULT 0,
    idmembresia INT NOT NULL,
    estado_membresia TINYINT(1) DEFAULT 1,
    idgimnasio INT NOT NULL,
    deleted TINYINT(1) DEFAULT 0,
    FOREIGN KEY (idbarrio) REFERENCES barrios(idbarrio),
    FOREIGN KEY (idlocalidad) REFERENCES localidades(idlocalidad),
    FOREIGN KEY (idmembresia) REFERENCES membresias(idmembresia),
    FOREIGN KEY (idgimnasio) REFERENCES gimnasio(idgimnasio)
);

-- Crear tabla rutinas con relación al gimnasio
CREATE TABLE rutinas (
    idrutina INT AUTO_INCREMENT PRIMARY KEY,
    rutina VARCHAR(100) NOT NULL,
    iddetalles INT NOT NULL,
    idgimnasio INT NOT NULL,
    deleted TINYINT(1) DEFAULT 0,
    FOREIGN KEY (iddetalles) REFERENCES detalles(iddetalles),
    FOREIGN KEY (idgimnasio) REFERENCES gimnasio(idgimnasio)
);

-- Crear tabla rutinas_miembro (relación muchos a muchos)
CREATE TABLE rutinas_miembro (
    id INT AUTO_INCREMENT PRIMARY KEY,
    idmiembro INT NOT NULL,
    idrutina INT NOT NULL,
    FOREIGN KEY (idmiembro) REFERENCES miembros(idmiembro),
    FOREIGN KEY (idrutina) REFERENCES rutinas(idrutina)
);

-- Crear tabla usuarios para autenticación
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(64) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    tipo_usuario INT NOT NULL,
    gimnasio_id INT NULL,
    miembro_id INT NULL,
    FOREIGN KEY (gimnasio_id) REFERENCES gimnasio(idgimnasio),
    FOREIGN KEY (miembro_id) REFERENCES miembros(idmiembro)
);

-- Insertar datos de ejemplo
-- Insertar localidades
INSERT INTO localidades (localidad) VALUES 
('Ciudad Central'), 
('Villa Norte'), 
('Barrio Sur');

-- Insertar barrios
INSERT INTO barrios (barrio) VALUES 
('Centro'), 
('Palermo'), 
('Belgrano'), 
('Recoleta');

-- Insertar gimnasios
INSERT INTO gimnasio (gimnasio, direccion, telefono, correo, localidad, horario, estado) VALUES 
('GymFit Central', 'Av. Principal 123', '555-1234', 'info@gymfitcentral.com', 'Ciudad Central', 'Lun-Vie: 7am-10pm, Sáb-Dom: 9am-8pm', 15),
('PowerGym Norte', 'Calle Norte 456', '555-5678', 'contacto@powergym.com', 'Villa Norte', 'Lun-Vie: 6am-11pm, Sáb-Dom: 8am-9pm', 8);

-- Insertar membresías para GymFit Central
INSERT INTO membresias (membresia, precio, descripcion, idgimnasio) VALUES 
('Básica GymFit', 5000, 'Acceso a equipamiento básico y clases grupales', 1),
('Premium GymFit', 8000, 'Acceso completo a todas las instalaciones y clases', 1),
('VIP GymFit', 12000, 'Acceso completo, entrenador personal y nutricionista', 1);

-- Insertar membresías para PowerGym Norte
INSERT INTO membresias (membresia, precio, descripcion, idgimnasio) VALUES 
('Básica PowerGym', 5500, 'Acceso a equipamiento básico y clases grupales', 2),
('Premium PowerGym', 8500, 'Acceso completo a todas las instalaciones y clases', 2),
('VIP PowerGym', 13000, 'Acceso completo, entrenador personal y nutricionista', 2);

-- Insertar detalles de rutinas para GymFit Central
INSERT INTO detalles (lunes, martes, miercoles, jueves, viernes, sabado, idgimnasio) VALUES 
('Pecho y bíceps', 'Descanso', 'Piernas', 'Espalda y tríceps', 'Hombros y abdominales', 'Cardio', 1),
('Cardio y core', 'Fuerza superior', 'Descanso', 'HIIT', 'Fuerza inferior', 'Yoga', 1);

-- Insertar detalles de rutinas para PowerGym Norte
INSERT INTO detalles (lunes, martes, miercoles, jueves, viernes, sabado, idgimnasio) VALUES 
('Full body', 'Cardio', 'Full body', 'Descanso', 'Full body', 'Cardio', 2),
('Fuerza', 'Flexibilidad', 'Fuerza', 'Cardio', 'Fuerza', 'Descanso', 2);

-- Insertar rutinas para GymFit Central
INSERT INTO rutinas (rutina, iddetalles, idgimnasio) VALUES 
('Rutina de fuerza GymFit', 1, 1),
('Rutina mixta GymFit', 2, 1);

-- Insertar rutinas para PowerGym Norte
INSERT INTO rutinas (rutina, iddetalles, idgimnasio) VALUES 
('Rutina principiantes PowerGym', 3, 2),
('Rutina avanzada PowerGym', 4, 2);

-- Insertar usuarios para gimnasios
INSERT INTO usuarios (username, password, tipo_usuario, gimnasio_id, miembro_id) VALUES 
('gymfit', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, 1, NULL),
('powergym', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, 2, NULL);

-- Insertar miembros para GymFit Central
INSERT INTO miembros (miembro, dni, telefono, gmail, idbarrio, idlocalidad, estado, idmembresia, estado_membresia, idgimnasio) VALUES 
('Juan Pérez', '12345678', '555-1111', 'juan@example.com', 1, 1, 1, 1, 1, 1),
('María López', '23456789', '555-2222', 'maria@example.com', 2, 1, 0, 2, 1, 1);

-- Insertar miembros para PowerGym Norte
INSERT INTO miembros (miembro, dni, telefono, gmail, idbarrio, idlocalidad, estado, idmembresia, estado_membresia, idgimnasio) VALUES 
('Carlos Rodríguez', '34567890', '555-3333', 'carlos@example.com', 3, 2, 1, 4, 1, 2),
('Ana Martínez', '45678901', '555-4444', 'ana@example.com', 4, 2, 0, 5, 1, 2);

-- Insertar usuarios para miembros
INSERT INTO usuarios (username, password, tipo_usuario, gimnasio_id, miembro_id) VALUES 
('juan', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 2, NULL, 1),
('maria', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 2, NULL, 2),
('carlos', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 2, NULL, 3),
('ana', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 2, NULL, 4);

-- Asignar rutinas a miembros
INSERT INTO rutinas_miembro (idmiembro, idrutina) VALUES 
(1, 1),
(2, 2),
(3, 3),
(4, 4);

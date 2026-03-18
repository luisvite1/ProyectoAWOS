<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Administrativo - Bar "Los Litros"</title>
    <link rel="stylesheet" href="./frontend/css/global.css">
    <link rel="stylesheet" href="./frontend/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

</head>
<body>
<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header('Location: index.html');
    exit;
}

include 'backend/config.php';

// Obtener usuarios activos
$sql_activos = "SELECT COUNT(*) as total FROM usuarios WHERE activo = 1";
$result_activos = $conexion->query($sql_activos);
$activos = $result_activos ? $result_activos->fetch_assoc()['total'] : 0;

// Obtener mesas registradas
$mesas = 0;
$sql_mesas = "SELECT COUNT(*) as total FROM mesas";
$result_mesas = $conexion->query($sql_mesas);
if ($result_mesas) {
    $mesas = $result_mesas->fetch_assoc()['total'];
}

$conexion->close();
?>
    <div class="admin-container">
        <!-- Encabezado -->
        <div class="admin-header">
            <div class="admin-header-content">
                <h1>Administrador</h1>
                <p>Panel principal </p>
            </div>
            <div class="menu-icon" id="menuToggle">
                <i class="fas fa-bars"></i>
                <div class="dropdown-menu" id="dropdownMenu">
                    <a href="#perfil"><i class="fas fa-user"></i>Perfil</a>
                    <a href="#terminos"><i class="fas fa-file-contract"></i>Términos y condiciones</a>
                    <button id="logoutBtn" class="btn btn-danger"><i class="fas fa-sign-out-alt"></i>Cerrar sesión</button>
                </div>
            </div>
        </div>

        <!-- Contenido principal -->
        <div class="admin-main">
            <!-- Sección de bienvenida -->
            <div class="welcome-section">
                <h2>Hola 👋</h2>
                <p>Desde aquí puedes gestionar usuarios y mesas del sistema de comendero.</p>
            </div>

            <!-- Accesos rápidos -->
            <div class="cards-grid">
                <div class="card" onclick="window.location.href='admin_perfil.php'">
                    <div class="card-icon"><i class="fas fa-user-circle"></i></div>
                    <h3>Ver perfil</h3>
                    <p>Consulta tu información, rol y estado en el sistema.</p>
                </div>

                <div class="card" onclick="window.location.href='admin_usuarios.php'">
                    <div class="card-icon"><i class="fas fa-users"></i></div>
                    <h3>Administrar usuarios</h3>
                    <p>Revisa, edita o elimina cuentas registradas.</p>
                </div>

                <div class="card" onclick="window.location.href='admin_registrar_usuario.html'">
                    <div class="card-icon"><i class="fas fa-user-plus"></i></div>
                    <h3>Registrar usuario</h3>
                    <p>Crea nuevas cuentas para meseros, barra, cocina o seguridad.</p>
                </div>

                <div class="card" onclick="window.location.href='admin_mesas.html'">
                    <div class="card-icon"><i class="fas fa-table"></i></div>
                    <h3>Control de mesas</h3>
                    <p>Visualiza el estado general de las mesas registradas.</p>
                </div>
            </div>

            <!-- Resumen rápido -->
            <div class="summary-section">
                <h3>Resumen rápido</h3>
                <div class="summary-cards">
                    <div class="summary-card">
                        <div class="summary-card-number" id="usuarios-activos"><?php echo $activos; ?></div>
                        <p class="summary-card-label">Usuarios activos</p>
                        <p class="summary-card-status">Actualizado</p>
                    </div>

                     <div class="summary-card">
                        <div class="summary-card-number" id="mesas-registradas"><?php echo $mesas; ?></div>
                        <p class="summary-card-label">Mesas registradas</p>
                        <p class="summary-card-status">Actualizado</p>
                    </div> 
                </div>
            </div>
        </div>
    </div>

    <script>
        // Toggle del menú
        document.getElementById('menuToggle').addEventListener('click', function(e) {
            e.stopPropagation();
            document.getElementById('dropdownMenu').classList.toggle('active');
        });

        // Cerrar menú al hacer clic fuera
        document.addEventListener('click', function() {
            document.getElementById('dropdownMenu').classList.remove('active');
        });

        // Cerrar sesión
        document.getElementById('logoutBtn').addEventListener('click', function() {
            if (confirm('¿Estás seguro de que deseas cerrar sesión?')) {
                // Limpiar localStorage y redirigir al login
                localStorage.clear();
                window.location.href = 'index.html';
            }
        });

        // Función para navegar a secciones
        function navigateTo(section) {
            console.log('Navegando a:', section);
            // Aquí puedes agregar lógica para navegar a diferentes secciones o módulos
            alert('Funcionalidad próximamente disponible: ' + section);
        }
    </script>
</body>
</html>

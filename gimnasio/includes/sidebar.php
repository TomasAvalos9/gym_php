<nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block sidebar collapse">
    <div class="sidebar-sticky pt-3">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : '' ?>" href="dashboard.php">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'miembros.php' ? 'active' : '' ?>" href="miembros.php">
                    <i class="fas fa-users"></i> Miembros
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'rutinas.php' ? 'active' : '' ?>" href="rutinas.php">
                    <i class="fas fa-dumbbell"></i> Rutinas
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'membresias.php' ? 'active' : '' ?>" href="membresias.php">
                    <i class="fas fa-id-card"></i> Membresías
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'otros.php' ? 'active' : '' ?>" href="otros.php">
                    <i class="fas fa-cog"></i> Otros
                </a>
            </li>
        </ul>
    </div>
</nav>

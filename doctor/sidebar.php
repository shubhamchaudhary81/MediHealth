<?php
// Get current page for active state
$current_page = basename($_SERVER['PHP_SELF']);
?>
<aside class="sidebar">
    <div class="sidebar-header">
        <a href="doctordash.php" class="logo">
            <i class="fas fa-heartbeat"></i>
            <span>MediHealth</span>
        </a>
    </div>
    
    <nav class="sidebar-nav">
        <ul>
            <li class="nav-item">
                <a href="doctordash.php" class="nav-link <?php echo $current_page === 'doctordash.php' ? 'active' : ''; ?>">
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="appointments.php" class="nav-link <?php echo $current_page === 'appointments.php' ? 'active' : ''; ?>">
                    <i class="fas fa-calendar-check"></i>
                    <span>Appointments</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="patients.php" class="nav-link <?php echo $current_page === 'patients.php' ? 'active' : ''; ?>">
                    <i class="fas fa-users"></i>
                    <span>Patients</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="prescriptions.php" class="nav-link <?php echo $current_page === 'prescriptions.php' ? 'active' : ''; ?>">
                    <i class="fas fa-prescription"></i>
                    <span>Prescriptions</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="medical_records.php" class="nav-link <?php echo $current_page === 'medical_records.php' ? 'active' : ''; ?>">
                    <i class="fas fa-file-medical"></i>
                    <span>Medical Records</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="settings.php" class="nav-link <?php echo $current_page === 'settings.php' ? 'active' : ''; ?>">
                    <i class="fas fa-cog"></i>
                    <span>Settings</span>
                </a>
            </li>
        </ul>
        <hr style="margin: 1.5rem 0; border: none; border-top: 1px solid var(--border-color);">
        <a href="logout.php" class="btn btn-danger" style="margin: 0 1.5rem; display: block; text-align: center; background: var(--warning-color); color: #fff; border-radius: var(--radius); padding: 0.75rem 0; font-weight: 600; text-decoration: none;">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </nav>
</aside>

<style>
    /* Sidebar Styles */
    .sidebar {
        width: var(--sidebar-width);
        background: white;
        box-shadow: 2px 0 10px rgba(0,0,0,0.1);
        position: fixed;
        height: 100vh;
        transition: all 0.3s ease;
        z-index: 1000;
    }

    .sidebar-header {
        padding: 1.5rem;
        border-bottom: 1px solid var(--border-color);
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .logo {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        text-decoration: none;
        color: var(--primary-color);
        font-weight: 600;
        font-size: 1.25rem;
    }

    .logo i {
        font-size: 1.5rem;
    }

    .sidebar-nav {
        padding: 1.5rem 0;
    }

    .nav-item {
        list-style: none;
        margin-bottom: 0.5rem;
    }

    .nav-link {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.75rem 1.5rem;
        color: var(--gray-color);
        text-decoration: none;
        transition: all 0.3s ease;
    }

    .nav-link:hover, .nav-link.active {
        background: var(--light-color);
        color: var(--primary-color);
    }

    .nav-link i {
        font-size: 1.25rem;
    }

    @media (max-width: 1024px) {
        .sidebar {
            width: 80px;
        }

        .sidebar .logo span,
        .sidebar .nav-link span {
            display: none;
        }

        .main-content {
            margin-left: 80px;
        }
    }
</style> 
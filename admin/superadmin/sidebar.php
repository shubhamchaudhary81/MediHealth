<?php
// Get current page for active menu highlighting
$current_page = basename($_SERVER['PHP_SELF']);
?>
<div class="sidebar">
    <div class="sidebar-header">
        <h2>MediHealth</h2>
    </div>
    <ul class="sidebar-menu">
        <li>
            <a href="dashboard.php" class="<?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
        </li>
        <li>
            <a href="hospitals.php" class="<?php echo ($current_page == 'hospitals.php') ? 'active' : ''; ?>">
                <i class="fas fa-hospital"></i> Hospitals
            </a>
        </li>
        <li>
            <a href="pending_hospital.php" class="<?php echo ($current_page == 'pending_hospital.php') ? 'active' : ''; ?>">
                <i class="fas fa-clock"></i> Pending Hospitals
            </a>
        </li>
        <li>
            <a href="doctors.php" class="<?php echo ($current_page == 'doctors.php') ? 'active' : ''; ?>">
                <i class="fas fa-user-md"></i> Doctors
            </a>
        </li>
        <li>
            <a href="patients.php" class="<?php echo ($current_page == 'patients.php') ? 'active' : ''; ?>">
                <i class="fas fa-users"></i> Patients
            </a>
        </li>
        <li>
            <a href="profile.php" class="<?php echo ($current_page == 'profile.php') ? 'active' : ''; ?>">
                <i class="fas fa-user"></i> Profile
            </a>
        </li>
    </ul>
</div>

<style>
.sidebar {
    width: 250px;
    background-color: rgb(74, 144, 226);
    color: white;
    padding: 20px 0;
}
.sidebar-header {
    padding: 0 20px 20px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    text-align: center;
}
.sidebar-header h2 {
    margin: 0;
    font-size: 1.5rem;
}
.sidebar-menu {
    list-style: none;
    padding: 0;
    margin: 20px 0;
}
.sidebar-menu li {
    margin-bottom: 5px;
}
.sidebar-menu a {
    display: block;
    padding: 12px 20px;
    color: white;
    text-decoration: none;
    transition: background-color 0.3s;
}
.sidebar-menu a:hover, .sidebar-menu a.active {
    background-color: rgba(255, 255, 255, 0.1);
}
.sidebar-menu i {
    margin-right: 10px;
    width: 20px;
    text-align: center;
}
</style> 
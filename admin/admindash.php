<?php
include_once ('header.php');
?>
<!-- <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medical Admin Panel</title>
    <link rel="stylesheet" href="styles.css">
</head> -->
<body>
    <!-- Navigation Bar -->
    <!-- <nav class="navbar">
        <h2 class="logo">Medical Admin</h2>
        <ul class="nav-links">
            <li><a href="#">Dashboard</a></li>
            <li><a href="#">Appointments</a></li>
            <li><a href="#">Doctors</a></li>
            <li><a href="#">Patients</a></li>
            <li><a href="#">Settings</a></li>
        </ul>
        <button class="logout-btn">Logout</button>
    </nav> -->

    <!-- Main Content -->
    <main class="main-content">
        <header class="header1">
            <h1>Dashboard Overview</h1>
            <p>Welcome to the MediHealth Admin Panel.</p><p> Manage appointments, doctors, and patients efficiently.</p>
        </header>

        <section class="dashboard-cards">
            <div class="card">
                <!-- <img src="appointments.png" alt="Appointments"> -->
                <h3>Total Appointments</h3>
                <p>120</p>
            </div>
            <div class="card">
                <!-- <img src="doctors.png" alt="Doctors"> -->
                <h3>Registered Doctors</h3>
                <p>45</p>
            </div>
            <div class="card">
                <!-- <img src="patients.png" alt="Patients"> -->
                <h3>Patients</h3>
                <p>300</p>
            </div>
        </section>

        <!-- Add Hospital and Doctor Form -->
        <section class="form-section">
            <h2>Add Hospital</h2>
            <form class="form">
                <label for="hospital-name">Hospital Name:</label>
                <input type="text" id="hospital-name" name="hospital-name" required>
                
                <label for="hospital-location">Location:</label>
                <input type="text" id="hospital-location" name="hospital-location" required>
                
                <label for="hospital-contact">Contact Number:</label>
                <input type="text" id="hospital-contact" name="hospital-contact" required>
                
                <button type="submit" class="submit-btn">Add Hospital</button>
            </form>
        </section>
        <section class="form-section">   
            <h2>Add Doctor</h2>
            <form class="form">
                <label for="doctor-name">Doctor Name:</label>
                <input type="text" id="doctor-name" name="doctor-name" required>
                
                <label for="doctor-specialty">Specialty:</label>
                <input type="text" id="doctor-specialty" name="doctor-specialty" required>
                
                <label for="doctor-hospital">Hospital:</label>
                <input type="text" id="doctor-hospital" name="doctor-hospital" required>
                
                <label for="doctor-contact">Contact Number:</label>
                <input type="text" id="doctor-contact" name="doctor-contact" required>
                
                <button type="submit" class="submit-btn">Add Doctor</button>
            </form>
        </section>
    </main>

    <?php
        // include_once ('../include/footer.php');
    ?>
</body>
</html>

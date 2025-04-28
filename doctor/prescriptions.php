
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Prescriptions - Main Content</title>
  <style>
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    body {
      display: flex;
    }

    .sidebar {
      width: 220px;
      background-color: #007bc4;
      color: white;
      padding-top: 20px;
      height: 100vh;
      position: fixed;
      left: 0;
      top: 0;
    }

    .sidebar h2 {
      text-align: center;
      margin-bottom: 30px;
    }

    .sidebar ul {
      list-style: none;
    }

    .sidebar ul li {
      padding: 0;
    }

    .sidebar ul li a {
      display: block;
      padding: 15px 20px;
      color: white;
      text-decoration: none;
    }

    .sidebar ul li a:hover,
    .sidebar ul li a.active {
      background-color: #0fa8e3;
    }

    .main-content {
      margin-left: 220px;
      padding: 20px;
      flex: 1;
      background-color: #f8f9fb;
      height: 100vh;
      overflow-y: auto;
    }

    .top-bar {
      margin-bottom: 20px;
    }

    .stats {
      display: flex;
      gap: 20px;
      margin-bottom: 20px;
    }

    .card {
      background: white;
      padding: 20px;
      flex: 1;
      border-radius: 10px;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
      text-align: center;
    }

    .card h2 {
      font-size: 24px;
    }

    .card p {
      color: #888;
    }

    .prescription-table {
      background: white;
      border-radius: 10px;
      overflow: hidden;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
    }

    table {
      width: 100%;
      border-collapse: collapse;
    }

    th, td {
      padding: 12px;
      border-bottom: 1px solid #eee;
      text-align: left;
    }

    th {
      background: #f1f3f5;
    }
  </style>
</head>
<body>
  <div class="main-content">
    <div class="top-bar">
      <h1>Prescriptions</h1>
    </div>

    <div class="stats">
      <div class="card">
        <h2>8</h2>
        <p>Total Prescriptions</p>
      </div>
      <div class="card">
        <h2 style="color: green;">7</h2>
        <p>Active</p>
      </div>
      <div class="card">
        <h2 style="color: red;">1</h2>
        <p>Expired</p>
      </div>
    </div>

    <div class="prescription-table">
      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>Patient</th>
            <th>Doctor</th>
            <th>Medications</th>
            <th>Issue Date</th>
            <th>Expiry Date</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>PRE-001</td>
            <td>Sita Sharma<br><small>PT-001</small></td>
            <td>Dr. Ramesh Adhikari</td>
            <td>3</td>
            <td>2025-04-15</td>
            <td>2025-05-15</td>
          </tr>
          <tr>
            <td>PRE-002</td>
            <td>Ram Bahadur Thapa<br><small>PT-002</small></td>
            <td>Dr. Bina Gurung</td>
            <td>2</td>
            <td>2025-04-18</td>
            <td>2025-05-18</td>
          </tr>
          <tr>
            <td>PRE-003</td>
            <td>Kiran Neupane<br><small>PT-003</small></td>
            <td>Dr. Sushil Koirala</td>
            <td>1</td>
            <td>2025-04-20</td>
            <td>2025-05-20</td>
          </tr>
          <tr>
            <td>PRE-004</td>
            <td>Anita Tamang<br><small>PT-004</small></td>
            <td>Dr. Manish Shrestha</td>
            <td>4</td>
            <td>2025-04-10</td>
            <td>2025-05-10</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</body>
</html>

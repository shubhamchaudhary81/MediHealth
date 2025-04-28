<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>MediHealth</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet" />
  <style>
    :root {
      --primary: #004080;
      --accent: #007bff;
      --bg: #eef4fa;
      --text-dark: #222;
      --text-muted: #666;
      --card-bg: #fff;
    }

    * {
      box-sizing: border-box;
    }

    body {
      font-family: 'Inter', sans-serif;
      background-color: var(--bg);
      margin: 0;
      padding: 40px;
      color: var(--text-dark);
    }

    .container {
      max-width: 900px;
      margin: auto;
      background-color: var(--card-bg);
      padding: 30px;
      border-radius: 14px;
      box-shadow: 0 10px 30px rgba(0, 64, 128, 0.15);
      border-left: 6px solid var(--primary);
    }

    .hospital-info {
      text-align: center;
      margin-bottom: 20px;
    }

    .hospital-info h1 {
      font-size: 24px;
      color: var(--primary);
      margin: 0;
    }

    .hospital-info p {
      margin: 4px 0;
      font-size: 14px;
      color: var(--text-muted);
    }

    .header {
      border-bottom: 2px solid var(--primary);
      padding-bottom: 20px;
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
    }

    .header h2 {
      margin: 0;
      font-size: 22px;
      color: var(--primary);
    }

    .header p {
      margin: 4px 0;
      font-size: 14px;
      color: var(--text-muted);
    }

    .header img {
      width: 50px;
    }

    .section {
      margin-top: 30px;
    }

    .section h3 {
      font-size: 18px;
      color: var(--accent);
      margin-bottom: 10px;
      border-left: 4px solid var(--accent);
      padding-left: 10px;
    }

    .editable-box {
      padding: 20px;
      background-color: #f9fcff;
      border-radius: 10px;
      min-height: 100px;
      border: 1px solid #d0e4f5;
      font-size: 15px;
      line-height: 1.6;
      outline: none;
    }

    .footer {
      display: flex;
      justify-content: space-between;
      margin-top: 40px;
      align-items: center;
    }

    .signature {
      width: 200px;
      height: 40px;
      border-bottom: 2px solid #000;
    }

    .qr {
      width: 90px;
      height: 90px;
      background-color: #dbe9fa;
      display: flex;
      justify-content: center;
      align-items: center;
      color: var(--primary);
      border-radius: 8px;
      font-size: 12px;
    }

    @media print {
      body {
        background: #fff;
        padding: 0;
      }

      .container {
        box-shadow: none;
        border-left: none;
        padding: 20px;
      }

      .editable-box {
        border: none;
        background: none;
      }
    }
  </style>
</head>
<body>
  <div class="container">
    
    <!-- HOSPITAL INFO -->
    <div class="hospital-info">
      <h1>Novel Hospital Biratnagar</h1>
      <p>Kanchanbari,Biratnagar,Morang Nepal</p>
      <p>Phone: 977 9876544322| Email: contact@novelhsptl@gmail.com</p>
      <p>Website: www.novelbrt.com</p>
    </div>

    <!-- DOCTOR HEADER -->
    <div class="header">
      <div>
        <h2>Dr. Balkrishna Shah</h2>
        <p>Family Physician | Reg. No. D123456</p>
        <p>emily.carter@careclinic.com | 977 9876543342</p>
      </div>
      <img src="https://img.icons8.com/ios-filled/100/004080/doctor-male.png" alt="Doctor Icon">
    </div>

    <!-- PATIENT SECTION -->
    <div class="section">
      <h3>Patient Details</h3>
      <div class="editable-box" contenteditable="true">
        Name: <br>
        Age: <br>
        Gender: <br>
        Date: <br>
      </div>
    </div>

    <!-- DIAGNOSIS -->
    <div class="section">
      <h3>Diagnosis</h3>
      <div class="editable-box" contenteditable="true">
        Describe the condition here.
      </div>
    </div>

    <!-- MEDICATIONS -->
    <div class="section">
      <h3>Prescribed Medicines</h3>
      <div class="editable-box" contenteditable="true">
        • Medicine 1 – dosage<br>
        • Medicine 2 – dosage<br>
        • Medicine 3 – dosage
      </div>
    </div>

    <!-- FOOTER -->
    <div class="footer">
      <div>
        <div class="signature"></div>
        <p style="margin-top: 6px;">Doctor’s Signature</p>
      </div>
      <div class="qr">QR Code</div>
    </div>

  </div>
</body>
</html>

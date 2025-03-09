<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>MediPoint - Patient Registration</title>
  <link rel="stylesheet" href="bye.css">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap">
  <!-- <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/lucide-icons@latest/dist/umd/lucide.min.js"> -->
  <!-- <script src="https://unpkg.com/lucide@latest"></script> -->
</head>
<body>
  <!-- <div class="panels-container">
    <div class="panel left-panel">
        <div class="content">
            <h1 style="    font-weight: 600;
            line-height: 1;
            font-size: 75px;
            color:#4A90E2 ;">MediHealth</h1>
            <p>Your Health, Our Priority </p><br>
            
          
        </div>
        <img src="register.svg" class="image" alt="" width="450px"/>
    </div>
    
</div> -->
  <div class="auth-page">
    <div class="auth-container">
      <div class="auth-content">
        <div class="auth-header">
          <a href="index.html" class="logo">
            <div class="logo-icon">
              <i data-lucide="file-text"></i>
            </div>
            <span>MediHealth</span>
          </a>
        </div>

        <div class="auth-form-container">
          <div class="auth-welcome">
            <h1>Create an account</h1>
            <p>Please enter your details to register</p>
          </div>

          <form class="auth-form">
            <div class="form-grid">
              <div class="form-group">
                <label for="firstName">First Name</label>
                <input type="text" id="firstName" class="form-input" placeholder="Enter your first name" required>
              </div>
              
              <div class="form-group">
                <label for="lastName">Last Name</label>
                <input type="text" id="lastName" class="form-input" placeholder="Enter your last name" required>
              </div>
            </div>

            <div class="form-group">
              <label for="email">Email</label>
              <input type="email" id="email" class="form-input" placeholder="Enter your email" required>
            </div>

            <div class="form-group">
              <label for="dob">Date of Birth</label>
              <input type="date" id="dob" class="form-input" required>
            </div>

            <div class="form-group">
              <label for="phone">Phone Number</label>
              <input type="tel" id="phone" class="form-input" placeholder="Enter your phone number" required>
            </div>
              <span style="font-weight: bold;">Full Address:</span><br>
            <div class="form-group">
              <label for="zone">Zone</label>
              <select id="zone" class="form-input" required>
                <option value="">Select Zone</option>
                <option value="Bagmati">Bagmati</option>
                <option value="Gandaki">Gandaki</option>
                <option value="Koshi">Koshi</option>
                <option value="Lumbini">Lumbini</option>
                <option value="Madhesh">Madhesh</option>
                <option value="Karnali">Karnali</option>
                <option value="Sudurpashchim">Sudurpashchim</option>
              </select>
            </div>
            
            <div class="form-group">
              <label for="district">District</label>
              <select id="district" class="form-input" required>
                <option value="">Select District</option>
              </select>
            </div>
            
            <div class="form-group">
              <label for="city">City</label>
              <select id="city" class="form-input" required>
                <option value="">Select City</option>
              </select>
            </div>
            
            <script>
              const districtsByZone = {
                "Bagmati": ["Kathmandu", "Lalitpur", "Bhaktapur", "Sindhupalchowk"],
                "Gandaki": ["Pokhara", "Gorkha", "Lamjung", "Tanahun"],
                "Koshi": ["Biratnagar", "Jhapa", "Morang", "Sunsari"],
                "Lumbini": ["Butwal", "Kapilvastu", "Rupandehi", "Palpa"],
                "Madhesh": ["Janakpur", "Parsa", "Bara", "Dhanusha"],
                "Karnali": ["Surkhet", "Jumla", "Mugu", "Dailekh"],
                "Sudurpashchim": ["Dhangadhi", "Kailali", "Kanchanpur", "Dadeldhura"]
              };
            
              const citiesByDistrict = {
                "Kathmandu": ["Kathmandu", "Kirtipur", "Tokha"],
                "Lalitpur": ["Patan", "Godawari", "Lubhu"],
                "Bhaktapur": ["Bhaktapur", "Thimi", "Suryabinayak"],
                "Pokhara": ["Pokhara", "Lekhnath"],
                "Biratnagar": ["Biratnagar", "Itahari"],
                "Butwal": ["Butwal", "Tilottama"],
                "Janakpur": ["Janakpur", "Mahendranagar"],
                "Surkhet": ["Surkhet", "Birendranagar"],
                "Dhangadhi": ["Dhangadhi", "Tikapur"]
              };
            
              document.getElementById('zone').addEventListener('change', function() {
                const districtSelect = document.getElementById('district');
                const citySelect = document.getElementById('city');
                districtSelect.innerHTML = '<option value="">Select District</option>';
                citySelect.innerHTML = '<option value="">Select City</option>';
                
                const selectedZone = this.value;
                if (selectedZone && districtsByZone[selectedZone]) {
                  districtsByZone[selectedZone].forEach(district => {
                    let option = document.createElement('option');
                    option.value = district;
                    option.textContent = district;
                    districtSelect.appendChild(option);
                  });
                }
              });
            
              document.getElementById('district').addEventListener('change', function() {
                const citySelect = document.getElementById('city');
                citySelect.innerHTML = '<option value="">Select City</option>';
                
                const selectedDistrict = this.value;
                if (selectedDistrict && citiesByDistrict[selectedDistrict]) {
                  citiesByDistrict[selectedDistrict].forEach(city => {
                    let option = document.createElement('option');
                    option.value = city;
                    option.textContent = city;
                    citySelect.appendChild(option);
                  });
                }
              });
            </script>
            <div class="form-group">
              <label for="password">Password</label>
              <div class="password-input-wrapper">
                <input type="password" id="password" class="form-input" placeholder="Create a password" required>
                <button type="button" class="password-toggle">
                  <i data-lucide="eye"></i>
                </button>
              </div>
            </div>

            <div class="form-group">
              <label for="confirmPassword">Confirm Password</label>
              <div class="password-input-wrapper">
                <input type="password" id="confirmPassword" class="form-input" placeholder="Confirm your password" required>
                <button type="button" class="password-toggle">
                  <i data-lucide="eye"></i>
                </button>
              </div>
            </div>

            <div class="form-check">
              <input type="checkbox" id="terms" required>
              <label for="terms">I agree to the <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a></label>
            </div>

            <button type="submit" class="btn btn-primary btn-full">Create Account</button>
<!--             
            <div class="auth-separator">
              <span>OR</span>
            </div>
            
            <button type="button" class="btn btn-outline btn-full google-btn">
              <img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/google/google-original.svg" alt="Google" width="18" height="18">
              Sign up with Google
            </button> -->
          </form>

          <div class="auth-footer">
            <p>Already have an account? <a href="login.html">Sign in</a></p>
          </div>
        </div>
      </div>
      
      <div class="auth-image">
        <div class="image-overlay"></div>
        <div class="auth-quote">
          <blockquote>
            "The good physician treats the disease; the great physician treats the patient who has the disease."
          </blockquote>
          <cite>— William Osler</cite>
        </div>
      </div>
    </div>
  </div>

  <script>
    // Initialize Lucide icons
    lucide.createIcons();
    
    // Password visibility toggle for both password fields
    document.querySelectorAll('.password-toggle').forEach(function(button) {
      button.addEventListener('click', function() {
        const passwordInput = this.parentElement.querySelector('input');
        const icon = this.querySelector('i');
        
        if (passwordInput.type === 'password') {
          passwordInput.type = 'text';
          icon.setAttribute('data-lucide', 'eye-off');
        } else {
          passwordInput.type = 'password';
          icon.setAttribute('data-lucide', 'eye');
        }
        
        lucide.createIcons();
      });
    });

    
  </script>
  


</body>
</html>
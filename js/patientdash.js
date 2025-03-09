// Wait for the DOM to be fully loaded
document.addEventListener('DOMContentLoaded', function() {
    // Set current year in footer
    document.getElementById('currentYear').textContent = new Date().getFullYear();
    
    // Mobile navigation toggle
    const menuToggle = document.getElementById('menuToggle');
    const mobileNav = document.getElementById('mobileNav');
    const navbar = document.getElementById('navbar');
    
    if (menuToggle && mobileNav) {
      menuToggle.addEventListener('click', function() {
        mobileNav.classList.toggle('active');
        
        // Change icon based on menu state
        if (mobileNav.classList.contains('active')) {
          menuToggle.innerHTML = '<i class="fa-solid fa-xmark"></i>';
        } else {
          menuToggle.innerHTML = '<i class="fa-solid fa-bars"></i>';
        }
      });
    }
    
    // Navbar scroll effect
    window.addEventListener('scroll', function() {
      if (window.scrollY > 10) {
        navbar.style.padding = '0.5rem 0';
        navbar.style.boxShadow = '0 2px 10px rgba(0, 0, 0, 0.1)';
      } else {
        navbar.style.padding = '1rem 0';
        navbar.style.boxShadow = '0 2px 10px rgba(0, 0, 0, 0.05)';
      }
    });
    
    // Dynamic doctor selection based on department
    const departmentSelect = document.getElementById('department');
    const doctorSelect = document.getElementById('doctor');
    
    if (departmentSelect && doctorSelect) {
      // Define the doctors by department
      const doctorsByDepartment = {
        general: [
          { id: "dr-smith", name: "Dr. John Smith" },
          { id: "dr-johnson", name: "Dr. Emily Johnson" }
        ],
        cardiology: [
          { id: "dr-wilson", name: "Dr. Robert Wilson" },
          { id: "dr-lee", name: "Dr. Jennifer Lee" }
        ],
        neurology: [
          { id: "dr-brown", name: "Dr. Michael Brown" },
          { id: "dr-davis", name: "Dr. Sarah Davis" }
        ],
        orthopedics: [
          { id: "dr-miller", name: "Dr. David Miller" },
          { id: "dr-wilson", name: "Dr. Laura Wilson" }
        ],
        pediatrics: [
          { id: "dr-thomas", name: "Dr. James Thomas" },
          { id: "dr-moore", name: "Dr. Elizabeth Moore" }
        ],
        dermatology: [
          { id: "dr-taylor", name: "Dr. Richard Taylor" },
          { id: "dr-white", name: "Dr. Patricia White" }
        ]
      };
      
      departmentSelect.addEventListener('change', function() {
        const selectedDepartment = this.value;
        
        // Clear existing options
        doctorSelect.innerHTML = '';
        
        // Add default option
        const defaultOption = document.createElement('option');
        defaultOption.value = '';
        defaultOption.disabled = true;
        defaultOption.selected = true;
        defaultOption.textContent = 'Select doctor';
        doctorSelect.appendChild(defaultOption);
        
        // Add doctors for the selected department
        if (selectedDepartment && doctorsByDepartment[selectedDepartment]) {
          doctorsByDepartment[selectedDepartment].forEach(doctor => {
            const option = document.createElement('option');
            option.value = doctor.id;
            option.textContent = doctor.name;
            doctorSelect.appendChild(option);
          });
          
          // Enable the doctor select
          doctorSelect.disabled = false;
        } else {
          // If no department selected, disable doctor select
          doctorSelect.disabled = true;
        }
      });
    }
    
    // Form validation and submission
    const appointmentForm = document.getElementById('appointmentForm');
    
    if (appointmentForm) {
      appointmentForm.addEventListener('submit', function(event) {
        event.preventDefault();
        
        // Simple validation
        let isValid = true;
        
        // Name validation
        const nameInput = document.getElementById('name');
        const nameError = document.getElementById('nameError');
        if (!nameInput.value.trim() || nameInput.value.length < 2) {
          nameError.textContent = 'Please enter a valid name (at least 2 characters)';
          isValid = false;
        } else {
          nameError.textContent = '';
        }
        
        // Email validation
        const emailInput = document.getElementById('email');
        const emailError = document.getElementById('emailError');
        const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailPattern.test(emailInput.value)) {
          emailError.textContent = 'Please enter a valid email address';
          isValid = false;
        } else {
          emailError.textContent = '';
        }
        
        // Phone validation
        const phoneInput = document.getElementById('phone');
        const phoneError = document.getElementById('phoneError');
        if (!phoneInput.value.trim() || phoneInput.value.length < 10) {
          phoneError.textContent = 'Please enter a valid phone number';
          isValid = false;
        } else {
          phoneError.textContent = '';
        }
        
        // Department validation
        const departmentInput = document.getElementById('department');
        const departmentError = document.getElementById('departmentError');
        if (!departmentInput.value) {
          departmentError.textContent = 'Please select a department';
          isValid = false;
        } else {
          departmentError.textContent = '';
        }
        
        // Doctor validation
        const doctorInput = document.getElementById('doctor');
        const doctorError = document.getElementById('doctorError');
        if (!doctorInput.value) {
          doctorError.textContent = 'Please select a doctor';
          isValid = false;
        } else {
          doctorError.textContent = '';
        }
        
        // Date validation
        const dateInput = document.getElementById('date');
        const dateError = document.getElementById('dateError');
        if (!dateInput.value) {
          dateError.textContent = 'Please select a date';
          isValid = false;
        } else {
          const selectedDate = new Date(dateInput.value);
          const today = new Date();
          today.setHours(0, 0, 0, 0);
          
          const day = selectedDate.getDay();
          const isWeekend = (day === 0 || day === 6);
          
          if (selectedDate < today) {
            dateError.textContent = 'Please select a future date';
            isValid = false;
          } else if (isWeekend) {
            dateError.textContent = 'Appointments are not available on weekends';
            isValid = false;
          } else {
            dateError.textContent = '';
          }
        }
        
        // Time validation
        const timeInput = document.getElementById('time');
        const timeError = document.getElementById('timeError');
        if (!timeInput.value) {
          timeError.textContent = 'Please select a time slot';
          isValid = false;
        } else {
          timeError.textContent = '';
        }
        
        // Reason validation
        const reasonInput = document.getElementById('reason');
        const reasonError = document.getElementById('reasonError');
        if (!reasonInput.value.trim() || reasonInput.value.length < 5) {
          reasonError.textContent = 'Please provide a reason for your visit (at least 5 characters)';
          isValid = false;
        } else {
          reasonError.textContent = '';
        }
        
        // If valid, show toast and reset form
        if (isValid) {
          // Form data
          const formData = {
            name: nameInput.value,
            email: emailInput.value,
            phone: phoneInput.value,
            department: departmentInput.options[departmentInput.selectedIndex].text,
            doctor: doctorInput.options[doctorInput.selectedIndex].text,
            date: new Date(dateInput.value).toLocaleDateString('en-US', {
              weekday: 'long',
              year: 'numeric',
              month: 'long',
              day: 'numeric'
            }),
            time: timeInput.value,
            reason: reasonInput.value
          };
          
          console.log('Appointment submitted:', formData);
          
          // Show success toast
          showToast('Success', `Your appointment with ${formData.doctor} on ${formData.date} at ${formData.time} has been booked successfully!`);
          
          // Reset form
          appointmentForm.reset();
          doctorSelect.disabled = true;
        }
      });
    }
    
    // Toast notification function
    function showToast(title, message) {
      const toast = document.getElementById('toast');
      const toastTitle = document.querySelector('.toast-title');
      const toastDescription = document.querySelector('.toast-description');
      
      toastTitle.textContent = title;
      toastDescription.textContent = message;
      
      toast.classList.add('show');
      
      // Hide toast after animation completes
      setTimeout(() => {
        toast.classList.remove('show');
      }, 5000);
    }
    
    // Set min date for appointment to today
    const dateInput = document.getElementById('date');
    if (dateInput) {
      const today = new Date();
      const year = today.getFullYear();
      let month = today.getMonth() + 1;
      let day = today.getDate();
      
      // Format to YYYY-MM-DD
      month = month < 10 ? '0' + month : month;
      day = day < 10 ? '0' + day : day;
      
      const minDate = `${year}-${month}-${day}`;
      dateInput.min = minDate;
    }
  });
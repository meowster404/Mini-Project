document.addEventListener("DOMContentLoaded", () => {
    const sign_in_btn = document.querySelector("#sign-in-btn")
    const sign_up_btn = document.querySelector("#sign-up-btn")
    const container = document.querySelector(".container")
    const farmerFields = document.querySelector(".farmer-fields")
    const signupUserTypeRadios = document.querySelectorAll('input[name="userType"]')
  
    sign_up_btn.addEventListener("click", () => {
      container.classList.add("sign-up-mode")
    })
  
    sign_in_btn.addEventListener("click", () => {
      container.classList.remove("sign-up-mode")
    })
  
    // Show/hide farmer-specific fields based on user type selection
    signupUserTypeRadios.forEach((radio) => {
      radio.addEventListener("change", function () {
        if (this.value === "farmer") {
          farmerFields.style.display = "block"
          const farmerInputs = farmerFields.querySelectorAll("input")
          farmerInputs.forEach((input) => (input.required = true))
        } else {
          farmerFields.style.display = "none"
          const farmerInputs = farmerFields.querySelectorAll("input")
          farmerInputs.forEach((input) => (input.required = false))
        }
      })
    })
  
    // Handle login form submission
    function showAlert(message, type = 'success') {
        const existingContainer = document.querySelector('.custom-alert-container');
        if (existingContainer) {
            existingContainer.remove();
        }

        const container = document.createElement('div');
        container.className = 'custom-alert-container';

        const alert = document.createElement('div');
        alert.className = `custom-alert ${type}`;

        const messageDiv = document.createElement('div');
        messageDiv.className = 'message';
        messageDiv.textContent = message;

        const closeBtn = document.createElement('span');
        closeBtn.className = 'close-btn';
        closeBtn.innerHTML = '×';
        closeBtn.onclick = () => {
            container.classList.remove('show');
            setTimeout(() => container.remove(), 300);
        };

        alert.appendChild(messageDiv);
        alert.appendChild(closeBtn);
        container.appendChild(alert);
        document.body.appendChild(container);

        setTimeout(() => container.classList.add('show'), 10);

        // Auto-hide after 3 seconds for success messages
        if (type === 'success') {
            setTimeout(() => {
                container.classList.remove('show');
                setTimeout(() => container.remove(), 300);
            }, 3000);
        }
    }

    // Update login form handler
    document.getElementById("loginForm").addEventListener("submit", async (e) => {
        e.preventDefault();
        const formData = new FormData(e.target);
        const data = {
            email: formData.get("email"),
            password: formData.get("password"),
            userType: formData.get("userType"),
        };
    
        try {
            const response = await fetch("/Mini-Project-Final/Backend/php/auth/process_login.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                },
                body: JSON.stringify(data),
            });
    
            const result = await response.json();
    
            if (result.success) {
                localStorage.setItem("user", JSON.stringify(result.user));
                localStorage.setItem("token", result.token);
                
                showAlert(`Welcome back, ${result.user.name}!`, 'success');
                
                // Redirect after alert
                setTimeout(() => {
                    window.location.href = result.user.type === "consumer" 
                        ? "/Mini-Project-Final/Frontend/Html/products.html"
                        : "/Mini-Project-Final/Backend/php/farmer-dashboard.php";
                }, 1500);
            } else {
                showAlert(result.message || "Login failed. Please try again.", 'error');
            }
        } catch (error) {
            console.error("Login error:", error);
            showAlert("An error occurred during login. Please try again.", 'error');
        }
    });
    
    // Update signup form handler
    document.getElementById("signupForm").addEventListener("submit", async (e) => {
        e.preventDefault();
        const formData = new FormData(e.target);
        const data = {
            fullName: formData.get("fullName"),
            email: formData.get("email"),
            phone: formData.get("phone"),
            password: formData.get("password"),
            userType: formData.get("userType"),
        };
    
        if (data.userType === "farmer") {
            data.farmName = formData.get("farmName");
            data.farmAddress = formData.get("farmAddress");
        }
    
        try {
            const response = await fetch("/Mini-Project-Final/Backend/php/auth/process_signup.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                },
                body: JSON.stringify(data),
            });
    
            const result = await response.json();
    
            if (result.success) {
                showAlert("Registration successful! Please log in.", 'success');
                setTimeout(() => {
                    container.classList.remove("sign-up-mode");
                    // Clear form
                    e.target.reset();
                }, 1500);
            } else {
                showAlert(result.message || "Registration failed. Please try again.", 'error');
            }
        } catch (error) {
            console.error("Registration error:", error);
            showAlert("An error occurred during registration. Please try again.", 'error');
        }
    });
  })
  
  // Add this function at the start of your file
  async function checkSession() {
      try {
          const response = await fetch('/Mini-Project-Final/Backend/php/auth/check_session.php', {
              method: 'POST',
              credentials: 'include'
          });
          const result = await response.json();
          
          if (!result.valid) {
              // Clear local storage
              localStorage.removeItem('user');
              localStorage.removeItem('token');
              
              // Redirect to login page if not already there
              if (!window.location.pathname.includes('auth.html')) {
                  window.location.href = '/Mini-Project-Final/Frontend/Html/auth.html';
              }
          }
      } catch (error) {
          console.error('Session check failed:', error);
      }
  }
  
  // Check session every minute
  setInterval(checkSession, 60000);
  
  // Check session on page load
  document.addEventListener('DOMContentLoaded', checkSession);
  
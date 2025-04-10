document.addEventListener("DOMContentLoaded", () => {
    const sign_in_btn = document.querySelector("#sign-in-btn");
    const sign_up_btn = document.querySelector("#sign-up-btn");
    const container = document.querySelector(".container");
    const farmerFields = document.querySelector(".farmer-fields");
    const signupUserTypeRadios = document.querySelectorAll('input[name="userType"]');

    // Toggle between sign-in and sign-up modes
    sign_up_btn.addEventListener("click", () => {
        container.classList.add("sign-up-mode");
    });

    sign_in_btn.addEventListener("click", () => {
        container.classList.remove("sign-up-mode");
    });

    // Show/hide farmer fields
    signupUserTypeRadios.forEach((radio) => {
        radio.addEventListener("change", function () {
            if (this.value === "farmer") {
                farmerFields.style.display = "block";
                const farmerInputs = farmerFields.querySelectorAll("input");
                farmerInputs.forEach((input) => (input.required = true));
            } else {
                farmerFields.style.display = "none";
                const farmerInputs = farmerFields.querySelectorAll("input");
                farmerInputs.forEach((input) => (input.required = false));
            }
        });
    });

    // Handle login form submission
    document.getElementById("loginForm").addEventListener("submit", async (e) => {
        e.preventDefault();
        const formData = new FormData(e.target);
        const userType = formData.get("userType");
        const email = formData.get("email");
        const password = formData.get("password");
    
        try {
            const response = await fetch("/Mini-Project-Final/Backend/php/auth/process_login.php", {
                method: "POST",
                body: new URLSearchParams({
                    'userType': userType,
                    'email': email,
                    'password': password
                })
            });
    
            const text = await response.text();
            console.log('Server response:', text); // For debugging
    
            if (text.includes('success')) {
                showAlert('Login successful!', 'success');
                
                setTimeout(() => {
                    if (userType === "farmer") {
                        window.location.href = "/Mini-Project-Final/Backend/php/farmer-dashboard.php";
                    } else {
                        window.location.href = "/Mini-Project-Final/Frontend/Html/products.html";
                    }
                }, 1500);
            } else {
                showAlert("Invalid email or password", 'error');
            }
        } catch (error) {
            console.error("Login error:", error);
            showAlert("Login failed. Please try again.", 'error');
        }
    });

    // Handle signup form submission
    document.getElementById("signupForm").addEventListener("submit", async (e) => {
        e.preventDefault();
        const formData = new FormData(e.target);
        
        // Convert FormData to JSON object
        const jsonData = {};
        formData.forEach((value, key) => {
            jsonData[key] = value;
        });

        try {
            const response = await fetch("/Mini-Project-Final/Backend/php/auth/process_signup.php", {
                method: "POST",
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(jsonData)
            });
    
            const result = await response.text();
            const data = result ? JSON.parse(result) : {};
    
            if (data.success) {
                showAlert("Registration successful! Please log in.", 'success');
                setTimeout(() => {
                    container.classList.remove("sign-up-mode");
                    e.target.reset();
                }, 1500);
            } else {
                showAlert(data.message || "Registration failed. Please try again.", 'error');
            }
        } catch (error) {
            console.error("Registration error:", error);
            showAlert("An error occurred during registration. Please try again.", 'error');
        }
    });
});

// Custom Alert Function
function showAlert(message, type = "success") {
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

    if (type === 'success') {
        setTimeout(() => {
            container.classList.remove('show');
            setTimeout(() => container.remove(), 300);
        }, 3000);
    }
}
  
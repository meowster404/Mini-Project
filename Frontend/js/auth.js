document.addEventListener('DOMContentLoaded', function() {
    const sign_in_btn = document.querySelector("#sign-in-btn");
    const sign_up_btn = document.querySelector("#sign-up-btn");
    const container = document.querySelector(".container");
    const farmerFields = document.querySelector('.farmer-fields');
    const signupUserTypeRadios = document.querySelectorAll('input[name="userType"]');

    sign_up_btn.addEventListener("click", () => {
        container.classList.add("sign-up-mode");
    });

    sign_in_btn.addEventListener("click", () => {
        container.classList.remove("sign-up-mode");
    });

    // Show/hide farmer-specific fields based on user type selection
    signupUserTypeRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.value === 'farmer') {
                farmerFields.style.display = 'block';
                const farmerInputs = farmerFields.querySelectorAll('input');
                farmerInputs.forEach(input => input.required = true);
            } else {
                farmerFields.style.display = 'none';
                const farmerInputs = farmerFields.querySelectorAll('input');
                farmerInputs.forEach(input => input.required = false);
            }
        });
    });

    // Handle login form submission
    document.getElementById('loginForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(e.target);
        const data = {
            email: formData.get('email'),
            password: formData.get('password'),
            userType: formData.get('userType')
        };

        try {
            const response = await fetch('/api/auth/login', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            });

            if (response.ok) {
                const result = await response.json();
                localStorage.setItem('user', JSON.stringify(result.user));
                localStorage.setItem('token', result.token);
                
                // Redirect based on user type
                if (result.user.type === 'consumer') {
                    window.location.href = 'products.html';
                } else {
                    window.location.href = 'farmer-dashboard.html';
                }
            } else {
                alert('Login failed. Please check your credentials.');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('An error occurred during login.');
        }
    });

    // Handle signup form submission
    document.getElementById('signupForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(e.target);
        const data = {
            fullName: formData.get('fullName'),
            email: formData.get('email'),
            phone: formData.get('phone'),
            password: formData.get('password'),
            userType: formData.get('userType')
        };

        if (data.userType === 'farmer') {
            data.farmName = formData.get('farmName');
            data.farmAddress = formData.get('farmAddress');
        }

        try {
            const response = await fetch('/api/auth/register', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            });

            if (response.ok) {
                const result = await response.json();
                alert('Registration successful! Please login.');
                container.classList.remove("sign-up-mode");
            } else {
                alert('Registration failed. Please try again.');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('An error occurred during registration.');
        }
    });
});
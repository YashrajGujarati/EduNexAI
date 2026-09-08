// Global function for Role Chip Selection
function selectRole(roleName) {
    const roleChips = document.querySelectorAll(".role-chip");

    roleChips.forEach(chip => {
        const radio = chip.querySelector('input[type="radio"]');
        if (chip.getAttribute("data-role") === roleName) {
            chip.classList.add("active");
            if (radio) radio.checked = true;
        } else {
            chip.classList.remove("active");
            if (radio) radio.checked = false;
        }
    });
}

function initRegisterApp() {
    const passwordInput = document.getElementById("password");
    const confirmPasswordInput = document.getElementById("confirm_password");
    const togglePasswordBtn = document.getElementById("togglePassword");
    const toggleConfirmPasswordBtn = document.getElementById("toggleConfirmPassword");
    const registerForm = document.getElementById("registerForm");

    const pwStrengthWrapper = document.getElementById("pwStrengthWrapper");
    const pwStrengthFill = document.getElementById("pwStrengthFill");
    const pwStrengthText = document.getElementById("pwStrengthText");
    const pwMatchFeedback = document.getElementById("pwMatchFeedback");

    const roleChips = document.querySelectorAll(".role-chip");

    // Event listener for Role Selector Chips
    if (roleChips.length > 0) {
        roleChips.forEach(chip => {
            chip.addEventListener("click", function () {
                const chosenRole = this.getAttribute("data-role");
                if (chosenRole) {
                    selectRole(chosenRole);
                }
            });
        });
    }

    // Toggle Password Visibility
    if (togglePasswordBtn && passwordInput) {
        togglePasswordBtn.addEventListener("click", function (e) {
            e.preventDefault();
            const isPassword = passwordInput.type === "password";
            passwordInput.type = isPassword ? "text" : "password";
            this.innerHTML = isPassword ? '<i class="fa-solid fa-eye-slash"></i>' : '<i class="fa-solid fa-eye"></i>';
        });
    }

    // Toggle Confirm Password Visibility
    if (toggleConfirmPasswordBtn && confirmPasswordInput) {
        toggleConfirmPasswordBtn.addEventListener("click", function (e) {
            e.preventDefault();
            const isPassword = confirmPasswordInput.type === "password";
            confirmPasswordInput.type = isPassword ? "text" : "password";
            this.innerHTML = isPassword ? '<i class="fa-solid fa-eye-slash"></i>' : '<i class="fa-solid fa-eye"></i>';
        });
    }

    // Evaluate Password Strength
    if (passwordInput && pwStrengthFill && pwStrengthText) {
        passwordInput.addEventListener("input", function () {
            const val = this.value;

            if (val.length === 0) {
                pwStrengthWrapper.style.display = "none";
                return;
            }

            pwStrengthWrapper.style.display = "block";
            let score = 0;

            if (val.length >= 6) score += 25;
            if (val.length >= 10) score += 25;
            if (/[A-Z]/.test(val)) score += 20;
            if (/[0-9]/.test(val)) score += 15;
            if (/[^A-Za-z0-9]/.test(val)) score += 15;

            pwStrengthFill.style.width = score + "%";

            if (score <= 30) {
                pwStrengthFill.style.backgroundColor = "#ef4444";
                pwStrengthText.style.color = "#ef4444";
                pwStrengthText.textContent = "Weak Password";
            } else if (score <= 70) {
                pwStrengthFill.style.backgroundColor = "#f59e0b";
                pwStrengthText.style.color = "#f59e0b";
                pwStrengthText.textContent = "Medium Password";
            } else {
                pwStrengthFill.style.backgroundColor = "#10b981";
                pwStrengthText.style.color = "#10b981";
                pwStrengthText.textContent = "Strong Password";
            }

            checkPasswordMatch();
        });
    }

    // Check Password Match Real-Time
    if (confirmPasswordInput) {
        confirmPasswordInput.addEventListener("input", checkPasswordMatch);
    }

    function checkPasswordMatch() {
        if (!confirmPasswordInput || !pwMatchFeedback) return;
        const pw = passwordInput ? passwordInput.value : "";
        const confirmPw = confirmPasswordInput.value;

        if (confirmPw.length === 0) {
            pwMatchFeedback.style.display = "none";
            return;
        }

        pwMatchFeedback.style.display = "block";
        if (pw === confirmPw) {
            pwMatchFeedback.style.color = "#10b981";
            pwMatchFeedback.innerHTML = '<i class="fa-solid fa-circle-check me-1"></i> Passwords match';
        } else {
            pwMatchFeedback.style.color = "#ef4444";
            pwMatchFeedback.innerHTML = '<i class="fa-solid fa-circle-xmark me-1"></i> Passwords do not match';
        }
    }

    // Form Submission Validation
    if (registerForm) {
        registerForm.addEventListener("submit", function (e) {
            const pw = passwordInput ? passwordInput.value : "";
            const confirmPw = confirmPasswordInput ? confirmPasswordInput.value : "";

            if (pw.length < 6) {
                e.preventDefault();
                alert("Password must be at least 6 characters long.");
                passwordInput.focus();
                return;
            }

            if (pw !== confirmPw) {
                e.preventDefault();
                alert("Passwords do not match. Please verify.");
                confirmPasswordInput.focus();
                return;
            }
        });
    }

    console.log("EduNexAI Split-Screen Registration System Ready");
}

// Execute immediately if DOM is already ready, or listen to DOMContentLoaded
if (document.readyState === "interactive" || document.readyState === "complete") {
    initRegisterApp();
} else {
    document.addEventListener("DOMContentLoaded", initRegisterApp);
}



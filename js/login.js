
function initLoginApp() {
    const togglePasswordBtn = document.getElementById("togglePassword");
    const passwordInput = document.getElementById("password");

    if (togglePasswordBtn && passwordInput) {
        togglePasswordBtn.addEventListener("click", function (e) {
            e.preventDefault();
            const isPassword = passwordInput.type === "password";
            passwordInput.type = isPassword ? "text" : "password";
            this.innerHTML = isPassword ? '<i class="fa-solid fa-eye-slash"></i>' : '<i class="fa-solid fa-eye"></i>';
        });
    }

    console.log("EduNexAI Split-Screen Login System Ready");
}

if (document.readyState === "interactive" || document.readyState === "complete") {
    initLoginApp();
} else {
    document.addEventListener("DOMContentLoaded", initLoginApp);
}
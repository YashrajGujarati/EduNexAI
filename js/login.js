
const togglePassword = document.getElementById("togglePassword");
const password = document.getElementById("password");

togglePassword.addEventListener("click", function () {
    if (password.type === "password") {
        password.type = "text";
        this.innerHTML = '<i class="fa-solid fa-eye-slash"></i>';
    }
    else {
        password.type = "password";
        this.innerHTML = '<i class="fa-solid fa-eye"></i>';
    }
});
window.onload = function () {

    console.log("EduNexAI Login Page Loaded Successfully");

};
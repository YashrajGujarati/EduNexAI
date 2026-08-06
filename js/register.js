const password = document.querySelector('input[name="password"]');
const confirmPassword = document.querySelector('input[name="confirm_password"]');
const registerForm = document.querySelector("form");
registerForm.addEventListener("submit", function (event) {

    if (password.value !== confirmPassword.value) {
        alert("Password and Confirm Password do not match.");
        event.preventDefault();
        return;
    }
    if (password.value.length < 6) {
        alert("Password must be at least 6 characters.");
          event.preventDefault();
        return;
    }
});
window.onload = function () {
    console.log("EduNexAI Register Page Loaded Successfully");
};
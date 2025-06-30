document.addEventListener("DOMContentLoaded", function () {
  let wrapper = document.querySelector(".wrapper"),
    signUpLinks = document.querySelectorAll(".signup-link"), // Select all signup links
    signInLinks = document.querySelectorAll(".signin-link"), // Select all signin links
    forgotPassLink = document.querySelector(".forgot-link"),
    signUpForm = document.querySelector(".form-container.sign-up"),
    signInForm = document.querySelector(".form-container.sign-in"),
    forgotPassForm = document.querySelector(".form-container.forgot-password"),
    resetPasswordForm = document.querySelector(".forgot-password form");

  // Ensure all elements exist before manipulating them
  if (signInForm) signInForm.style.display = "flex";
  if (signUpForm) signUpForm.style.display = "none";
  if (forgotPassForm) forgotPassForm.style.display = "none";

  // Switch to Sign Up Form
  signUpLinks.forEach((link) => {
    link.addEventListener("click", (e) => {
      e.preventDefault();
      if (signUpForm) signUpForm.style.display = "flex";
      if (signInForm) signInForm.style.display = "none";
      if (forgotPassForm) forgotPassForm.style.display = "none";
    });
  });

  // Switch to Sign In Form
  signInLinks.forEach((link) => {
    link.addEventListener("click", (e) => {
      e.preventDefault();
      if (signInForm) signInForm.style.display = "flex";
      if (signUpForm) signUpForm.style.display = "none";
      if (forgotPassForm) forgotPassForm.style.display = "none";
    });
  });

  // Switch to Forgot Password Form (Check if forgotPassLink exists)
  if (forgotPassLink) {
    forgotPassLink.addEventListener("click", (e) => {
      e.preventDefault();
      if (forgotPassForm) forgotPassForm.style.display = "flex";
      if (signInForm) signInForm.style.display = "none";
      if (signUpForm) signUpForm.style.display = "none";
    });
  }

  // Handle Reset Password Form Submission (Check if resetPasswordForm exists)
  if (resetPasswordForm) {
    resetPasswordForm.addEventListener("submit", function (e) {
      let newPassword = resetPasswordForm
        .querySelector("input[name='new_password']")
        .value.trim();
      let confirmPassword = resetPasswordForm
        .querySelector("input[name='confirm_password']")
        .value.trim();

      console.log("New Password:", newPassword);
      console.log("Confirm Password:", confirmPassword);

      if (newPassword !== confirmPassword) {
        alert("Passwords do not match! Please try again.");
        e.preventDefault(); // Stop form submission if passwords don't match
      }
    });
  }
});

function login() {

    // Get the login form
    const loginForm = document.querySelector("#loginForm");

    // Get the input fields
    const email = document.querySelector("#email");
    const password = document.querySelector("#password");

    // Get the error messages
    const emailError = document.querySelector("#emailError");
    const passwordError = document.querySelector("#passwordError");

    // Listen for form submission
    loginForm.addEventListener("submit", function (event) {

        // Prevent the page from refreshing
        event.preventDefault();

        // Reset error messages
        emailError.textContent = "";
        passwordError.textContent = "";

        let isValid = true;

        // Check email
        if (email.value.trim() === "") {
            emailError.textContent = "Veuillez saisir votre e-mail.";
            isValid = false;
        }
        else if (!email.validity.valid) {
            emailError.textContent = "Veuillez saisir un e-mail valide.";
            isValid = false;
        }

        // Check password
        if (password.value.trim() === "") {
            passwordError.textContent = "Veuillez saisir votre mot de passe.";
            isValid = false;
        }

        
    });


}






    // Listen for form submission
    loginForm.addEventListener("submit", function (event) {

        // Prevent the page from refreshing
        event.preventDefault();

        // Get the login form
        const loginForm = document.querySelector("#loginForm");

        // Get the input fields
        const email = document.querySelector("#email");
        const password = document.querySelector("#password");

        // Get the error messages
        const emailError = document.querySelector("#emailError");
        const passwordError = document.querySelector("#passwordError");

        // Reset error messages
        emailError.textContent = "";
        passwordError.textContent = "";

        // Check email
        if (email.value.trim() === "") {
            emailError.textContent = "Veuillez saisir votre e-mail.";
            return;
        }
        else if (email.value.length>80){
            emailError.textContent = "votre email est trop long."; 
            return;
        }
        else if (!email.validity.valid) {
            emailError.textContent = "Veuillez saisir un e-mail valide.";
            return;
        }

        // Check password
        if (password.value.trim() =="") {
            passwordError.textContent = "Veuillez saisir votre mot de passe.";
            return;
        }

        loginForm.submit();
        
    });





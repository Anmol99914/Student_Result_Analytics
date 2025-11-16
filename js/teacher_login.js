document.forms['teacher_form'].addEventListener("submit", teacherLogin);
function teacherLogin(event) {
    event.preventDefault(); // Stop form from submitting automatically

    //Get input values
    const email = document.forms['teacher_form']['email'].value.trim();
    const password = document.forms['teacher_form']['password'].value.trim();

    //Get error Elements
    const emailError = document.getElementById("email_Error");
    const passwordError = document.getElementById("pass_Error");

    //Define validation
    // const rollRegex = /^[100-115]$/;
    const emailRegex = /^[A-Za-z0-9._+-%]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$/;

    let isValid = true;

    //Validate roll_id
    if (email === "") {
        emailError.textContent = "Email address cannot be empty!!";
        isValid = false;
    }
    else if (!emailRegex.test(email)) {
        emailError.textContent = "Enter a valid email e.g. kalpana@123gmail.com";
        isValid = false;
    }
    else {
        emailError.textContent = "";
    }

    //Validate semsester
    if (password === "") {
        passError.textContent = "Password field cannot be empty!1";
        isValid = false;
    }
    else if(password.length <6 || password.length > 15){
        passwordError.textContent = "Password must be between 6 and 15 characters";
        isValid = false;
    }
    else {
        passwordError.textContent = "";
    }

    // If both are valid, submit to PHP for login verification
    if (isValid) {
        document.forms['teacher_form'].submit();
    }


}
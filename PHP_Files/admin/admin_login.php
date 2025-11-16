<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <title>Admin Login</title>
    <style>
        .error {
            color: red;
        }
    </style>
</head>

<body class="bg-light d-flex flex-column align-items-center justify-content-center vh-100">

    <a href="../../index.html" class="btn btn-danger position-absolute" style="top: 20px; left: 20px; z-index: 10;">
        <i class="bi bi-house-heart"></i> Back To Home
    </a>

    <!-- Page heading -->
    <h1 class="text-center mb-4">Student Result Analytics</h1>

    <!-- Login form -->
    <div class="col-md-4">
        <div class="card shadow-sm p-4">
            <h3 class="text-center mb-4">Admin Login</h3>
            <form action="admin_validation.php" method="POST" name="admin_form">
                <?php
                    $error_msg = '';
                    if(isset($_GET['error'])){
                        if($_GET['error'] === "invalid"){
                            $error_msg = "Invalid username or password!!";
                        }
                    }
                ?>

                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" class="form-control" id="username" name="username" placeholder="UserName"
                        required autocomplete = "username">
                    <p id="usernameError" class="error"></p>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" placeholder="Password"
                        required autocomplete="current-password">
                    <p id="passwordError" class="error">
                        <?php echo $error_msg; ?> 
                    </p>
                </div>
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" value="remember-me" id="remember" name="remember">
                    <label class="form-check-label" for="remember">Remember me</label>
                </div>
                <button type="submit" class="btn btn-primary w-100 py-2">Sign In</button>
            </form>

        </div>
    </div>
    <script src="js/admin_login.js"></script>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
        crossorigin="anonymous"></script>
    <script>
        window.addEventListener('pageshow', function (event) {
            if (event.persisted) {
                window.location.reload();
            }
        });

        // document.addEventListener('DOMContentLoaded', function () {
        //     const form = document.forms['admin_form'];
        //     const usernameInput = document.getElementById('username');
        //     const passwordInput = document.getElementById('password');
        //     const rememberCheckbox = document.getElementById('remember');

        //     // Restore saved values
        //     if (localStorage.getItem('remember') === 'true') {
        //         usernameInput.value = localStorage.getItem('saved_username') || '';
        //         passwordInput.value = localStorage.getItem('saved_password') || '';
        //         rememberCheckbox.checked = true;
        //     }

        //     // Save on submit
        //     form.addEventListener("submit", function () {
        //         if (rememberCheckbox.checked) {
        //             localStorage.setItem("remember", "true");
        //             localStorage.setItem("saved_username", usernameInput.value);
        //             localStorage.setItem("saved_password", passwordInput.value);
        //         } else {
        //             localStorage.removeItem("remember");
        //             localStorage.removeItem("saved_username");
        //             localStorage.removeItem("saved_password");
        //         }
        //     });
        // });

    </script>

</body>

</html>
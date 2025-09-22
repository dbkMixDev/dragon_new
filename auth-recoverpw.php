<?php
session_start();

?>
<!doctype html>
<html lang="en">

    <head>
        
        <meta charset="utf-8" />
        <title>Recover Password | Dragon Play - Admin Panel</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta content="Premium Multipurpose Admin & Dashboard Template" name="description" />
        <meta content="Themesbrand" name="author" />
        <!-- App favicon -->
        <link rel="shortcut icon" href="assets/images/favicon.ico">

        <!-- Bootstrap Css -->
        <link href="assets/css/bootstrap.min.css" id="bootstrap-style" rel="stylesheet" type="text/css" />
        <!-- Icons Css -->
        <link href="assets/css/icons.min.css" rel="stylesheet" type="text/css" />
        <!-- App Css-->
        <link href="assets/css/app.min.css" id="app-style" rel="stylesheet" type="text/css" />

    </head>

    <body>
        <div class="account-pages my-5 pt-sm-5">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-md-8 col-lg-6 col-xl-5">
                        <div class="card overflow-hidden">
                            <div class="bg-primary bg-soft">
                                <div class="row">
                                    <div class="col-7">
                                        <div class="text-primary p-4">
                                            <h5 class="text-primary"> Reset Password</h5>
                                            <p>Reset your account.</p>
                                        </div>
                                    </div>
                                    <div class="col-5 align-self-end">
                                        <img src="assets/images/profile-img.png" alt="" class="img-fluid">
                                    </div>
                                </div>
                            </div>
                            <div class="card-body pt-0"> 
                                <div>
                                    <a href="index.php">
                                        <div class="avatar-md profile-user-wid mb-4">
                                            <span class="avatar-title rounded-circle bg-light">
                                                <img src="assets/images/logo.svg" alt="" class="rounded-circle" height="34">
                                            </span>
                                        </div>
                                    </a>
                                </div>
                                
                                <div class="p-2">
                                   <?php if (isset($_SESSION['error2'])): ?>
    <div class="alert alert-danger text-center mb-4" role="alert">
        <?= htmlspecialchars($_SESSION['error2']) ?>
    </div>
    <?php unset($_SESSION['error2']); ?>
<?php else: ?>
    <div class="alert alert-success text-center mb-4" role="alert">
        Enter your Email and instructions will be sent to you!
    </div>
<?php endif; ?>

                                  <form class="form-horizontal" action="controller/gettoken.php" method="POST">
    <div class="mb-3">
        <label for="useremail" class="form-label">Email</label>
        <input type="email" class="form-control" id="useremail" name="email" placeholder="Enter email" required>
    </div>

   <div class="text-end">
    <button class="btn btn-primary w-md waves-effect waves-light d-flex align-items-center justify-content-center gap-2" type="submit" id="resetBtn">
        <span class="spinner-border spinner-border-sm me-2 d-none" id="spinner"></span>
        <span id="btnText">Reset</span>
    </button>
</div>

</form>

                                </div>
            
                            </div>
                        </div>
                        <div class="mt-5 text-center">
                            <p>Remember It ? <a href="login.php" class="fw-medium text-primary"> Sign In here</a> </p>
                            <p>© <script>document.write(new Date().getFullYear())</script> Dragon Play. Best Project <i class="mdi mdi-heart text-danger"></i> by dbk.dev</p>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <!-- JAVASCRIPT -->
        <script src="assets/libs/jquery/jquery.min.js"></script>
        <script src="assets/libs/bootstrap/js/bootstrap.bundle.min.js"></script>
        <script src="assets/libs/metismenu/metisMenu.min.js"></script>
        <script src="assets/libs/simplebar/simplebar.min.js"></script>
        <script src="assets/libs/node-waves/waves.min.js"></script>
        
        <!-- App js -->
        <script src="assets/js/app.js"></script>
    </body>
   <script>
    const resendDelay = 5 * 60 * 1000; // 5 menit dalam milidetik
    const resetButton = document.getElementById("resetBtn");
    const btnText = document.getElementById("btnText");
    const spinner = document.getElementById("spinner");

    function updateButtonState() {
        const lastSent = parseInt(localStorage.getItem("lastResetTime") || "0");
        const now = Date.now();

        if (lastSent && now - lastSent < resendDelay) {
            const remaining = Math.ceil((resendDelay - (now - lastSent)) / 1000);
            resetButton.disabled = true;
            btnText.innerText = "Wait " + remaining + "s";
            spinner.classList.add("d-none");

            const interval = setInterval(() => {
                const left = Math.ceil((resendDelay - (Date.now() - lastSent)) / 1000);
                if (left <= 0) {
                    clearInterval(interval);
                    resetButton.disabled = false;
                    btnText.innerText = "Reset";
                } else {
                    btnText.innerText = "Wait " + left + "s";
                }
            }, 1000);
        } else {
            resetButton.disabled = false;
            btnText.innerText = "Reset";
            spinner.classList.add("d-none");
        }
    }

    document.querySelector("form").addEventListener("submit", function () {
        localStorage.setItem("lastResetTime", Date.now());

        // Disable button & show spinner
        resetButton.disabled = true;
        spinner.classList.remove("d-none");
        btnText.innerText = "Sending...";
    });

    window.addEventListener("load", updateButtonState);
    window.addEventListener("pageshow", updateButtonState);
</script>


</html>

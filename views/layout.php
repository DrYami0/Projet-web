<?php
// Layout based on account-profile.html. It expects a $content variable containing
// the specific page content (events list, create form, etc.).
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Events - Dashboard</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="shortcut icon" href="../assets/images/favicon.ico">
    <link rel="preconnect" href="https://fonts.googleapis.com/">
    <link rel="preconnect" href="https://fonts.gstatic.com/" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&family=Poppins:wght@400;500;700&display=swap">
    <link rel="stylesheet" type="text/css" href="../assets/vendor/font-awesome/css/all.min.css">
    <link rel="stylesheet" type="text/css" href="../assets/vendor/bootstrap-icons/bootstrap-icons.css">
    <link rel="stylesheet" type="text/css" href="../assets/vendor/aos/aos.css">
    <link rel="stylesheet" type="text/css" href="../assets/vendor/flatpickr/css/flatpickr.min.css">
    <link rel="stylesheet" type="text/css" href="../assets/vendor/choices/css/choices.min.css">
    <link rel="stylesheet" type="text/css" href="../assets/css/style.css">
</head>
<body class="dashboard">

<?php include __DIR__ . '/partials/header_and_sidebar.php'; ?>

<main>
    <section class="pt-3">
        <div class="container">
            <div class="row">
                <!-- Sidebar column is already rendered in header_and_sidebar.php -->
                <div class="col-lg-8 col-xl-9">
                    <div class="vstack gap-4">
                        <?php echo $content ?? ''; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<footer class="bg-dark p-3">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-4">
                <div class="text-center text-md-start mb-3 mb-md-0">
                    <a href="#"> <img class="h-30px" src="../assets/images/logo-light.svg" alt="logo"> </a>
                </div>
            </div>
        </div>
    </div>
</footer>

<script src="../assets/vendor/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/vendor/aos/aos.js"></script>
<script src="../assets/vendor/flatpickr/js/flatpickr.min.js"></script>
<script src="../assets/vendor/choices/js/choices.min.js"></script>
<script src="../assets/js/functions.js"></script>
</body>
</html>

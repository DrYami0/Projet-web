<?php
// Extracted header + sidebar from account-profile.html, slightly simplified.
?>
<header class="navbar-light header-sticky">
    <nav class="navbar navbar-expand-xl">
        <div class="container">
            <a class="navbar-brand" href="#">
                <img class="light-mode-item navbar-brand-item" src="../assets/images/logo.svg" alt="logo">
                <img class="dark-mode-item navbar-brand-item" src="../assets/images/logo-light.svg" alt="logo">
            </a>
            <button class="navbar-toggler ms-auto mx-3 me-md-0 p-0 p-sm-2" type="button" data-bs-toggle="collapse" data-bs-target="#navbarCollapse" aria-controls="navbarCollapse" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-animation">
                    <span></span>
                    <span></span>
                    <span></span>
                </span>
            </button>
            <div class="navbar-collapse collapse" id="navbarCollapse">
                <ul class="navbar-nav navbar-nav-scroll">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php?controller=events&action=index">Events</a>
                    </li>
                </ul>
            </div>
            <ul class="nav flex-row align-items-center list-unstyled ms-xl-auto">
                <li class="nav-item ms-3 dropdown">
                    <a class="avatar avatar-xs p-0" href="#" id="profileDropdown" role="button" data-bs-auto-close="outside" data-bs-display="static" data-bs-toggle="dropdown" aria-expanded="false">
                        <img class="avatar-img rounded-circle" src="../assets/images/avatar/01.jpg" alt="avatar">
                    </a>
                    <ul class="dropdown-menu dropdown-animation dropdown-menu-end shadow pt-3" aria-labelledby="profileDropdown">
                        <li class="px-3 mb-3">
                            <div class="d-flex align-items-center">
                                <div class="avatar me-3">
                                    <img class="avatar-img rounded-circle shadow" src="../assets/images/avatar/01.jpg" alt="avatar">
                                </div>
                                <div>
                                    <span class="h6 mt-2 mt-sm-0">User</span>
                                    <p class="small m-0">user@example.com</p>
                                </div>
                            </div>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </nav>
</header>

<div class="container mt-3">
    <div class="row">
        <div class="col-lg-4 col-xl-3">
            <div class="card bg-light w-100">
                <div class="card-body p-3">
                    <div class="text-center mb-3">
                        <div class="avatar avatar-xl mb-2">
                            <img class="avatar-img rounded-circle border border-2 border-white" src="../assets/images/avatar/01.jpg" alt="">
                        </div>
                        <h6 class="mb-0">User</h6>
                        <span class="text-reset text-primary-hover small">user@example.com</span>
                        <hr>
                    </div>
                    <ul class="nav nav-pills-primary-soft flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" href="index.php?controller=events&action=index"><i class="bi bi-calendar-event fa-fw me-2"></i>Events</a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

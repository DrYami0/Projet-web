<!--================Header Menu Area =================-->
<header class="header_area">
    <div class="top_menu row m0">
        <div class="container">
            <div class="float-left">
                <ul class="list header_social">
                    <li><a href="#"><i class="fa fa-facebook"></i></a></li>
                    <li><a href="#"><i class="fa fa-twitter"></i></a></li>
                    <li><a href="#"><i class="fa fa-dribbble"></i></a></li>
                    <li><a href="#"><i class="fa fa-behance"></i></a></li>
                </ul>
            </div>
            <div class="float-right">
                <a class="dn_btn" href="tel:+21612345678">+216 12 345 678</a>
                <a class="dn_btn" href="mailto:support@PrismStudio.com">support@PrismStudio.com</a>
            </div>
        </div>	
    </div>	
    <div class="main_menu">	
        <nav class="navbar navbar-expand-lg navbar-light">
            <div class="container" style="padding-left: 0;">
                <!-- Brand and toggle get grouped for better mobile display -->
                <a class="navbar-brand logo_h" href="<?php echo BASE_URL; ?>" style="margin-left: 0; padding-left: 0;"><img src="<?php echo BASE_URL; ?>assets/img/logo.png" alt=""></a>
                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <!-- Collect the nav links, forms, and other content for toggling -->
                <div class="collapse navbar-collapse offset" id="navbarSupportedContent">
                    <ul class="nav navbar-nav menu_nav ml-auto">
                        <li class="nav-item <?php echo (isset($page) && $page === 'home') ? 'active' : ''; ?>"><a class="nav-link" href="<?php echo BASE_URL; ?>">Accueil</a></li> 
                        <li class="nav-item <?php echo (isset($page) && $page === 'game') ? 'active' : ''; ?>"><a class="nav-link" href="<?php echo BASE_URL; ?>game">Mini-Jeu</a></li>
                    </ul>
                    <div class="nav-right">
                        <a href="#" class="sign-in-btn">Sign-In / Login</a>
                    </div>
                </div> 
            </div>
        </nav>
    </div>
</header>
<!--================Header Menu Area =================-->


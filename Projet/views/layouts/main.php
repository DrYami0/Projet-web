<!doctype html>
<html lang="en">
<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="icon" href="<?php echo BASE_URL; ?>assets/img/favicon.png" type="image/png">
    <title><?php echo isset($title) ? $title : 'PerFran Education'; ?></title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/bootstrap.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/vendors/linericon/style.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/font-awesome.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/vendors/owl-carousel/owl.carousel.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/vendors/lightbox/simpleLightbox.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/vendors/nice-select/css/nice-select.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/vendors/animate-css/animate.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/vendors/popup/magnific-popup.css">
    <!-- main css -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/responsive.css">
    <?php if (isset($page) && $page === 'game'): ?>
    <style>
        /* Game-specific styles avec les couleurs de référence */
        /* Couleurs: #EEEEEE (gris clair), #4AB1AC (turquoise), #EEBA0B (jaune) */
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #EEEEEE 0%, #f5f5f5 100%);
            color: #333;
            margin: 0;
            padding: 40px 20px;
            line-height: 1.6;
            min-height: 100vh;
        }
        
        .game-container {
            max-width: 900px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(74, 177, 172, 0.15);
            padding: 40px;
            position: relative;
            overflow: hidden;
        }
        
        .game-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(90deg, #4AB1AC 0%, #EEBA0B 100%);
        }
        
        h1 {
            text-align: center;
            color: #4AB1AC;
            margin-bottom: 10px;
            font-size: 2.5em;
            font-weight: 700;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
        }
        
        .game-subtitle {
            text-align: center;
            color: #666;
            margin-bottom: 30px;
            font-size: 1.1em;
        }
        
        .game-instructions {
            background: linear-gradient(135deg, #4AB1AC 0%, #3a9a94 100%);
            color: white;
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 30px;
            text-align: center;
            font-size: 1.1em;
            box-shadow: 0 5px 15px rgba(74, 177, 172, 0.3);
            position: relative;
        }
        
        .game-instructions::after {
            content: '📝';
            position: absolute;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 1.5em;
        }
        
        .text-container {
            font-size: 20px;
            line-height: 2;
            margin-bottom: 40px;
            background: #EEEEEE;
            padding: 30px;
            border-radius: 15px;
            min-height: 250px;
            border: 3px solid #4AB1AC;
            box-shadow: inset 0 2px 10px rgba(0,0,0,0.05);
            position: relative;
        }
        
        .text-container::before {
            content: '📄 Texte à compléter';
            position: absolute;
            top: -15px;
            left: 20px;
            background: #4AB1AC;
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.8em;
            font-weight: bold;
        }
        
        .blank {
            display: inline-block;
            min-width: 160px;
            height: 45px;
            border: 3px dashed #4AB1AC;
            margin: 0 8px;
            text-align: center;
            vertical-align: middle;
            background: #ffffff;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            border-radius: 8px;
            line-height: 45px;
        }
        
        .blank:hover {
            background: #EEEEEE;
            border-color: #EEBA0B;
            transform: scale(1.05);
            box-shadow: 0 5px 15px rgba(238, 186, 11, 0.3);
        }
        
        .blank.filled {
            border-style: solid;
            border-color: #4AB1AC;
            background: linear-gradient(135deg, #4AB1AC 0%, #3a9a94 100%);
            color: white;
            font-weight: bold;
            box-shadow: 0 5px 15px rgba(74, 177, 172, 0.4);
        }
        
        .blank.filled::after {
            content: '✓';
            position: absolute;
            right: 5px;
            top: 50%;
            transform: translateY(-50%);
            color: #EEBA0B;
            font-size: 1.2em;
        }
        
        .words-pool {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 15px;
            margin-bottom: 40px;
            padding: 25px;
            background: #EEEEEE;
            border-radius: 15px;
            border: 2px dashed #4AB1AC;
            position: relative;
        }
        
        .words-pool::before {
            content: '🎯 Glissez les mots ici';
            position: absolute;
            top: -15px;
            left: 20px;
            background: #EEBA0B;
            color: #333;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.9em;
            font-weight: bold;
        }
        
        .word {
            background: linear-gradient(135deg, #EEBA0B 0%, #d4a509 100%);
            color: #333;
            padding: 12px 20px;
            border-radius: 25px;
            cursor: grab;
            user-select: none;
            transition: all 0.3s ease;
            font-weight: 600;
            font-size: 1.1em;
            box-shadow: 0 4px 10px rgba(238, 186, 11, 0.3);
            border: 2px solid transparent;
        }
        
        .word:hover {
            transform: translateY(-3px) scale(1.05);
            box-shadow: 0 6px 20px rgba(238, 186, 11, 0.5);
            border-color: #4AB1AC;
        }
        
        .word:active {
            cursor: grabbing;
            transform: scale(0.95);
        }
        
        .word.dragged {
            opacity: 0.4;
            transform: rotate(5deg) scale(0.9);
        }
        
        .submit-btn {
            display: block;
            margin: 0 auto;
            background: linear-gradient(135deg, #4AB1AC 0%, #3a9a94 100%);
            color: white;
            border: none;
            padding: 15px 40px;
            font-size: 1.2em;
            font-weight: bold;
            border-radius: 30px;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 5px 20px rgba(74, 177, 172, 0.4);
            text-transform: uppercase;
            letter-spacing: 1px;
            position: relative;
            overflow: hidden;
        }
        
        .submit-btn::before {
            content: '✓';
            position: absolute;
            left: 20px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 1.3em;
        }
        
        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(74, 177, 172, 0.6);
            background: linear-gradient(135deg, #3a9a94 0%, #4AB1AC 100%);
        }
        
        .submit-btn:active {
            transform: translateY(0);
        }
        
        .submit-btn:disabled {
            background: #EEEEEE;
            color: #999;
            cursor: not-allowed;
            box-shadow: none;
            transform: none;
        }
        
        .submit-btn:disabled::before {
            content: '⏳';
        }
        
        .feedback {
            text-align: center;
            margin-top: 30px;
            padding: 20px;
            border-radius: 15px;
            font-size: 1.2em;
            font-weight: bold;
            animation: slideIn 0.5s ease;
        }
        
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .feedback.success {
            background: linear-gradient(135deg, #4AB1AC 0%, #3a9a94 100%);
            color: white;
            box-shadow: 0 5px 20px rgba(74, 177, 172, 0.4);
        }
        
        .feedback.error {
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a5a 100%);
            color: white;
            box-shadow: 0 5px 20px rgba(255, 107, 107, 0.4);
        }
        
        .hidden-text {
            display: none;
        }
        
        /* Animation pour les mots corrects/incorrects */
        .word.correct {
            background: linear-gradient(135deg, #4AB1AC 0%, #3a9a94 100%);
            color: white;
            animation: pulse 0.5s ease;
        }
        
        .word.incorrect {
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a5a 100%);
            color: white;
            animation: shake 0.5s ease;
        }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }
        
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-10px); }
            75% { transform: translateX(10px); }
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .game-container {
                padding: 20px;
            }
            
            h1 {
                font-size: 1.8em;
            }
            
            .text-container {
                font-size: 16px;
                padding: 20px;
            }
            
            .blank {
                min-width: 120px;
                height: 40px;
                line-height: 40px;
            }
        }
    </style>
    <?php endif; ?>
</head>
<body>
    <?php include VIEW_PATH . '/partials/header.php'; ?>
    
    <?php echo isset($content) ? $content : ''; ?>
    
    <?php include VIEW_PATH . '/partials/footer.php'; ?>
    
    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="<?php echo BASE_URL; ?>assets/js/jquery-3.3.1.min.js"></script>
    <script src="<?php echo BASE_URL; ?>assets/js/popper.js"></script>
    <script src="<?php echo BASE_URL; ?>assets/js/bootstrap.min.js"></script>
    <script src="<?php echo BASE_URL; ?>assets/js/stellar.js"></script>
    <script src="<?php echo BASE_URL; ?>assets/vendors/lightbox/simpleLightbox.min.js"></script>
    <script src="<?php echo BASE_URL; ?>assets/vendors/nice-select/js/jquery.nice-select.min.js"></script>
    <script src="<?php echo BASE_URL; ?>assets/vendors/isotope/imagesloaded.pkgd.min.js"></script>
    <script src="<?php echo BASE_URL; ?>assets/vendors/isotope/isotope.pkgd.min.js"></script>
    <script src="<?php echo BASE_URL; ?>assets/vendors/owl-carousel/owl.carousel.min.js"></script>
    <script src="<?php echo BASE_URL; ?>assets/vendors/popup/jquery.magnific-popup.min.js"></script>
    <script src="<?php echo BASE_URL; ?>assets/js/jquery.ajaxchimp.min.js"></script>
    <script src="<?php echo BASE_URL; ?>assets/vendors/counter-up/jquery.waypoints.min.js"></script>
    <script src="<?php echo BASE_URL; ?>assets/vendors/counter-up/jquery.counterup.js"></script>
    <script src="<?php echo BASE_URL; ?>assets/js/mail-script.js"></script>
    <script src="<?php echo BASE_URL; ?>assets/js/theme.js"></script>
</body>
</html>


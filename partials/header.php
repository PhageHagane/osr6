<header id="header" class="header d-flex align-items-center fixed-top">
    <div class="container-fluid container-xl position-relative d-flex align-items-center justify-content-between">

        <a href="index.php" class="logo d-flex align-items-center">
            <!-- Uncomment the line below if you also wish to use an image logo -->
            <img src="assets/img/dti_header.png" alt="">
        </a>

        <?php
        if (basename($_SERVER['PHP_SELF']) == 'index.php') {
            ?>
            <nav id="navmenu" class="navmenu">
                <ul>
                    <!-- <li><a href="#hero" class="active">Home</a></li>
                    <li><a href="#about">About</a></li>
                    <li><a href="#services">Services</a></li>
                    <li><a href="#portfolio">Portfolio</a></li>
                    <li><a href="#team">Team</a></li>
                    <li><a href="#contact">Contact</a></li> -->
                    <?php
                    $links = [
                        ['href' => 'index.php', 'label' => 'Home'],
                        ['href' => 'registration.php', 'label' => 'On-site Registration'],
                        ['href' => 'zoom-registration.php', 'label' => 'Zoom Registration'],
                    ];

                    foreach ($links as $link) {
                        $active = (basename($_SERVER['PHP_SELF']) == $link['href']) ? ' class="active"' : '';
                        echo '<li><a href="' . $link['href'] . '"' . $active . '>' . $link['label'] . '</a></li>';
                    }
                    ?>
                </ul>
                <i class="mobile-nav-toggle d-xl-none bi bi-list"></i>
            </nav>
            <?php
        } else {

            ?>
            <nav id="navmenu" class="navmenu">
                <ul>
                    <?php
                    $links = [
                        ['href' => 'index.php', 'label' => 'Home'],
                        ['href' => 'registration.php', 'label' => 'On-site Registration'],
                        ['href' => 'zoom-registration.php', 'label' => 'Zoom Registration'],
                    ];

                    foreach ($links as $link) {
                        $active = (basename($_SERVER['PHP_SELF']) == $link['href']) ? ' class="active"' : '';
                        echo '<li><a href="' . $link['href'] . '"' . $active . '>' . $link['label'] . '</a></li>';
                    }
                    ?>
                </ul>
                <i class="mobile-nav-toggle d-xl-none bi bi-list"></i>
            </nav>
            <?php
        }
        ?>

    </div>
</header>
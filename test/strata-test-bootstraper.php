<?php
    $app = \Strata\Strata::bootstrap(include("vendor/autoload.php"));

    $app->init();
    $app->includeWordpressFixture();
    $app->includeGettextFixture();
    $app->run();

    // Customize the bootstrap file to suite your needs.
    // In theory, you should not need to edit the lines above
    // and would only need to include other bootstrap files
    // under this paragraph.

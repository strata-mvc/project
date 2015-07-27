<div class="dropdown dropdown-<?php echo $dropdown->getTemplateName(); ?>">
    <div class="dropdown-inner col-sm-12">
        <div class="col-sm-8 col first">
            <h4><?php echo $data["services_title"]; ?></h4>
            <ul class="services">
            <?php foreach ($data["services_repeater"] as $service) : ?>
                <li class="col-sm-6">
                    <a class="submenu-link" href="<?php echo $service["link"]; ?>"><?php echo $service["title"]; ?></a>
                    <?php echo $service["description"]; ?>
                </li>
            <?php endforeach; ?>
            </ul>
            <a class="btn black" href="<?php echo $data["overview_link"]; ?>"><?php echo $data["overview_label"]; ?></a>
        </div>
        <div class="col-sm-4 col last">
            <h4><?php echo $data["services_title"]; ?></h4>
            <ul class="strenghts">
           <?php foreach ($data["strengths_repeater"] as $strength) : ?>
                <li>
                    <a class="submenu-link" href="<?php echo $strength["link"]; ?>"><?php echo $strength["title"]; ?></a>
                    <?php echo $strength["description"]; ?>
                </li>
            <?php endforeach; ?>
            </ul>
        </div>
    </div>
</div>


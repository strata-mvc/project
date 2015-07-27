<?php
    $parentId = \App\Model\Page::getParentId();
    if ($parentId > 0) :
?>


    <?php $expertBGImg =  $Acf->get("speak_with_expert_bg_image", $parentId); ?>
    <section class="speak-banner"  <?php echo ($expertBGImg) ? 'style="background-image:url('.$expertBGImg.')"' : ''; ?>>
        <div class="container">
            <div class="col-sm-4 speak-title">
                <h3><?php echo $Acf->get("speak_with_expert_title", $parentId); ?></h3>
            </div>
            <div class="col-sm-4 speak-text">
                <?php echo $Acf->get("speak_with_expert_content", $parentId); ?>
            </div>
            <div class="col-sm-4 speak-call">
                <div class="inner-block">
                    <span itemprop="telephone">
                        <a class="telephone" href="tel:<?php echo $Acf->get("phone_number", $parentId); ?>">
                            <?php echo $Acf->get("phone_number", $parentId); ?>
                        </a>
                    </span>
                    <p><?php _e("Or", PROJECT_KEY) ?> <a class="cta" href="<?php echo $Acf->get("request_callback_link", $parentId); ?>"><?php echo $Acf->get("request_callback_label", $parentId); ?></a></p>
                </div>
            </div>
        </div>
    </section>



    <?php
        $ctas = (array)$Acf->get("cross_reference_repeater", $parentId);
    ?>
    <section class="bottom-ctas">
        <div class="container">
            <?php foreach ($ctas as $idx => $cta) : ?>
            <div class="cta-block col-md-4">
                <div class="block-icon">
                    <?php if($cta['block_image_type'] == "image") : ?>
                        <img src="<?php echo $cta['block_image']; ?>" alt="" class="svg" />
                    <?php else : ?>
                        <?php echo $cta['block_icon']; ?>
                    <?php endif; ?>
                </div>
                <h4><?php echo $cta["title"] ?></h4>
                <?php echo $cta["content"] ?>
                <?php if (!empty($cta['call_to_action_link'])) : ?>
                    <a class="btn" href="<?php echo $cta['call_to_action_link']; ?>"><?php echo $cta['call_to_action_label']; ?></a>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </section>

<?php endif; ?>

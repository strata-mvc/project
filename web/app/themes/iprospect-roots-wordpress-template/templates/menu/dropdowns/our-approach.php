<div class="dropdown dropdown-<?php echo $dropdown->getTemplateName(); ?>">
	<div class="dropdown-inner col-sm-8  col-sm-offset-2">
		<div class="col-sm-6 col first">
			<a class="submenu-link" href="<?php echo $data["process_link"]; ?>"><?php echo $data["process_title"]; ?></a>
			<?php echo $data["process_content"]; ?>
		</div>
		<div class="col-sm-6 col last">
			<a class="submenu-link" href="<?php echo $data["offering_link"]; ?>"><?php echo $data["offering_title"]; ?></a>
			<?php echo $data["offering_content"]; ?>

			<?php $img = $data["offering_thumbnail"]; ?>
			<?php if (is_array($img)) : ?>
			    <img src="<?php echo $img["url"] ?>" alt=""/>
			<?php endif; ?>
		</div>
	</div>
</div>

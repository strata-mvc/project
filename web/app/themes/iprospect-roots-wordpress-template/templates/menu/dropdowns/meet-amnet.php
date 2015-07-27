<div class="dropdown dropdown-<?php echo $dropdown->getTemplateName(); ?>">
    <div class="dropdown-inner col-sm-8  col-sm-offset-2">
		<div class="col-sm-6 col first">
			<a class="submenu-link" href="<?php echo $data["block_link"]; ?>"><?php echo $data["block_title"]; ?></a>
			<?php echo $data["block_content"]; ?>
		</div>
		<div class="col-sm-6 col last">
			<a class="submenu-link" href="<?php echo $data["our_people_link"]; ?>"><?php echo $data["our_people_title"]; ?></a>
			<?php echo $data["our_people_content"]; ?>
		</div>
	</div>
</div>

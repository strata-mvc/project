<?php foreach ($Acf->get("columns", $MenuHelper->linkedObject->ID) as $column) : ?>
    <div class="col-md-3">
    <ul>
    <?php foreach ($column['sections'] as $section) : ?>
	    <?php echo $MenuHelper->generateTitle($section); ?>
	    <?php if($section['links']) : ?>
	    	<li>
		    	<ul class="submenu">
		        <?php foreach ($section['links'] as $link) : ?>
		            <?php echo $MenuHelper->generateLink($link); ?>
		        <?php endforeach; ?>

		        </ul>
		        <?php echo $MenuHelper->generateTextBlock($section); ?>
	        </li>
	    <?php endif; ?>
	<?php endforeach; ?>
	</ul>
	</div>
<?php endforeach; ?>
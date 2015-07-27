<div class="dropdown dropdown-<?php echo $dropdown->getTemplateName(); ?>">
    <div class="dropdown-inner col-sm-8  col-sm-offset-3">
    	<div class="block">
	    	<a class="submenu-link" href="<?php echo $data["block_link"]; ?>"><?php echo $data["block_title"]; ?></a>
		    <?php echo $data["block_content"]; ?>
	    </div>
	    <div class="block last">
	        <a class="submenu-link" href="<?php echo $data["our_clients_link"]; ?>"><?php echo $data["our_clients_title"]; ?></a>
		    <?php if (is_array($data["client_repeater"])) : ?>
		    	<ul class="clients">
			    <?php foreach ($data["client_repeater"] as $client) : ?>
			        <li>
			        	<div class="client-logo">
			        		<?php echo get_the_post_thumbnail($client->ID) ?>
			        	</div>
			        </li>

			    <?php endforeach; ?>
			    </ul>
		    <?php endif; ?>
	    </div>
    </div>
</div>

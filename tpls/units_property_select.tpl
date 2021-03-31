<h3>Select property for Unit Setup</h3>
<ul>
    <?php foreach($prop_list as $id => $name){ ?>
    <li><a href="?units_menue&property_id=<?php echo $id; ?>"><?php echo $name; ?></a></li>
    <?php } ?>
</ul>
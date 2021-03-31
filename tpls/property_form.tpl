<div class="property_form">
    <h3><?php echo $property->name; ?> </h3>
    <span>Connectivity: <?php if($connectivity){ echo "OK"; }else{ echo "NOT OK"; } ?> <?php if($active == '1') echo "and ACTIVE"; ?></span>
    <?php if($active == '1'){ ?> <a href="/?deactivate=<?php echo $property->id; ?>" style="color: red"><b>dectivate</b></a><br> <?php } ?>
    <?php if($active == '0' && $connectivity){ ?>
    <a href="/?activate=<?php echo $property->id; ?>" style="color: red"><b>Activate LeiwandLock</b></a><br><i
            style="font-size: 80%">ensure you have finished <a
                href="?units_menue&property_id=<?php echo $property->id; ?>">Setup for Units</a> and <a
                href="?emails_menue">Email Setup</a> first!</i>
    <?php } ?>
    <form action="?action=setup_save" method="post">
        <input type="hidden" name="id" value="<?php echo $setup['id']; ?>"> <label
                for="<?php echo $property->id; ?>fproperty_id">Property ID:</label><br> <input type="text"
                                                                                               id="<?php echo $property->id; ?>fproperty_id"
                                                                                               name="property_id"
                                                                                               value="<?php echo $property->id; ?>"
                                                                                               readonly><br> <label
                for="<?php echo $property->id; ?>fhost">LeiwandLock Server:</label><br> <input type="text"
                                                                                               id="<?php echo $property->id; ?>fhost"
                                                                                               name="host"
                                                                                               placeholder="host/IP"
                                                                                               value="<?php echo $setup['host']; ?>"><br>
        <label for="<?php echo $property->id; ?>fll_token">LeiwandLock Token:</label><br> <input type="password"
                                                                                                 id="<?php echo $property->id; ?>fll_token"
                                                                                                 name="ll_token"
                                                                                                 value="<?php echo $setup['ll_token']; ?>"><br>
        <br> <input type="submit" value="Submit">
    </form>
</div>
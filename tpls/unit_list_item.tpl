</tr>
<tr>
    <td>
        <?php echo $unit->id; ?>
    </td>
    <td>
        <?php echo $unit->name; ?>
    </td>
    <td>
        <?php if($db_units_array[$unit->id]['synced'] == 1){ ?> <b>yes</b><?php }else{ ?><label
                for="<?php echo $unit->id; ?>_sync">sync </label> <input type="checkbox"
                                                                         id="<?php echo $unit->id; ?>_sync"
                                                                         name="sync[<?php echo $unit->id; ?>]"
                                                                         value="<?php echo $unit->name; ?>"/><?php }; ?>
    </td>
    <td>
        <?php if($db_units_array[$unit->id]['synced'] == 1){ ?><?php if($db_units_array[$unit->id]['active'] == 1){ ?>
        <b>yes</b><?php }else{ ?><label for="<?php echo $unit->id; ?>_active">activate </label> <input type="checkbox"
                                                                                                       id="<?php echo $unit->id; ?>_active"
                                                                                                       name="active[<?php echo $unit->id; ?>]"
                                                                                                       value="<?php echo $unit->name; ?>"/><?php }; ?><?php }else{ echo "sync first"; } ?>
    </td>
</tr>
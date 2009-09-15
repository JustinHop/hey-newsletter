<?php
/*
 * new newsletter
 */

?>
<div class="hey_wrap"> 
<div id="hey_mainblock">
<form action="<?php echo $action_url ?>" method="post">
<input type="hidden" name="submitted" value="1" />
<div id="date">Date: <input id="datepicker" type="text" name="date" <?php 
if ( isset($_POST['submitted']) ) { 
    echo 'value="'.$_POST['date'].'" ';
}?> />
    <span class="heybutton heypostadd">Add Another Article</span>
</div>
<div id="heyposts">
<div id="heypost_0" class="heypost">
    <textarea cols="50" rows="5" name="heypostpost_0" class="heypostpost"></textarea>

</div>
     <?php 
    /*
      echo '<select multiple="multiple" name="heypostcats_0[]" size="5" class="heypostcats">';
      $categories=  get_categories(); 
      foreach ($categories as $cat) {
        $option = '<option value="'. $cat->cat_ID.'">';
        $option .= $cat->cat_name;
        $option .= ' ('.$cat->category_count.')';
        $option .= '</option>';
        echo $option . "\n";
      }
        echo '</div>';
    </select>
     */
     ?>
    <fieldset class="inline-edit-col-center inline-edit-categories"><div class="inline-edit-col">
        <span class="title inline-edit-categories-label"><?php _e( 'Categories' ); ?>
            <span class="catshow"><?php _e('[more]'); ?></span>
            <span class="cathide" style="display:none;"><?php _e('[less]'); ?></span>
        </span>  
        <ul class="cat-checklist">
            <?php wp_category_checklist(); ?>
        </ul>    
    </div></fieldset>

    <div class="heybutton heypostremove">Remove</div>
    
</div>
<div class="submit"><input type="submit" name="Submit" value="Create" /></div>
</form>
</div>
<?php if ( isset($_POST['submitted']) ) {?>
<div id="sideblock" style="float:right;width:220px;margin-left:10px;">
<div id="postedlabel">POSTed</div>
<div>Date: <?php echo $_POST['datepicker']; ?></div>
<div>Dump</div>
<div style="white-space: pre;"><?php print_r($_POST); ?></div>
</div>
<?php } ?>



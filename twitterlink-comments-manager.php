<?php // twitterlink-comments settings page
$options=$this->get_options();
?>
<style type="text/css">
    dl { padding: 3px;}
    dl h3 { padding: 2px; background-color: #dfdfdf; margin: 0px 0px 5px 0px;}
    td dl { padding:3px; margin: 15px 0 0; background-color: white; border: 1px solid #dfdfdf; }
    td.right { width: 20%;}
    #radio_buts tr td {text-align: center; border: 1px solid #dfdfdf;}
    .doitcenter td {text-align: center;}
    .but-warning:hover { background: #ce0000; color: #ffffff;}
    table input { background-color: lightGoldenRodYellow;}
		</style>
<script type="text/javascript">
(function($) {
$(document).ready(function(){
    if($('#auto_add').val() == 0) {
	$('.auto_add_options').hide();
    }
    $('#auto_add').change(function(){
	if($(this).val() == 1){
	    $('.auto_add_options').show();
	} else {
	    $('.auto_add_options').hide();
	}
	});
    });
})(jQuery);
</script>

<div class="wrap">
<h2><?php _e('TwitterLink Comments',$this->plugin_domain);?></h2>
<p><?php _e('This plugin adds an extra field for the comment author to add their Twitter username so it can be displayed in the chosen position in the list of comments for a post.<br/>No template editing will be required unless you want to specify the exact look and postion of the field.',$this->plugin_domain);?></p>
<p><?php _e('Reset to default settings if you are having any problems',$this->plugin_domain);?></p>
<table>
<tbody><tr valign="top"><td class="left">
		<!-- *********************** BEGIN: Main Content ******************* -->
		<form name="form1" method="post" action="<?php echo $action_url ?>">
		<?php wp_nonce_field('twitterlink-nonce');?>
		<fieldset class="options">
		<dl>
			<h3><?php _e('Add Twitter Field Automatically?',$this->plugin_domain)?></h3>
			<select name="auto_add" id="auto_add">
			    <?php $yes = $no = 0; if($options['auto_add'] == 1) { $yes = 'selected="selected"' ;  } else { $no = 'selected="selected"'; }?>
			    <option value="1" <?php echo $yes;?>><?php _e('Yes',$this->plugin_domain);?></option>
			    <option value="0" <?php echo $no;?>><?php _e('No',$this->plugin_domain);?></option>
			</select>
		<p><?php _e('If you want to add the field yourself, please make sure it uses name="atf_twitter_id" and is part of the &lt;form&gt; that contains the Name, email, url and comment fields',$this->plugin_domain)?></p>
		</dl>
		<dl class="auto_add_options">
		    <h3><?php _e('css classes',$this->plugin_domain)?></h3>
		    <Label for="div_class"><?php _e('Class for the DIV used to wrap the field (for automatic added field)',$this->plugin_domain)?></Label>
		    <br/><input type="text" name="div_class" value="<?php echo twitlink_deslashit(htmlspecialchars($options['div_class']));?>"/>
		    <br/>
		    <label for="input_class"><?php _e('Class for the input field (for automatic added field)',$this->plugin_domain)?></label>
		    <br/><input type="text" name="input_class" value="<?php echo twitlink_deslashit(htmlspecialchars($options['input_class']));?>"/>
		</dl>
		<dl class="auto_add_options">
		    <h3><?php _e('Field Description',$this->plugin_domain)?></h3>
		    <table width="100%"><tr><td>
		    <label for="pre_html"><?php _e('HTML before field description (maximum 120 characters)',$this->plugin_domain)?></label>
		    <br/><input size="80" type="text" name="pre_html" value="<?php echo twitlink_deslashit(htmlspecialchars($options['pre_html']));?>"/>
		    <br/><label for="field_description"><?php _e('Text describing the added field (added before the input field)',$this->plugin_domain)?></label>
		    <br/><textarea name="field_description" cols="60" rows="4"><?php echo twitlink_deslashit(htmlspecialchars($options['field_description']));?></textarea>
		    <br/><label for="post_html"><?php _e('HTML after field description (maximum 120 characters)',$this->plugin_domain)?></label>
		    <br/><input size="80" type="text" name="post_html" value="<?php echo twitlink_deslashit(htmlspecialchars($options['post_html']));?>"/>
		    </td>
		    <td width="33%">
			<?php _e('You can use the following tags only',$this->plugin_domain);?> : <br/>
			<?php echo htmlspecialchars("<a> <br> <p> <img> <small> <strong>");?>
			<br/>&lt;a <? _e('can use',$this->plugin_domain);?> href= title= rel= target=
			<br/>&lt;p <? _e('can use',$this->plugin_domain);?> class=
			<br/>&lt;img <? _e('can use',$this->plugin_domain);?> src= title= alt= width= height=
		    </td></tr></table>
		</dl>
		<dl>
		    <h3><?php _e('Twitter Link Position',$this->plugin_domain);?></h3>
		    <table id="radio_buts" width="100%"><tr><td width="33%"><?php _e('Under Name Link',$this->plugin_domain);?></td><td width="33%"><?php _e('Start of Comment Text',$this->plugin_domain);?></td><td width="33%"><?php _e('End of Comment Text',$this->plugin_domain)?></td></tr>
		    <tr><td><input type="radio" name="position" value="under_name" <?php echo $options['position'] == 'under_name' ? "checked" : '';?>/></td>
			<td><input type="radio" name="position" value="start_comment" <?php echo $options['position'] == 'start_comment' ? "checked" : '';?>/></td>
			<td><input type="radio" name="position" value="end_comment" <?php echo $options['position'] == 'end_comment' ? "checked" : '';?>/></td>
		    </tr>
		    </table>
		</dl>
		<dl>
		    <h3><?php _e('Link Format',$this->plugin_domain);?></h3>
		    <table class="doitcenter">
			<tr><td width="33%"><?php _e('HTML before link (max 120 chars)',$this->plugin_domain);?></td><td width="33%"><?php _e('Anchor Text (use [username] to show username) no html',$this->plugin_domain);?></td><td width="33%"><?php _e('HTML after link (max 120 chars)',$this->plugin_domain);?></td></tr>
			<tr><td><input type="text" name="pre_link_html" value="<?php echo twitlink_deslashit(htmlspecialchars($options['pre_link_html']));?>"/></td><td><input type="text" name="anchor_text" value="<?php echo twitlink_deslashit(htmlspecialchars($options['anchor_text']));?>"/></td><td><input type="text" name="post_link_html" value="<?php echo twitlink_deslashit(htmlspecialchars($options['post_link_html']));?>"/></td></tr>
			<tr><td><?php _e('Use NoFollow?',$this->plugin_domain);?></td><td><?php _e('Open in new window?',$this->plugin_domain);?></td><td><?php _e('Manual Link?');?></td></tr>
			<tr><td><input type="radio" name="nofollow" value="yes" <?php echo $options['nofollow'] == 'yes' ? 'checked' : '';?>/><?php _e('Yes',$this->plugin_domain);?><input type="radio" name="nofollow" value="no" <?php echo $options['nofollow'] == 'no' ? 'checked' : '';?>/><?php _e('No',$this->plugin_domain);?></td>
			    <td><input type="radio" name="newwindow" value="yes" <?php echo $options['newwindow'] == 'yes' ? 'checked' : '';?>/><?php _e('Yes',$this->plugin_domain);?><input type="radio" name="newwindow" value="no" <?php echo $options['newwindow'] == 'no' ? 'checked' : '';?>/><?php _e('No',$this->plugin_domain);?></td>
			    <td><input type="radio" name="manual" value="yes" <?php echo $options['manual'] == 'yes' ? 'checked' : '';?>/><?php _e('Yes',$this->plugin_domain);?><input type="radio" name="manual" value="no" <?php echo $options['manual'] == 'no' ?  'checked' : '';?>/><?php _e('No',$this->plugin_domain);?></td>
			</tr>
		    </table>
		</dl>
		<p>
		    
		    <input class="button-primary" type="submit" name="save" value="<?php _e('Save Changes',$this->plugin_domain);?>"/>
		    <input class="but-warning" type="submit" name="reset" value="<?php _e('Delete/Reset Settings',$this->plugin_domain);?>" onclick="return confirm('<?php _e('Do you really want to delete/reset the plugin settings?',$this->plugin_domain);?>');"/>
		    
		</p>
		<td class="right">
		<!-- *********************** BEGIN: Sidebar ************************ -->		

		

		<dl>
			<dt></dt><h4><?php _e('Plugin',$this->plugin_domain);?></h4>
			<dd>
			<ul>
				<li><a class="lhome" href="http://comluv.com/download/twitlink-comments"><?php _e('Plugin Homepage',$this->plugin_domain);?></a></li>
				<li><a class="lwp" href="http://comluv.com/support-ticket/"><?php _e('WordPress Support',$this->plugin_domain);?></a></li>
			</ul>			
			</dd>

		</dl>

		<dl>
			<dt></dt><h4><?php _e('Do you like this plugin?',$this->plugin_domain);?></h4>
			<dd>
			<p style="font-size: 8pt;"><?php _e('I spend a lot of time on the plugins I have written for WordPress. Any donation would be highly appreciated.',$this->plugin_domain);?></p>
			<ul>
				<li><a class="lpaypal" href="http://comluv.com/about/donate/"><?php _e('Donate via PayPal',$this->plugin_domain);?></a></li>
			</ul>			
			</dd>

		</dl>
		<dl>
		    <dt></dt><h4><?php _e('Translations',$this->plugin_domain);?></h4>
		    <dd>
			<p style="font-size: 8pt;"><?php _e('Many thanks to these people for providing translations',$this->plugin_domain);?></p>
			<ul>
			    <li><?php _e('Italian by',$this->plugin_domain);?> <a class="italian" href="http://gidibao.net/">Gianni</a></li>
			</ul>
		    </dd>
		</dl>
		<!-- *********************** END: Sidebar ************************ -->
		</td> <!-- [right] -->
		
		
		</tr>
		
		</tbody></table>
		</fieldset>
		
		</form>
</div>
<?php
?>
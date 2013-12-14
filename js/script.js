jQuery(document).ready(function(){
    
    // email sub event
    jQuery('#ab_sub_button').live('click',function(){
        var email = jQuery('#sub_email').val();
        jQuery('#ab_sub_button').text('Please wait...');
        if(email == ''){
            alert('Please enter an email address');
            return;
        }
        // send sub action to admin 
        jQuery.post(ajaxurl,{action: 'ab_subscribe', email : email},function(data){ jQuery('#sub_box').after('<div style="margin-bottom: 5px; padding: 5px; font-size: 10px;">' + data + '</div>').hide()});
    }); 
    // listen for rest button click
    jQuery('.wrap form #reset').click(function(evt){
        var message = ab_global.reset_message;
        return confirm(message);
    });   
});
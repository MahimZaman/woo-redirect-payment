<?php
    if($_SERVER['REQUEST_METHOD'] == 'POST'){
        $store_type = isset($_POST['wss_store_type']) ? $_POST['wss_store_type'][0] : '' ;
        $receiver = isset($_POST['wss_receiver']) ? $_POST['wss_receiver'] : '' ;
        $supplier = isset($_POST['wss_supplier']) ? $_POST['wss_supplier'] : '' ;

        update_option('wss_supplier', $supplier);
        update_option('wss_receiver', $receiver);
        update_option('wss_store_type', $store_type);
    }
?>

<div class="wrap">
    <div class="wss_settings_wrap">
        <h2 class="wss_settings_title">
            <?php echo __('Woo Supplier Store - Settings', 'wss-text');?>
        </h2>
        <hr>
        <form action="" class="wss_form" method="POST">
            <label class="wss_label"><?php _e('Who are you?', 'wss-text');?></label>
            <div class="wss_form_group wss_radio_group">
                <div class="wss_radio_inner">
                    <input type="radio" value="receiver" name="wss_store_type[]" id="wss_st_receiver" class="wss_store_type" <?php echo get_option('wss_store_type') === "receiver" ? 'checked' : '' ;?>/>
                    <label for="wss_st_receiver" class="wss_label"><?php _e('Product Store', 'wss-text');?></label>
                </div>
                <div class="wss_radio_inner">
                    <input type="radio" value="supplier" name="wss_store_type[]" id="wss_st_supplier" class="wss_store_type" <?php echo get_option('wss_store_type') === "supplier" ? 'checked' : '' ;?>/>
                    <label for="wss_st_supplier" class="wss_label"><?php _e('Checkout Store', 'wss-text');?></label>
                </div>
            </div>
            <div id="wss_receiver_fields" class="wss_form_group">
                <label for="wss_supplier"><?php _e('Checkout Store URL:', 'wss-text');?></label>
                <input type="url" placeholder="https://www.example.com" name="wss_supplier" id="wss_supplier" value="<?php echo get_option('wss_supplier');?>">
            </div>
            <div id="wss_supplier_fields" class="wss_form_group">
                <label for="wss_receiver"><?php _e('Product Store URL:', 'wss-text');?></label>
                <input type="url" placeholder="https://www.example.com" name="wss_receiver" id="wss_receiver" value="<?php echo get_option('wss_receiver');?>">
            </div>
            <button class="button button-primary"><?php _e('Save Settings', 'wss-text');?></button>
        </form>
    </div>
</div>
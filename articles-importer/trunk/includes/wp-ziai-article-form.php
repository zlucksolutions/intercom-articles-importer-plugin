<div class="wrap wp-ziai-article">
    <h1><?php esc_html_e('Automatic Articles Settings', 'ziai-articles'); ?></h1>
    <?php
    // wp_enqueue_script('jquery-ui-progressbar');
    // $wp_scripts = wp_scripts();
    // wp_enqueue_style('plugin_name-admin-ui-css',
    //     'http://ajax.googleapis.com/ajax/libs/jqueryui/' . $wp_scripts->registered['jquery-ui-core']->ver . '/themes/smoothness/jquery-ui.css',
    //     false,
    //     1.0,
    //     false);
    ?>
    <div id="wpbody" role="main">
        <div id="wpbody-content">
            <div class="wrap nosubsub">
                <hr class="wp-header-end">
                <div id="ajax-response"></div>
                <div id="col-container" class="wp-clearfix">
                    <div id="col-left">
                        <div class="col-wrap">
                            <div class="form-wrap">
                                <?php
                                if($errormsg) { ?>
                                    <p><?php _e($errormsg, 'ziai-articles'); ?></p>
                                <?php
                                }
                                ?>
                                <form method="post" action="" class="validate zl-admin-form">
                                    <div id="loader" class="lds-dual-ring hidden overlay"></div>
                                    <div id="progressbar"></div><br>
                                    <div class="zl-ziai-setting zl-setting-2">
                                        <div class="form-field form-required term-name-wrap">
                                            <label for="ziai_access_token"><b><?php esc_html_e('Access Token', 'ziai-articles'); ?></b>
                                                <div class="hint">
                                                    <i class="hint-icon">i</i>
                                                    <div class="hint-description"><?php _e('Find details on how to find your Access Token <a href="https://developers.intercom.com/building-apps/docs/authentication-types#section-access-tokens" target="_blank">here.</a>', 'ziai-articles'); ?></div>
                                                </div>
                                            </label>
                                            <input name="ziai_access_token" id="ziai_access_token" type="text" <?php if($token != ''){ ?> value="<?php echo esc_attr($token); ?>" <?php } ?> aria-required="true" required/>
                                        </div>
                                        <br>
                                        <div class="zl-button">
                                            <!-- <div style="padding: 10px 0;"> -->
                                            <p class="submit">
                                                <button type="button" class="button button-primary" id="bulk-import">Import All</button><span class=""></span>
                                            </p>
                                            <!-- </div> -->
                                            <div></div>
                                        </div>
                                    </div>
                                    <br>
                                    <div class="zl-ziai-setting zl-setting-2">
                                        <div style="display: none;">
                                            <div class="form-field form-required term-name-wrap">
                                                <label for="zl_get_post_type"><b><?php esc_html_e('Post Type', 'ziai-articles'); ?></b></label>
                                                <?php 
                                                echo '<select name="zl_post_type_get">
                                                <option value="zl_ziai_article">Automatic Article</option>
                                                </select>';
                                                ?>
                                                <span class="zl-ajax-loader"></span>
                                            </div>
                                            <div class="form-field form-required term-name-wrap post_type_category">
                                            </div>
                                        </div>
                                        <h2><?php esc_html_e('Automatic Article Cron - Auto script to collect Automatic Article from the given Access Token.', 'ziai-articles'); ?></h2>
                                        <div class="form-field form-required term-name-wrap">
                                            <label for="zl_anchor_default_author"><b><?php esc_html_e('Assign Imported Article to', 'ziai-articles'); ?></b></label>
                                            <?php wp_dropdown_users(array('name' => 'zl_default_author', 'selected' => $zl_default_author)); ?>
                                        </div>
                                        <div class="form-field form-required term-name-wrap">
                                            <label for="ziai_cron_start_time"><b>Automatic Cron Start time</b><small><i> (for when to execute the event.)</i></small>
                                                <div class="hint">
                                                    <i class="hint-icon">i</i>
                                                    <div class="hint-description"><?php esc_html_e('Automatic cron will start from this time and will continue running at defined intervals as below.', 'ziai-articles'); ?></div>
                                                </div>
                                            </label>
                                            <input name="ziai_cron_start_time" id="ziai_cron_start_time" type="time" value="<?php echo esc_attr($cron_start_time); ?>" aria-required="true" required="required" />
                                        </div>
                                        <div class="form-field form-required term-name-wrap">
                                            <label for="ziai_cron_time"><b>Run Cron to fetch Automatic at every X hours - </b><small><i> (Eg. - for every 1 hour 30 minutes - enter 1.5)</i></small>
                                                <div class="hint">
                                                    <i class="hint-icon">i</i>
                                                    <div class="hint-description"><?php esc_html_e('The Automatic Cron will run at every X hours - (eg. every 2 hours).', 'ziai-articles'); ?></div>
                                                </div>
                                            </label>
                                            <input name="ziai_cron_time" id="ziai_cron_time" type="number" min="0" step="any" value="<?php echo esc_attr($cron_time); ?>" aria-required="true" required="required" />
                                        </div>
                                        <br>
                                        <div class="zl-button">
                                            <?php
                                            wp_nonce_field('zl-ziai-settings-save', 'zl-ziai-settings');
                                            submit_button('Save Changes', 'primary', 'savechanges');
                                            //submit_button('Save & Run Now', 'primary runnow', 'runnow');
                                            ?>
                                            <!-- <div style="padding: 10px 0;"> -->
                                            <!-- <p class="submit">
                                                <button type="button" class="button button-primary" id="bulk-import">Import All</button><span class=""></span>
                                            </p> -->
                                            <!-- </div> -->
                                            <div></div>
                                        </div>
                                    </div>
                                </form>
                                <!-- <div>
                                    <h5>Bulk Importer</h5>
                                    <div id="progressbar"></div>
                                    <div style="padding: 10px 0;">
                                        <button class="button button-primary" id="bulk-import">Import All</button>
                                    </div>
                                </div> -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="clear"></div>
        </div>
    </div>
</div>
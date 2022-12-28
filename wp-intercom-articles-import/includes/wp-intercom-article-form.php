<div class="wrap wp-intercom-article">
    <h1><?php _e('Intercom Settings', 'wp-intercom-article'); ?></h1>
    <div id="wpbody" role="main">
        <div id="wpbody-content">
            <div class="wrap nosubsub">
                <hr class="wp-header-end">
                <div id="ajax-response"></div>
                <div id="col-container" class="wp-clearfix">
                    <div id="col-left">
                        <div class="col-wrap">
                            <div class="form-wrap">
                                <p><?php _e($errormsg, 'wp-intercom-article'); ?></p>
                                <form method="post" action="" class="validate zl-admin-form">
                                    <div class="zl-intercom-setting zl-setting-2">
                                        <div class="form-field form-required term-name-wrap">
                                            <label for="zl_access_token"><b><?php _e('Access Token', 'wp-intercom-article'); ?></b>
                                                <div class="hint">
                                                    <i class="hint-icon">i</i>
                                                    <div class="hint-description"><?php _e('Find details on how to find your Access Token <a href="https://developers.intercom.com/building-apps/docs/authentication-types#section-access-tokens" target="_blank">here.</a>', 'wp-intercom-article'); ?></div>
                                                </div>
                                            </label>
                                            <input name="zl_access_token" id="zl_access_token" type="text" value="<?php echo $token; ?>" aria-required="true" required/>
                                        </div>
                                    </div>
                                    <br>
                                    <div class="zl-intercom-setting zl-setting-2">
                                        <div style="display: none;">
                                            <div class="form-field form-required term-name-wrap">
                                                <label for="zl_get_post_type"><b><?php _e('Post Type', 'wp-intercom-article'); ?></b></label>
                                                <?php 
                                                echo '<select name="zl_post_type_get">';
                                                ?>
                                                <option value="zl_intercom_article">Intercom Article</option>
                                                <?php
                                                echo '</select>';
                                                ?>
                                                <span class="zl-ajax-loader"></span>
                                            </div>
                                            <div class="form-field form-required term-name-wrap post_type_category">
                                            </div>
                                        </div>
                                        <h2><?php _e('Intercom Article Cron - Auto script to collect intercom Article from the given Access Token.', 'wp-intercom-article'); ?></h2>
                                        <div class="form-field form-required term-name-wrap">
                                            <label for="zl_anchor_default_author"><b><?php _e('Assign Imported Article to', 'wp-intercom-article'); ?></b></label>
                                            <?php wp_dropdown_users(array('name' => 'zl_default_author', 'selected' => $zl_default_author)); ?>
                                        </div>
                                        <div class="form-field form-required term-name-wrap">
                                            <label for="zl_intercom_cron_start_time"><b>Intercom Cron Start time</b><small><i> (for when to execute the event.)</i></small>
                                                <div class="hint">
                                                    <i class="hint-icon">i</i>
                                                    <div class="hint-description"><?php _e('Intercom cron will start from this time and will continue running at defined intervals as below.', 'wp-intercom-article'); ?></div>
                                                </div>
                                            </label>
                                            <input name="zl_intercom_cron_start_time" id="zl_intercom_cron_start_time" type="time" value="<?php echo $cron_start_time ?>" aria-required="true" required="required" />
                                        </div>
                                        <div class="form-field form-required term-name-wrap">
                                            <label for="zl_intercom_cron_time"><b>Run Cron to fetch Intercom at every X hours - </b><small><i> (Eg. - for every 1 hour 30 minutes - enter 1.5)</i></small>
                                                <div class="hint">
                                                    <i class="hint-icon">i</i>
                                                    <div class="hint-description"><?php _e('The Intercom Cron will run at every X hours - (eg. every 2 hours).', 'wp-intercom-article'); ?></div>
                                                </div>
                                            </label>
                                            <input name="zl_intercom_cron_time" id="zl_intercom_cron_time" type="number" min="0" step="any" value="<?php echo $cron_time ?>" aria-required="true" required="required" />
                                        </div>
                                        <br>
                                        <div class="zl-button">
                                            <?php
                                            wp_nonce_field('zl-intercom-settings-save', 'zl-intercom-settings');
                                            submit_button('Save Changes', 'primary', 'savechanges');
                                            submit_button('Save & Run Now', 'primary runnow', 'runnow');
                                            ?>
                                            <div></div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="clear"></div>
        </div>
    </div>
</div>
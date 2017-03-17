<?php

class JPCFSMAdmin
{
    private $network_active_plugins;

    public $actions;
    private $notices;

    public static function instance() {
        static $instance = null;
        
        if ( null === $instance ) {
            $instance = new JPCFSMAdmin;
            $instance->setup();
        }
        
        return $instance;
    }

    private function setup()
    {
        if ( !$this->network_active_plugins ) {
            $this->network_active_plugins = get_site_option('active_sitewide_plugins', array());
        }

        $this->actions = array(
            ($this->isNetworkActive() ? 'network_' : '') . 'admin_menu' => array( $this, 'pages' )
        );

        foreach ( $this->actions as $tag => $callback ) {
            if ( is_callable($callback) ) {
                add_action ( $tag, $callback );
            }
        }
    }

    private function isNetworkActive()
    {
        if ( !is_multisite() ) {
            return false;
        }

        return is_array($this->network_active_plugins) && isset($this->network_active_plugins[JPCFSM_BASE]);
    }

    public function pages()
    {
        add_submenu_page(
            $this->isNetworkActive() ? 'settings.php' : 'options-general.php',
            sprintf(__('%s Settings', JPCFSM_DOMAIN), JPCFSM_NAME),
            'JetPack CF Message',
            'manage_options',
            'jpcfsm',
            array($this, 'display')
        );

        $this->maybeUpdate();

        return $this;
    }

    public function maybeUpdate()
    {
        $this->update();
    }

    private function update()
    {
        if ( !isset($_POST['submit']) )
            return;

        if ( !isset($_POST['jpcfm_nonce']) || !wp_verify_nonce($_POST['jpcfm_nonce'], 'jpcfm_update') )
            return;

        $settings = array();

        if ( isset($_POST['message']) && trim($_POST['message']) ) {
            $settings['message'] = esc_attr($_POST['message']);
        }

        if ( isset( $_POST['strip_content'] ) ) {
            $settings['strip_content'] = true;
        }

        if ( $settings ) {
            update_option('jpcfsm_settings', $settings);
        } else {
            delete_option('jpcfsm_settings');
        }

        jp_cf_success_message()->flushSettings();

        $this->notices = '<div class="is-dismissible notice updated"><p>' . __('Changes saved successfully!', JPCFSM_DOMAIN) . '</p></div>';
    }

    public function display()
    {
        global $current_user;
        $jpcfsm = jp_cf_success_message();

        ?>

        <style type="text/css">
            @media screen and (min-width: 700px) {
                .jpcfsm-cont {
                    display: flex;
                    justify-content: space-between;
                    max-width: 100%;
                }
                .jpcfsm-left {
                    margin-right: 2em;
                    overflow: hidden;
                }
                .jpcfsm-right {
                    max-width: 30%;
                }
            }
        </style>

        <div class="wrap jpcfsm-cont">

            <?php if ( $this->notices ) : ?>
                <?php print $this->notices; ?>
            <?php endif; ?>

            <div class="jpcfsm-left">

                <form method="post" id="poststuff" class="gc-settings">
                    <div id="postbox-container" class="postbox-container">
                        <div class="meta-box-sortables ui-sortable" id="normal-sortables">

                            <div class="postbox">
                                <h3 class="hndle"><span><?php _e('Format Message', JPCFSM_DOMAIN); ?></span></h3>
                                <div class="inside">
                                    <p>
                                        <em><?php _e('Format your message below', JPCFSM_DOMAIN); ?></em>
                                    </p>

                                    <?php wp_editor($jpcfsm->settings('message'), 'message'); ?>
                                </div>
                            </div>

                            <div class="postbox">
                                <h3 class="hndle"><span><?php _e('Other Settings', JPCFSM_DOMAIN); ?></span></h3>
                                <div class="inside">
                                    <p>
                                        <label>
                                            <input type="checkbox" name="strip_content" <?php checked($jpcfsm->settings('strip_content'), true); ?>/>
                                            <?php _e('Display only this custom message upon form sent (this will exclude the post/page default content)', JPCFSM_DOMAIN); ?>
                                        </label>
                                    </p>
                                </div>
                            </div>

                            <div class="postbox">
                                <h3 class="hndle"><?php _e('Save Changes', JPCFSM_DOMAIN); ?></h3>
                                <div class="inside">
                                    <p>
                                        <?php wp_nonce_field('jpcfm_update', 'jpcfm_nonce'); ?>
                                        <input type="submit" name="submit" class="button button-primary" value="<?php _e('Save Changes', JPCFSM_DOMAIN); ?>" />
                                    </p>
                                </div>
                            </div>

                        </div>
                    </div>
                </form>

            </div>

            <div class="jpcfsm-right">
                <div class="jcfsm_right">

                    <h3><?php _e('Stay tuned to updates', JPCFSM_DOMAIN); ?></h3>
                    <p><i><?php _e('Join our mailing list today for more free and premium WordPress plugins!', JPCFSM_DOMAIN); ?></i><p>
                    <form action="//samelh.us12.list-manage.com/subscribe/post?u=677d27f6f70087b832c7d6b67&amp;id=7b65601974" method="post" name="mc-embedded-subscribe-form" class="validate" target="_blank" novalidate="">
                        <label><strong><?php _e('Email:', JPCFSM_DOMAIN); ?></strong><br/>
                            <input type="email" value="<?php echo $current_user->user_email; ?>" name="EMAIL" class="required email" id="mce-EMAIL" />
                        </label>
                        <br/>
                        <label><strong><?php _e('Your name:', JPCFSM_DOMAIN); ?></strong><br/>
                            <input type="text" value="<?php echo $current_user->user_nicename; ?>" name="FNAME" class="" id="mce-FNAME" />
                        </label>
                        <br/>
                        <input type="submit" value="<?php esc_attr_e('Subscribe', JPCFSM_DOMAIN); ?>" name="subscribe" id="mc-embedded-subscribe" class="button" />
                    </form>
                    <p><hr/></p>

                    <h3><?php _e('Are you looking for help?', JPCFSM_DOMAIN); ?></h3>
                    <p><?php _e('Don\'t worry, we got you covered:', JPCFSM_DOMAIN); ?></p>
                    <li><a href="https://wordpress.org/support/plugin/jetpack-contact-form-success-message"><?php _e('Go to plugin support forums on WordPress', JPCFSM_DOMAIN); ?></a></li>
                    <li><a href="http://blog.samelh.com/"><?php _e('Browse our blog for tutorials', JPCFSM_DOMAIN); ?></a></li>
                    <p><hr/></p>

                    <p>
                        <li><a href="https://wordpress.org/support/view/plugin-reviews/jetpack-contact-form-success-message?rate=5#postform"><?php _e('Rate &amp; review this plugin? &#9733;&#9733;&#9733;&#9733;&#9733;', JPCFSM_DOMAIN); ?></a></li>
                        <li><a href="https://twitter.com/samuel_elh"><?php _e('Follow @Samuel_Elh on Twitter', JPCFSM_DOMAIN); ?></a></li>
                    </p>

                </div>
            </div>

        </div>

        <?php
    }
}
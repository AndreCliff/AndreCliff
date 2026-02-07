<?php
/**
 * Admin settings page for managing the Retro Tape Deck track list.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class RTD_Admin {

    public function __construct() {
        add_action( 'admin_menu', array( $this, 'add_menu_page' ) );
        add_action( 'admin_init', array( $this, 'register_settings' ) );
    }

    /**
     * Add a settings page under the Settings menu.
     */
    public function add_menu_page() {
        add_options_page(
            __( 'Retro Tape Deck', 'retro-tape-deck' ),
            __( 'Tape Deck Player', 'retro-tape-deck' ),
            'manage_options',
            'retro-tape-deck',
            array( $this, 'render_page' )
        );
    }

    /**
     * Register plugin settings.
     */
    public function register_settings() {
        register_setting( 'rtd_settings_group', 'rtd_tracks', array(
            'type'              => 'string',
            'sanitize_callback' => array( $this, 'sanitize_tracks' ),
            'default'           => '[]',
        ) );

        register_setting( 'rtd_settings_group', 'rtd_brand_name', array(
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => 'RetroSound',
        ) );

        register_setting( 'rtd_settings_group', 'rtd_model_name', array(
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => 'Dual Cassette Stereo System',
        ) );
    }

    /**
     * Sanitize the JSON tracks input.
     */
    public function sanitize_tracks( $input ) {
        $decoded = json_decode( $input, true );
        if ( ! is_array( $decoded ) ) {
            add_settings_error( 'rtd_tracks', 'invalid_json', __( 'Invalid JSON in track list.', 'retro-tape-deck' ) );
            return get_option( 'rtd_tracks', '[]' );
        }
        $clean = array();
        foreach ( $decoded as $track ) {
            if ( empty( $track['url'] ) || empty( $track['title'] ) ) {
                continue;
            }
            $clean[] = array(
                'title' => sanitize_text_field( $track['title'] ),
                'url'   => esc_url_raw( $track['url'] ),
            );
        }
        return wp_json_encode( $clean );
    }

    /**
     * Render the settings page.
     */
    public function render_page() {
        $tracks     = get_option( 'rtd_tracks', '[]' );
        $brand      = get_option( 'rtd_brand_name', 'RetroSound' );
        $model      = get_option( 'rtd_model_name', 'Dual Cassette Stereo System' );
        $decoded    = json_decode( $tracks, true );
        if ( ! is_array( $decoded ) ) {
            $decoded = array();
        }
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Retro Tape Deck Player Settings', 'retro-tape-deck' ); ?></h1>

            <form method="post" action="options.php">
                <?php settings_fields( 'rtd_settings_group' ); ?>

                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="rtd_brand_name"><?php esc_html_e( 'Brand Name', 'retro-tape-deck' ); ?></label>
                        </th>
                        <td>
                            <input type="text" id="rtd_brand_name" name="rtd_brand_name"
                                   value="<?php echo esc_attr( $brand ); ?>" class="regular-text" />
                            <p class="description"><?php esc_html_e( 'The name displayed on the deck (e.g. RetroSound, SoundWave).', 'retro-tape-deck' ); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="rtd_model_name"><?php esc_html_e( 'Model Name', 'retro-tape-deck' ); ?></label>
                        </th>
                        <td>
                            <input type="text" id="rtd_model_name" name="rtd_model_name"
                                   value="<?php echo esc_attr( $model ); ?>" class="regular-text" />
                            <p class="description"><?php esc_html_e( 'Sub-label under the brand (e.g. Dual Cassette Stereo System).', 'retro-tape-deck' ); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="rtd_tracks_field"><?php esc_html_e( 'Track List (JSON)', 'retro-tape-deck' ); ?></label>
                        </th>
                        <td>
                            <textarea id="rtd_tracks_field" name="rtd_tracks" rows="12" cols="70"
                                      class="large-text code"><?php echo esc_textarea( wp_json_encode( $decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) ); ?></textarea>
                            <p class="description">
                                <?php esc_html_e( 'Enter tracks as a JSON array. Each object needs "title" and "url" keys.', 'retro-tape-deck' ); ?><br>
                                <code>[{"title": "Track Name", "url": "https://example.com/track.mp3"}]</code>
                            </p>
                        </td>
                    </tr>
                </table>

                <h3><?php esc_html_e( 'Usage', 'retro-tape-deck' ); ?></h3>
                <p><?php esc_html_e( 'Place the following shortcode on any page or post:', 'retro-tape-deck' ); ?></p>
                <p><code>[retro_tape_deck]</code></p>
                <p><?php esc_html_e( 'You can also pass tracks directly via shortcode attributes (overrides settings):', 'retro-tape-deck' ); ?></p>
                <p><code>[retro_tape_deck tracks='[{"title":"Song","url":"https://example.com/song.mp3"}]']</code></p>

                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }
}

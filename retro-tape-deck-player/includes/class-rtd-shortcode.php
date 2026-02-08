<?php
/**
 * Renders the [retro_tape_deck] shortcode.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class RTD_Shortcode {

    /**
     * Render the single tape deck player.
     *
     * @param array $atts Shortcode attributes.
     * @return string HTML output.
     */
    public static function render( $atts ) {
        $atts = shortcode_atts( array(
            'tracks' => '',
            'brand'  => '',
            'model'  => '',
        ), $atts, 'retro_tape_deck' );

        // Resolve tracks: shortcode attr > plugin settings
        if ( ! empty( $atts['tracks'] ) ) {
            $tracks = json_decode( $atts['tracks'], true );
        } else {
            $tracks = json_decode( get_option( 'rtd_tracks', '[]' ), true );
        }

        if ( ! is_array( $tracks ) ) {
            $tracks = array();
        }

        $brand = ! empty( $atts['brand'] ) ? $atts['brand'] : get_option( 'rtd_brand_name', 'RetroSound' );
        $model = ! empty( $atts['model'] ) ? $atts['model'] : get_option( 'rtd_model_name', 'Cassette Player' );

        // Sanitise track data for JSON embed
        $safe_tracks = array();
        foreach ( $tracks as $t ) {
            if ( empty( $t['url'] ) || empty( $t['title'] ) ) {
                continue;
            }
            $safe_tracks[] = array(
                'title' => sanitize_text_field( $t['title'] ),
                'url'   => esc_url( $t['url'] ),
            );
        }

        ob_start();
        ?>
        <div class="rtd-player">

            <!-- Top bar: Power | Brand | Counter -->
            <div class="rtd-top-bar">
                <div class="rtd-power-section">
                    <span class="rtd-power-label">Power</span>
                    <span class="rtd-led"></span>
                </div>
                <div class="rtd-brand">
                    <div class="rtd-brand-name"><?php echo esc_html( $brand ); ?></div>
                    <div class="rtd-brand-model"><?php echo esc_html( $model ); ?></div>
                </div>
                <div class="rtd-counter-section">
                    <div class="rtd-counter-label">Counter</div>
                    <span class="rtd-display-time" style="font-family:'Courier New',monospace;font-size:13px;color:#5a5248;">0:00</span>
                </div>
            </div>

            <!-- Cassette window -->
            <div class="rtd-cassette-window">
                <div class="rtd-cassette">
                    <!-- Label -->
                    <div class="rtd-cassette-label">
                        <div class="rtd-cassette-label-lines"></div>
                        <span class="rtd-cassette-side">A Side</span>
                        <div class="rtd-cassette-type">
                            <span>Noise Reduction</span>
                            <span>In / Out</span>
                        </div>
                    </div>
                    <!-- Reels -->
                    <div class="rtd-reels-area">
                        <div class="rtd-reel">
                            <div class="rtd-reel-inner"></div>
                            <div class="spoke-3"></div>
                        </div>
                        <div class="rtd-tape-bridge"></div>
                        <div class="rtd-reel">
                            <div class="rtd-reel-inner"></div>
                            <div class="spoke-3"></div>
                        </div>
                    </div>
                    <!-- Guide posts -->
                    <div class="rtd-cassette-guides">
                        <div class="rtd-guide-post"></div>
                        <div class="rtd-guide-post"></div>
                    </div>
                </div>
            </div>

            <!-- LCD display -->
            <div class="rtd-display">
                <span class="rtd-display-track">-- NO TAPE --</span>
                <span class="rtd-display-time">0:00</span>
            </div>

            <!-- VU meters -->
            <div class="rtd-vu-container">
                <span class="rtd-vu-label">L</span>
                <div class="rtd-vu-meter rtd-vu-left">
                    <?php for ( $i = 0; $i < 14; $i++ ) : ?>
                        <div class="vu-bar"></div>
                    <?php endfor; ?>
                </div>
                <span class="rtd-vu-label">R</span>
                <div class="rtd-vu-meter rtd-vu-right">
                    <?php for ( $i = 0; $i < 14; $i++ ) : ?>
                        <div class="vu-bar"></div>
                    <?php endfor; ?>
                </div>
            </div>

            <!-- Transport controls -->
            <div class="rtd-controls-section">
                <div class="rtd-controls">
                    <button class="rtd-btn rtd-btn-stop" title="Stop/Eject">
                        <div class="icon-stop"></div>
                        <span class="rtd-btn-label">Stop</span>
                    </button>
                    <button class="rtd-btn rtd-btn-prev" title="Previous Track">
                        <div class="icon-prev"><span></span><span></span></div>
                        <span class="rtd-btn-label">Prev</span>
                    </button>
                    <button class="rtd-btn rtd-btn-rew" title="Rewind">
                        <div class="icon-rew"><span></span><span></span></div>
                        <span class="rtd-btn-label">Rew</span>
                    </button>
                    <button class="rtd-btn rtd-btn-ff" title="Fast Forward">
                        <div class="icon-ff"><span></span><span></span></div>
                        <span class="rtd-btn-label">F.Fwd</span>
                    </button>
                    <button class="rtd-btn rtd-btn-next" title="Next Track">
                        <div class="icon-next"><span></span><span></span></div>
                        <span class="rtd-btn-label">Next</span>
                    </button>
                    <button class="rtd-btn rtd-btn-play" title="Play">
                        <div class="icon-play"></div>
                        <span class="rtd-btn-label">Play</span>
                    </button>
                    <button class="rtd-btn rtd-btn-pause" title="Pause">
                        <div class="icon-pause"><span></span><span></span></div>
                        <span class="rtd-btn-label">Pause</span>
                    </button>
                    <button class="rtd-btn rtd-btn-eject" title="Eject">
                        <div class="icon-eject"><span></span><span></span></div>
                        <span class="rtd-btn-label">Eject</span>
                    </button>
                </div>
            </div>

            <!-- Volume & progress -->
            <div class="rtd-sliders-row">
                <div class="rtd-slider-group">
                    <label for="rtd-vol-<?php echo esc_attr( wp_unique_id() ); ?>">Volume</label>
                    <input type="range" class="rtd-slider rtd-volume" min="0" max="100" value="75" />
                </div>
                <div class="rtd-progress-wrap">
                    <label>Position</label>
                    <input type="range" class="rtd-progress-bar" min="0" max="100" value="0" />
                </div>
            </div>

            <!-- Playlist drawer -->
            <button class="rtd-playlist-toggle">&#9654; Playlist</button>
            <div class="rtd-playlist">
                <ol></ol>
            </div>

            <!-- Footer -->
            <div class="rtd-footer">
                <span class="rtd-footer-left">Auto Reverse &bull; Noise Reduction</span>
                <span class="rtd-footer-right">Stereo &bull; Normal / CrO&#8322;</span>
            </div>

            <!-- Track data (consumed by JS) -->
            <script type="application/json" class="rtd-track-data"><?php echo wp_json_encode( $safe_tracks ); ?></script>
        </div>
        <?php
        return ob_get_clean();
    }
}

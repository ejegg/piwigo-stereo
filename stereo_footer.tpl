<p>
    {$STEREO_FORMAT}
    <label for="gif_radio">{$STEREO_FORMAT_GIF}
        <input type="radio" value="gif" name="stereo_format" id="gif_radio" {$GIF_SELECTED} />
    </label>
    <label for="cross_radio">{$STEREO_FORMAT_CROSS_EYED}
        <input type="radio" value="cross" name="stereo_format" id="cross_radio" {$CROSS_SELECTED} />
    </label>
    <label for="wall_radio">{$STEREO_FORMAT_WALL_EYED}
        <input type="radio" value="wall" name="stereo_format" id="wall_radio" {$WALL_SELECTED} />
    </label>
</p>
<script type="text/javascript" src="{$REL_DIR}/js.cookie.js"></script>
<script type="text/javascript">
    var i, inputs = document.getElementsByName('stereo_format');
    for( i = 0; i < inputs.length; i++ ) {
        inputs[i].addEventListener( 'change', function( e ) {
            if ( this.checked ) {
                Cookies.set( 'piwigo_stereo_mode', this.value );
                document.location.reload();
            }
        } );
    }
</script>

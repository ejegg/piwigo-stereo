<img src="{$GIF_URL}" id="stereoGif" />
<script type="text/javascript" src="{$REL_DIR}/libgif.js" ></script>
<script type="text/javascript" src="{$REL_DIR}/hammer.js" ></script>
<script type="text/javascript" src="{$REL_DIR}/wiggleAdjust.js" ></script>
<script type="text/javascript">
    var img = document.getElementById('stereoGif');
    var superG = new SuperGif({ldelim} gif:img {rdelim});
    var adjust = new WiggleAdjust(superG, {$WIGGLE_PARAMS});
    superG.load( adjust.attach );
</script>

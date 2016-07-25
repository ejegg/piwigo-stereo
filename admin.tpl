
<h2>{$TITLE} &#8250; {'Edit photo'|@translate} {$TABSHEET_TITLE}</h2>

<p>{'STEREO_INSTRUCTION'|@translate}</p>
<form id="stereoForm" method="post" action="{$F_ACTION}">
    <input type="hidden" name="offsetX" id="offsetX" value="{$OFFSET_X}">
    <input type="hidden" name="offsetY" id="offsetY" value="{$OFFSET_Y}">
    <input type="hidden" name="pwg_token" value="{$PWG_TOKEN}">
<p>
    <input class="submit" type="submit" value="{'Save Settings'|@translate}" name="submit"/>
</p>
</form>
{$PICTURE}
<script type="text/javascript">
    var offset, form = document.forms.stereoForm;
    form.onsubmit = function() {
        offset = adjust.getOffset();
        form.offsetX.value = offset.x;
        form.offsetY.value = offset.y;
    }
</script>

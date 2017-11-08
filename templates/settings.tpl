{ft_include file='modules_header.tpl'}

<table cellpadding="0" cellspacing="0">
    <tr>
        <td width="45"><a href="index.php"><img src="images/icon_visualization.png" border="0" width="34" height="34"/></a>
        </td>
        <td class="title">
            <a href="../../admin/modules">{$LANG.word_modules}</a>
            <span class="joiner">&raquo;</span>
            <a href="./">{$L.module_name}</a>
            <span class="joiner">&raquo;</span>
            {$LANG.phrase_main_settings}
        </td>
    </tr>
</table>

{ft_include file='messages.tpl'}

<div class="margin_bottom_large">
    {$L.text_activity_chart_intro}
</div>

<form method="post" action="{$same_page}" onsubmit="return rsv.validate(this, rules)">
    <table cellspacing="0" cellpadding="1" class="list_table">
        <tr>
            <td width="230" class="pad_left_small">{$L.phrase_quicklinks_dialog_default_dimensions}</td>
            <td class="pad_left_small">
                <table cellspacing="0" cellpadding="0">
                    <tr>
                        <td width="60" class="medium_grey"><label for="width">{$L.word_width}</label></td>
                        <td><input type="text" name="quicklinks_dialog_width" id="width" size="3"
                                   value="{$module_settings.quicklinks_dialog_width}"/>px
                        </td>
                    </tr>
                    <tr>
                        <td class="medium_grey"><label for="height">{$L.word_height}</label></td>
                        <td><input type="text" name="quicklinks_dialog_height" id="height" size="3"
                                   value="{$module_settings.quicklinks_dialog_height}"/>px
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td class="pad_left_small">{$L.phrase_vis_thumb_size}</td>
            <td>
                <input type="text" name="quicklinks_dialog_thumb_size" id="quicklinks_dialog_thumb_size" size="3"
                       value="{$module_settings.quicklinks_dialog_thumb_size}"/>px
            </td>
        </tr>
        <tr>
            <td class="pad_left_small">{$L.phrase_default_cache_frequency}</td>
            <td>
                {cache_frequency_dropdown name_id="default_cache_frequency" default=$module_settings.default_cache_frequency}
                <div class="hint">
                    {$L.text_cache_frequency_explanation}
                </div>
            </td>
        </tr>
        <tr>
            <td class="pad_left_small">{$L.phrase_hide_visualizations_from_client_accounts}</td>
            <td>
                <input type="radio" name="hide_from_client_accounts" id="hfca1" value="yes"
                       {if $module_settings.hide_from_client_accounts == "yes"}checked{/if} />
                <label for="hfca1">{$LANG.word_yes}</label>
                <input type="radio" name="hide_from_client_accounts" id="hfca2" value="no"
                       {if $module_settings.hide_from_client_accounts == "no"}checked{/if} />
                <label for="hfca2">{$LANG.word_no}</label>
            </td>
        </tr>
        <tr>
            <td class="pad_left_small">{$L.phrase_allow_clients_refresh_cache}</td>
            <td>
                <input type="radio" name="clients_may_refresh_cache" id="cmrc1" value="yes"
                       {if $module_settings.clients_may_refresh_cache == "yes"}checked{/if} />
                <label for="cmrc1">{$LANG.word_yes}</label>
                <input type="radio" name="clients_may_refresh_cache" id="cmrc2" value="no"
                       {if $module_settings.clients_may_refresh_cache == "no"}checked{/if} />
                <label for="cmrc2">{$LANG.word_no}</label>
            </td>
        </tr>
    </table>

    <p>
        <input type="submit" name="update" value="{$LANG.word_update}"/>
        <input type="submit" name="clear_cache" value="{$L.phrase_clear_visualization_cache}"/>
    </p>

</form>

{ft_include file='modules_footer.tpl'}

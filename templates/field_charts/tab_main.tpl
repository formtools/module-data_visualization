<form action="{$same_page}" method="post" onsubmit="return rsv.validate(this, rules)">
    <input type="hidden" name="vis_id" value="{$vis_info.vis_id}"/>
    <input type="hidden" name="tab" value="main"/>

    <div class="subtitle underline margin_top_large">{$LANG.phrase_main_settings|upper}</div>

    {ft_include file="messages.tpl"}

    <table cellspacing="1" cellpadding="0" class="list_table">
        <tr>
            <td class="pad_left_small" width="180">{$L.phrase_visualization_name}</td>
            <td>
                <input type="text" name="vis_name" id="vis_name" value="{$vis_info.vis_name|escape}"
                       style="width: 99%"/>
            </td>
        </tr>
        <tr>
            <td class="pad_left_small">{$LANG.word_form}</td>
            <td>
                {forms_dropdown name_id="form_id" include_blank_option=true default=$vis_info.form_id}
            </td>
        </tr>
        <tr>
            <td class="pad_left_small">{$LANG.word_view}</td>
            <td>
                {views_dropdown form_id=$vis_info.form_id name_id="view_id" selected=$vis_info.view_id omit_hidden_views=true}
            </td>
        </tr>
        <tr>
            <td class="pad_left_small">{$LANG.word_field}</td>
            <td>
                {chart_form_field_dropdown name_id="field_id" form_id=$vis_info.form_id selected=$vis_info.field_id}
            </td>
        </tr>
        <tr>
            <td class="pad_left_small">{$L.phrase_cache_update_frequency}</td>
            <td>
                {cache_frequency_dropdown name_id="cache_update_frequency" default=$vis_info.cache_update_frequency}
                <div class="hint">
                    {$L.text_cache_frequency_explanation}
                </div>
            </td>
        </tr>
    </table>

    <p>
        <input type="button" id="delete_visualization" value="{$L.phrase_delete_visualization}" class="burgundy right"/>
        <input type="submit" name="update" value="{$LANG.word_update}"/>
    </p>

</form>

{ft_include file='modules_header.tpl'}

<table cellpadding="0" cellspacing="0">
    <tr>
        <td width="45"><a href="../index.php"><img src="../images/icon_visualization.png" border="0" width="34"
                                                   height="34"/></a></td>
        <td class="title">
            <a href="../../../admin/modules">{$LANG.word_modules}</a>
            <span class="joiner">&raquo;</span>
            <a href="../">{$L.module_name}</a>
            <span class="joiner">&raquo;</span>
            {$L.phrase_default_field_chart_settings}
        </td>
    </tr>
</table>

{ft_include file='messages.tpl'}

{include file="../../no_internet_connection.tpl"}

<div class="margin_bottom_large">
    {$L.text_default_field_chart_settings_page}
</div>

<form method="post" action="{$same_page}">

    <table cellspacing="0" cellpadding="0" width="100%" class="margin_bottom_large">
        <tr>
            <td valign="top">
                <div class="subtitle underline margin_bottom_large">{$L.phrase_default_settings|upper}</div>

                <table cellspacing="0" cellpadding="1" class="list_table margin_bottom_large">
                    <tr>
                        <td width="190" class="pad_left_small">{$L.phrase_chart_type}</td>
                        <td>
                            <input type="radio" name="field_chart_default_chart_type" id="ct1" value="pie_chart"
                                   {if $module_settings.field_chart_default_chart_type == "pie_chart"}checked{/if} />
                            <label for="ct1">{$L.phrase_pie_chart}</label>
                            <input type="radio" name="field_chart_default_chart_type" id="ct2" value="bar_chart"
                                   {if $module_settings.field_chart_default_chart_type == "bar_chart"}checked{/if} />
                            <label for="ct2">{$L.phrase_bar_chart}</label>
                            <input type="radio" name="field_chart_default_chart_type" id="ct3" value="column_chart"
                                   {if $module_settings.field_chart_default_chart_type == "column_chart"}checked{/if} />
                            <label for="ct3">{$L.phrase_column_chart}</label>
                        </td>
                    </tr>
                    <tr>
                        <td class="pad_left_small">{$L.phrase_ignore_fields_with_empty_vals}</td>
                        <td>
                            <input type="radio" name="field_chart_ignore_empty_fields" id="ief1" value="yes"
                                   {if $module_settings.field_chart_ignore_empty_fields == "yes"}checked{/if} />
                            <label for="ief1">{$LANG.word_yes}</label>
                            <input type="radio" name="field_chart_ignore_empty_fields" id="ief2" value="no"
                                   {if $module_settings.field_chart_ignore_empty_fields == "no"}checked{/if} />
                            <label for="ief2">{$LANG.word_no}</label>
                        </td>
                    </tr>
                    <tr>
                        <td class="pad_left_small">{$L.word_colour}</td>
                        <td>
                            {colour_dropdown name_id="field_chart_colour" default=$module_settings.field_chart_colour}
                        </td>
                    </tr>
                </table>

                <div class="subtitle underline margin_bottom_large">{$L.phrase_pie_chart_settings|upper}</div>

                <table cellspacing="0" cellpadding="1" class="list_table">
                    <tr>
                        <td width="190" class="pad_left_small">{$L.phrase_pie_chart_format}</td>
                        <td>
                            <input type="radio" name="field_chart_pie_chart_format" id="pcf1" value="2D"
                                   {if $module_settings.field_chart_pie_chart_format == "2D"}checked{/if}
                                    {if $module_settings.field_chart_default_chart_type != "pie_chart"}disabled{/if} />
                            <label for="pcf1">2D</label>
                            <input type="radio" name="field_chart_pie_chart_format" id="pcf2" value="3D"
                                   {if $module_settings.field_chart_pie_chart_format == "3D"}checked{/if}
                                    {if $module_settings.field_chart_default_chart_type != "pie_chart"}disabled{/if} />
                            <label for="pcf2">3D</label>
                        </td>
                    </tr>
                    <tr>
                        <td class="pad_left_small">{$L.phrase_include_legend_in_thumbnail}</td>
                        <td>
                            <input type="radio" name="field_chart_include_legend_quicklinks" id="ilq1" value="yes"
                                   {if $module_settings.field_chart_include_legend_quicklinks == "yes"}checked{/if}
                                    {if $module_settings.field_chart_default_chart_type != "pie_chart"}disabled{/if} />
                            <label for="ilq1">{$LANG.word_yes}</label>
                            <input type="radio" name="field_chart_include_legend_quicklinks" id="ilq2" value="no"
                                   {if $module_settings.field_chart_include_legend_quicklinks == "no"}checked{/if}
                                    {if $module_settings.field_chart_default_chart_type != "pie_chart"}disabled{/if} />
                            <label for="ilq2">{$LANG.word_no}</label>
                        </td>
                    </tr>
                    <tr>
                        <td class="pad_left_small">{$L.phrase_include_legend_in_full_size}</td>
                        <td>
                            <input type="radio" name="field_chart_include_legend_full_size" id="ilf1" value="yes"
                                   {if $module_settings.field_chart_include_legend_full_size == "yes"}checked{/if}
                                    {if $module_settings.field_chart_default_chart_type != "pie_chart"}disabled{/if} />
                            <label for="ilf1">{$LANG.word_yes}</label>
                            <input type="radio" name="field_chart_include_legend_full_size" id="ilf2" value="no"
                                   {if $module_settings.field_chart_include_legend_full_size == "no"}checked{/if}
                                    {if $module_settings.field_chart_default_chart_type != "pie_chart"}disabled{/if} />
                            <label for="ilf2">{$LANG.word_no}</label>
                        </td>
                    </tr>
                </table>
            </td>
            <td width="250" valign="top">
                <div class="subtitle underline margin_bottom_large">{$L.phrase_thumbnail_example|upper}</div>
                <div id="thumb_chart"></div>
            </td>
        </tr>
    </table>

    <div class="subtitle underline margin_bottom_large">{$L.phrase_full_size_example|upper}</div>
    <div id="full_size_chart"></div>

    <p>
        <input type="submit" name="update" value="{$LANG.word_update}"/>
    </p>

</form>

{ft_include file='modules_footer.tpl'}

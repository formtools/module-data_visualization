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
            {$L.phrase_default_activity_chart_settings}
        </td>
    </tr>
</table>

{ft_include file='messages.tpl'}

{include file="../../no_internet_connection.tpl"}

<div class="margin_bottom_large">
    {$L.text_default_activity_chart_settings_page}
</div>

<form method="post" action="{$same_page}">

    <table cellspacing="0" cellpadding="0" width="100%" class="margin_bottom_large">
        <tr>
            <td valign="top">

                <div class="subtitle underline margin_bottom_large">{$L.phrase_default_settings|upper}</div>

                <table cellspacing="0" cellpadding="1" class="list_table">
                    <tr>
                        <td class="pad_left_small">{$L.phrase_date_range_to_display}</td>
                        <td>
                            {chart_date_range name_id="date_range" default=$module_settings.activity_chart_date_range}
                        </td>
                    </tr>
                    <tr>
                        <td class="pad_left_small">{$L.phrase_group_submission_count_by}</td>
                        <td>
                            <input type="radio" name="submission_count_group" id="scd1" value="month"
                                   {if $module_settings.activity_chart_submission_count_group == "month"}checked{/if} />
                            <label for="scd1">{$L.word_month}</label>
                            <input type="radio" name="submission_count_group" id="scd3" value="day"
                                   {if $module_settings.activity_chart_submission_count_group == "day"}checked{/if} />
                            <label for="scd3">{$L.word_day}</label>
                        </td>
                    </tr>
                    <tr>
                        <td class="pad_left_small">{$L.phrase_chart_type}</td>
                        <td>
                            <input type="radio" name="chart_type" id="lc1" value="line_chart"
                                   {if $module_settings.activity_chart_default_chart_type == "line_chart"}checked{/if} />
                            <label for="lc1">{$L.phrase_line_chart}</label>
                            <input type="radio" name="chart_type" id="lc2" value="area_chart"
                                   {if $module_settings.activity_chart_default_chart_type == "area_chart"}checked{/if} />
                            <label for="lc2">{$L.phrase_area_chart}</label>
                            <input type="radio" name="chart_type" id="lc3" value="column_chart"
                                   {if $module_settings.activity_chart_default_chart_type == "column_chart"}checked{/if} />
                            <label for="lc3">{$L.phrase_column_chart}</label>
                        </td>
                    </tr>
                    <tr>
                        <td class="pad_left_small">{$L.word_colour}</td>
                        <td>
                            {colour_dropdown name_id="colour" default=$module_settings.activity_chart_colour}
                        </td>
                    </tr>
                    <tr>
                        <td class="pad_left_small">{$L.phrase_line_width}</td>
                        <td>
                            {line_width_dropdown name_id="line_width" default=$module_settings.activity_chart_line_width}
                            px
                            <div class="hint">This is for Line and Area charts only.</div>
                        </td>
                    </tr>
                </table>
            </td>
            <td width="250" valign="top">
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

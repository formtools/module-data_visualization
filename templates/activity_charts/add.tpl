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
            {$L.phrase_create_new_activity_chart}
        </td>
    </tr>
</table>

{if $g_message}

    {ft_include file="messages.tpl"}
    <div><b>{$L.word_actions}</b></div>
    <ul>
        <li><a href="../">{$L.phrase_list_visualizations}</a></li>
        <li><a href="add.php">{$L.phrase_create_a_new_activity_chart}</a></li>
        {if $g_success}
            <li><a href="edit.php?page=main&vis_id={$vis_id}">{$L.phrase_edit_the_activity_chart}</a></li>
        {/if}
        {if $form_id && $view_id}
            <li>
                <a href="../../../admin/forms/submissions.php?form_id={$form_id}&view_id={$view_id}">{$L.phrase_view_form_submissions}</a>
            </li>
        {/if}
    </ul>
{else}

    {ft_include file="messages.tpl"}
    {include file="../../no_internet_connection.tpl"}
    <div class="margin_bottom_large">
        {$L.text_add_visualization}
    </div>
    <form action="{$same_page}" method="post" onsubmit="return rsv.validate(this, rules)">
        <div class="subtitle underline margin_bottom_large">{$LANG.phrase_main_settings|upper}</div>

        <table cellspacing="1" cellpadding="0" class="list_table margin_bottom_large">
            <tr>
                <td class="pad_left_small" width="180">{$L.phrase_visualization_name}</td>
                <td>
                    <input type="text" name="vis_name" id="vis_name" value="Form Activity" style="width: 99%"/>
                </td>
            </tr>
            <tr>
                <td class="pad_left_small">{$LANG.word_form}</td>
                <td>
                    {forms_dropdown name_id="form_id" include_blank_option=true}
                </td>
            </tr>
        </table>

        <table cellspacing="0" cellpadding="0" width="100%">
            <tr>
                <td valign="top">

                    <div class="subtitle underline margin_bottom_large">{$L.word_appearance|upper}</div>

                    <table cellspacing="0" cellpadding="1" class="list_table margin_bottom_large">
                        <tr>
                            <td class="pad_left_small">Date Range to display</td>
                            <td>
                                {chart_date_range name_id="date_range" default=$module_settings.activity_chart_date_range}
                            </td>
                        </tr>
                        <tr>
                            <td class="pad_left_small">Group submission count by</td>
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
                                {* {if $module_settings.activity_chart_default_chart_type == "column_chart"}disabled="disabled"{/if} *}
                                {line_width_dropdown name_id="line_width" default=$module_settings.activity_chart_line_width}
                                px
                                <div class="hint">This is for Line and Area charts only.</div>
                            </td>
                        </tr>
                    </table>
                </td>
                <td width="250" valign="top">
                    <div class="subtitle underline margin_bottom_large">{$L.word_thumbnail}</div>
                    <div id="thumb_chart"></div>
                </td>
            </tr>
        </table>

        <div class="subtitle underline margin_bottom_large">{$L.phrase_full_size}</div>
        <div id="full_size_chart"></div>

        <p>
            <input type="submit" name="add" value="{$L.phrase_create_visualization}"/>
        </p>

    </form>
{/if}

{ft_include file='modules_footer.tpl'}

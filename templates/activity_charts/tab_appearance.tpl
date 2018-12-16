{ft_include file="messages.tpl"}

<form action="{$same_page}" method="post">
    <input type="hidden" id="page_type" value="edit"/>
    <input type="hidden" id="has_submissions_in_view" value="{$has_submissions_in_view}"/>
    <input type="hidden" name="vis_id" id="vis_id" value="{$vis_info.vis_id}"/>
    <input type="hidden" name="form_id" id="form_id" value="{$vis_info.form_id}"/>
    <input type="hidden" name="view_id" id="view_id" value="{$vis_info.view_id}"/>
    <input type="hidden" name="vis_name" id="vis_name" value="{$vis_info.vis_name|escape}"/>
    <input type="hidden" name="tab" value="appearance"/>

    <div class="notify margin_bottom_large" style="display:none" id="no_data_message">
        <div style="padding: 6px">
            {if $vis_info.view_id}
                {$L.notify_no_submissions_in_form_view}
            {else}
                {$L.notify_no_submissions_in_form}
            {/if}
            {$L.text_examples_contain_dud_data}
        </div>
    </div>

    {include file="../../no_internet_connection.tpl"}

    <table cellspacing="0" cellpadding="0" width="100%">
        <tr>
            <td valign="top">

                <div class="subtitle underline margin_bottom_large">{$L.word_appearance|upper}</div>

                <table cellspacing="0" cellpadding="1" class="list_table margin_bottom_large">
                    <tr>
                        <td class="pad_left_small">{$L.phrase_date_range_to_display}</td>
                        <td>
                            {chart_date_range name_id="date_range" default=$vis_info.date_range}
                        </td>
                    </tr>
                    <tr>
                        <td class="pad_left_small">{$L.phrase_group_submission_count_by}</td>
                        <td>
                            <input type="radio" name="submission_count_group" id="scd1" value="month"
                                   {if $vis_info.submission_count_group == "month"}checked{/if} />
                            <label for="scd1">{$L.word_month}</label>
                            <input type="radio" name="submission_count_group" id="scd3" value="day"
                                   {if $vis_info.submission_count_group == "day"}checked{/if} />
                            <label for="scd3">{$L.word_day}</label>
                        </td>
                    </tr>
                    <tr>
                        <td class="pad_left_small">{$L.phrase_chart_type}</td>
                        <td>
                            <input type="radio" name="chart_type" id="lc1" value="line_chart"
                                   {if $vis_info.chart_type == "line_chart"}checked{/if} />
                            <label for="lc1">{$L.phrase_line_chart}</label>
                            <input type="radio" name="chart_type" id="lc2" value="area_chart"
                                   {if $vis_info.chart_type == "area_chart"}checked{/if} />
                            <label for="lc2">{$L.phrase_area_chart}</label>
                            <input type="radio" name="chart_type" id="lc3" value="column_chart"
                                   {if $vis_info.chart_type == "column_chart"}checked{/if} />
                            <label for="lc3">{$L.phrase_column_chart}</label>
                        </td>
                    </tr>
                    <tr>
                        <td class="pad_left_small">{$L.word_colour}</td>
                        <td>
                            {colour_dropdown name_id="colour" default=$vis_info.colour}
                        </td>
                    </tr>
                    <tr>
                        <td class="pad_left_small">{$L.phrase_line_width}</td>
                        <td>
                            {line_width_dropdown name_id="line_width" default=$vis_info.line_width} px
                            <div class="hint">{$L.text_line_area_only}</div>
                        </td>
                    </tr>
                </table>
            </td>
            <td width="250" valign="top">
                <div class="subtitle underline margin_bottom_large">{$L.word_thumbnail|upper}</div>
                <div id="thumb_chart">
                    <div class="loading"></div>
                </div>
            </td>
        </tr>
    </table>

    <div class="subtitle underline margin_bottom_large">{$L.phrase_full_size|upper}</div>
    <div id="full_size_chart">
        <div class="loading"></div>
    </div>

    <p>
        <input type="button" id="delete_visualization" value="{$L.phrase_delete_visualization}" class="burgundy right"/>
        <input type="submit" name="update" value="{$LANG.word_update}"/>
    </p>

</form>

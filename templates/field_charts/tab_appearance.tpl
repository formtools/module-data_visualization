{ft_include file="messages.tpl"}

<form action="{$same_page}" method="post">
    <input type="hidden" name="vis_id" id="vis_id" value="{$vis_info.vis_id}"/>
    <input type="hidden" name="form_id" id="form_id" value="{$vis_info.form_id}"/>
    <input type="hidden" name="view_id" id="view_id" value="{$vis_info.view_id}"/>
    <input type="hidden" name="field_id" id="field_id" value="{$vis_info.field_id}"/>
    <input type="hidden" name="vis_name" id="vis_name" value="{$vis_info.vis_name|escape}"/>
    <input type="hidden" name="tab" value="appearance"/>

    {include file="../../no_internet_connection.tpl"}

    <table cellspacing="0" cellpadding="0" width="100%" class="margin_bottom_large">
        <tr>
            <td valign="top">
                <div class="subtitle underline margin_bottom_large">{$L.word_appearance|upper}</div>

                <table cellspacing="0" cellpadding="1" class="list_table margin_bottom_large">
                    <tr>
                        <td class="pad_left_small">{$L.phrase_chart_type}</td>
                        <td>
                            <input type="radio" name="chart_type" id="ct1" value="pie_chart"
                                   {if $vis_info.chart_type == "pie_chart"}checked{/if} />
                            <label for="ct1">{$L.phrase_pie_chart}</label>
                            <input type="radio" name="chart_type" id="ct2" value="bar_chart"
                                   {if $vis_info.chart_type == "bar_chart"}checked{/if} />
                            <label for="ct2">{$L.phrase_bar_chart}</label>
                            <input type="radio" name="chart_type" id="ct3" value="column_chart"
                                   {if $vis_info.chart_type == "column_chart"}checked{/if} />
                            <label for="ct3">{$L.phrase_column_chart}</label>
                        </td>
                    </tr>
                    <tr>
                        <td class="pad_left_small">{$L.phrase_date_range_to_display}</td>
                        <td>
                            {chart_date_range name_id="date_range" default=$vis_info.date_range}
                        </td>
                    </tr>
                    <tr>
                        <td class="pad_left_small">{$L.phrase_ignore_fields_with_empty_vals}</td>
                        <td>
                            <input type="radio" name="field_chart_ignore_empty_fields" id="ief1" value="yes"
                                   {if $vis_info.field_chart_ignore_empty_fields == "yes"}checked{/if} />
                            <label for="ief1">{$LANG.word_yes}</label>
                            <input type="radio" name="field_chart_ignore_empty_fields" id="ief2" value="no"
                                   {if $vis_info.field_chart_ignore_empty_fields == "no"}checked{/if} />
                            <label for="ief2">{$LANG.word_no}</label>
                        </td>
                    </tr>
                    <tr>
                        <td class="pad_left_small">{$L.word_colour}</td>
                        <td>
                            <input type="hidden" name="colour_old" value="{$vis_info.colour}"/>
                            {colour_dropdown name_id="colour" default=$vis_info.colour}
                            <div class="hint">
                                {$L.text_bar_and_col_charts_only}
                            </div>
                        </td>
                    </tr>
                </table>

                <div class="subtitle underline margin_bottom_large">{$L.phrase_pie_chart_settings|upper}</div>

                <table cellspacing="0" cellpadding="1" class="list_table">
                    <tr>
                        <td width="190" class="pad_left_small">{$L.phrase_pie_chart_format}</td>
                        <td>
                            <input type="radio" name="pie_chart_format" id="pcf1" value="2D"
                                   {if $vis_info.pie_chart_format == "2D" || $vis_info.pie_chart_format == ""}checked{/if}
                                    {if $vis_info.chart_type != "pie_chart"}disabled{/if} />
                            <label for="pcf1">2D</label>
                            <input type="radio" name="pie_chart_format" id="pcf2" value="3D"
                                   {if $vis_info.pie_chart_format == "3D"}checked{/if}
                                    {if $vis_info.chart_type != "pie_chart"}disabled{/if} />
                            <label for="pcf2">3D</label>
                        </td>
                    </tr>
                    <tr>
                        <td class="pad_left_small">{$L.phrase_include_legend_in_thumbnail}</td>
                        <td>
                            <input type="radio" name="include_legend_quicklinks" id="ilq1" value="yes"
                                   {if $vis_info.include_legend_quicklinks == "yes"}checked{/if}
                                    {if $vis_info.chart_type != "pie_chart"}disabled{/if} />
                            <label for="ilq1">{$LANG.word_yes}</label>
                            <input type="radio" name="include_legend_quicklinks" id="ilq2" value="no"
                                   {if $vis_info.include_legend_quicklinks == "no" || $vis_info.include_legend_quicklinks == ""}checked{/if}
                                    {if $vis_info.chart_type != "pie_chart"}disabled{/if} />
                            <label for="ilq2">{$LANG.word_no}</label>
                        </td>
                    </tr>
                    <tr>
                        <td class="pad_left_small">{$L.phrase_include_legend_in_full_size}</td>
                        <td>
                            <input type="radio" name="include_legend_full_size" id="ilf1" value="yes"
                                   {if $vis_info.include_legend_full_size == "yes" || $vis_info.include_legend_full_size == ""}checked{/if}
                                    {if $vis_info.chart_type != "pie_chart"}disabled{/if} />
                            <label for="ilf1">{$LANG.word_yes}</label>
                            <input type="radio" name="include_legend_full_size" id="ilf2" value="no"
                                   {if $vis_info.include_legend_full_size == "no"}checked{/if}
                                    {if $vis_info.chart_type != "pie_chart"}disabled{/if} />
                            <label for="ilf2">{$LANG.word_no}</label>
                        </td>
                    </tr>
                </table>

            </td>
            <td width="250" valign="top">
                <div class="subtitle underline margin_bottom_large">&nbsp;{$L.word_thumbnail|upper}</div>
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

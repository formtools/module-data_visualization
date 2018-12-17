{ft_include file='modules_header.tpl'}

<table cellpadding="0" cellspacing="0">
    <tr>
        <td width="45"><a href="index.php"><img src="images/icon_visualization.png" border="0" width="34" height="34"/></a>
        </td>
        <td class="title">
            <a href="../../admin/modules">{$LANG.word_modules}</a>
            <span class="joiner">&raquo;</span>
            {$L.module_name}
        </td>
    </tr>
</table>

{ft_include file='messages.tpl'}

{include file="../no_internet_connection.tpl"}

{if $total_results == 0}
    <div class="notify" class="margin_bottom_large">
        <div style="padding:8px">
            {$L.text_no_visualizations}
        </div>
    </div>
{else}
    <div id="search_form" class=" margin_bottom_large">
        <form action="{$same_page}" method="post">
            <table cellspacing="2" cellpadding="0" id="search_form_table" width="100%">
                <tr>
                    <td width="100" class="medium_grey">{$L.phrase_search_string}</td>
                    <td width="280" class="col2"><input type="text" name="keyword" value="{$keyword|escape}"/></td>
                    <td width="120" class="medium_grey" valign="top" rowspan="2">{$L.phrase_visualization_type}</td>
                    <td rowspan="2" valign="top">
                        <input type="checkbox" name="vis_types[]" id="vt1" value="activity"
                               {if "activity"|in_array:$vis_types}checked{/if} />
                        <label for="vt1">{$L.phrase_activity_charts}</label><br/>
                        <input type="checkbox" name="vis_types[]" id="vt2" value="field"
                               {if "field"|in_array:$vis_types}checked{/if} />
                        <label for="vt2">{$L.phrase_field_charts}</label>
                    </td>
                </tr>
                <tr>
                    <td class="medium_grey">{$LANG.word_form}</td>
                    <td class="col2">{forms_dropdown name_id="dv_search_form_id" default=$search_form_id include_blank_option=true blank_option_label=$L.phrase_all_forms}</td>
                </tr>
                <tr>
                    <td class="medium_grey">{$LANG.word_view}</td>
                    <td class="col2">
                        {if $search_form_id}
                            {views_dropdown form_id=$search_form_id name_id="dv_search_view_id" selected=$search_view_id
                            show_empty_label=true empty_label=$L.phrase_all_views}
                        {else}
                            <select name="view_id" id="view_id" disabled="disabled">
                                <option value="">{$L.phrase_all_views}</option>
                            </select>
                        {/if}
                    </td>
                    <td class="medium_grey">{$L.phrase_chart_type}</td>
                    <td>
                        <select name="dv_search_chart_type">
                            <option value="" {if $chart_type == ""}selected{/if}>{$L.phrase_all_types}</option>
                            <option value="area_chart"
                                    {if $chart_type == "area_chart"}selected{/if}>{$L.word_area}</option>
                            <option value="bar_chart"
                                    {if $chart_type == "bar_chart"}selected{/if}>{$L.word_bar}</option>
                            <option value="column_chart"
                                    {if $chart_type == "column_chart"}selected{/if}>{$L.word_column}</option>
                            <option value="line_chart"
                                    {if $chart_type == "line_chart"}selected{/if}>{$L.word_line}</option>
                            <option value="pie_chart"
                                    {if $chart_type == "pie_chart"}selected{/if}>{$L.word_pie}</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class="medium_grey" valign="top">{$LANG.word_accounts}</td>
                    <td colspan="2">

                        <table>
                            <tr>
                                <td>
                                    <input type="radio" name="account_type" value="admin" id="at1"
                                           {if $account_type == "admin"}checked{/if} /> <label
                                            for="at1">{$LANG.word_administrator}</label>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <input type="radio" name="account_type" value="client" id="at2"
                                           {if $account_type == "client"}checked{/if} /> <label
                                            for="at2">{$LANG.word_clients}</label>
                                    {clients_dropdown name_id="client_id" default=$client_id include_blank_option=true blank_option=$L.phrase_all_accounts}
                                </td>
                            </tr>
                        </table>

                    </td>
                    <td align="right" valign="bottom">
                        <input type="submit" name="search" value="{$LANG.word_search}" class="margin_left"/>
                        <input type="button" name="reset" onclick="window.location='{$same_page}?reset=1'"
                                {if $results|@count < $total_results}
                                    value="{$LANG.phrase_show_all} ({$total_results})" class="bold"
                                {else}
                                    value="{$LANG.phrase_show_all}" class="light_grey" disabled
                                {/if} />
                    </td>
                </tr>
            </table>

        </form>
    </div>
    {if $results|@count == 0}
        <div class="notify" class="margin_bottom_large">
            <div style="padding:8px">
                {$L.text_no_visualization_found_in_search}
            </div>
        </div>
    {else}

        {$pagination}

        {assign var="table_group_id" value="1"}

        {foreach from=$results item=result name=row}
            {assign var=vis_id value=$result.vis_id}
            {assign var=index value=$smarty.foreach.row.index}
            {assign var=count value=$smarty.foreach.row.iteration}

            {* if it's the first row or the start of a new table, open the table & display the headings *}
            {if $count == 1 || $count != 1 && (($count-1) % $num_visualizations_per_page == 0)}

                {if $table_group_id == "1"}
                    {assign var="style" value="display: block"}
                {else}
                    {assign var="style" value="display: none"}
                {/if}

                <div id="page_{$table_group_id}" style="{$style}">

                <table class="list_table" style="width:100%" cellpadding="1" cellspacing="1">
                <tr style="height: 20px;">
                    <th>{$L.phrase_visualization_name}</th>
                    <th>{$LANG.word_form}</th>
                    <th>{$LANG.word_permissions}</th>
                    <th>{$L.phrase_visualization_type}</th>
                    <th>{$L.phrase_chart_type}</th>
                    <th class="edit"></th>
                    <th class="del"></th>
                </tr>
            {/if}
            <tr>
                <td class="pad_left_small">{$result.vis_name}</td>
                <td class="pad_left_small">
                    {if $result.view_id}
                        <a href="../../admin/forms/submissions.php?form_id={$result.form_id}&view_id={$result.view_id}">{display_form_name form_id=$result.form_id}</a>
                    {else}
                        <a href="../../admin/forms/submissions.php?form_id={$result.form_id}">{display_form_name form_id=$result.form_id}</a>
                    {/if}
                </td>
                <td class="pad_left_small">
                    {if $result.access_type == "admin"}
                        <span class="medium_grey">{$L.phrase_admin_only}</span>
                    {elseif $result.access_type == "public"}
                        <span>{$LANG.word_public}</span>
                    {else}
                        <span>{$LANG.word_private}</span>
                    {/if}
                </td>
                <td class="pad_left_small">
                    {if $result.vis_type == "activity"}
                        <span class="blue">{$L.phrase_activity_charts}</span>
                    {else}
                        <span class="purple">{$L.phrase_field_chart}</span>
                    {/if}
                </td>
                <td class="pad_left_small">
                    {if $result.chart_type == "area_chart"}
                        {$L.word_area}
                    {elseif $result.chart_type == "line_chart"}
                        {$L.word_line}
                    {elseif $result.chart_type == "column_chart"}
                        {$L.word_column}
                    {elseif $result.chart_type == "bar_chart"}
                        {$L.word_bar}
                    {elseif $result.chart_type == "pie_chart"}
                        {$L.word_pie}
                    {/if}
                </td>
                <td class="edit">
                    {if $result.vis_type == "activity"}
                        <a href="activity_charts/edit.php?vis_id={$result.vis_id}"></a>
                    {elseif $result.vis_type == "field"}
                        <a href="field_charts/edit.php?vis_id={$result.vis_id}"></a>
                    {/if}
                </td>
                <td class="del"><a href="#" onclick="return vis_ns.delete_visualization({$result.vis_id})"></a></td>
            </tr>
            {if $count != 1 && ($count % $num_visualizations_per_page) == 0}
                </table></div>
                {assign var='table_group_id' value=$table_group_id+1}
            {/if}

        {/foreach}

        {* if the table wasn't closed, close it! *}
        {if ($results|@count % $num_visualizations_per_page) != 0}
            </table></div>
        {/if}

    {/if}
{/if}

<p>
    <input type="button" id="create_visualization" value="{$L.phrase_create_new_visualization}"/>
    {if $results|@count > 0}
        <input type="button" id="view_visualizations" value="{$L.phrase_view_visualizations}"/>
    {/if}
</p>
</form>

<div class="hidden" id="create_visualization_dialog">
    <ul>
        <li>
            <div class="chart_type">
                <div class="dv_create_visualization_heading">{$L.phrase_activity_chart}</div>
                <img src="images/example_area_chart.png"/>
                <input type="hidden" class="visualization_type" value="activity_chart"/>
            </div>
            <div class="comment">
                {$L.text_activity_chart_desc}
            </div>
        </li>
        <li>
            <div class="chart_type">
                <div class="dv_create_visualization_heading">{$L.phrase_field_chart}</div>
                <img src="images/example_pie_chart.png"/>
                <input type="hidden" class="visualization_type" value="field_chart"/>
            </div>
            <div class="comment">
                {$L.text_field_chart_desc}
            </div>
        </li>
    </ul>
</div>

{ft_include file='modules_footer.tpl'}

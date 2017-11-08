{ft_include file='modules_header.tpl'}

  <table cellpadding="0" cellspacing="0">
  <tr>
    <td width="45"><a href="../index.php"><img src="../images/icon_visualization.png" border="0" width="34" height="34" /></a></td>
    <td class="title">
      <a href="../../../admin/modules">{$LANG.word_modules}</a>
      <span class="joiner">&raquo;</span>
      <a href="../">{$L.module_name}</a>
      <span class="joiner">&raquo;</span>
      {$L.phrase_new_field_chart}
    </td>
  </tr>
  </table>

  {if $g_message}

    {ft_include file="messages.tpl"}

    <div><b>{$L.word_actions}</b></div>
    <ul>
      <li><a href="../">{$L.phrase_list_visualizations}</a></li>
      <li><a href="add.php">{$L.phrase_create_new_field_chart}</a></li>
      {if $g_success}
        <li><a href="edit.php?vis_id={$vis_id}">{$L.phrase_edit_this_field_chart}</a></li>
      {/if}
      {if $form_id && $view_id}
        <li><a href="../../../admin/forms/submissions.php?form_id={$form_id}&view_id={$view_id}">{$L.phrase_view_form_submissions}</a></li>
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
	        <input type="text" name="vis_name" id="vis_name" value="" style="width: 99%" />
	      </td>
	    </tr>
	    <tr>
	      <td class="pad_left_small">{$LANG.word_form}</td>
	      <td>
	        {forms_dropdown name_id="form_id" include_blank_option=true}
	      </td>
	    </tr>
	    <tr>
	      <td class="pad_left_small">{$LANG.word_view}</td>
	      <td>
	        <select name="view_id" id="view_id" disabled>
	          <option value="">{$LANG.phrase_please_select_form}</option>
	        </select>
	      </td>
	    </tr>
      <tr>
        <td class="pad_left_small">{$LANG.word_field}</td>
        <td>
          <select name="field_id" id="field_id" disabled>
            <option value="">{$L.phrase_please_select_view}</option>
          </select>
        </td>
      </tr>
	    </table>

	    <table cellspacing="0" cellpadding="0" width="100%" class="margin_bottom_large">
	    <tr>
	      <td valign="top">

	        <div class="subtitle underline margin_bottom_large">{$L.word_appearance|upper}</div>

	        <table cellspacing="0" cellpadding="1" class="list_table margin_bottom_large">
	        <tr>
	          <td class="pad_left_small">{$L.phrase_chart_type}</td>
	          <td>
	            <input type="radio" name="chart_type" id="ct1" value="pie_chart"
	              {if $module_settings.field_chart_default_chart_type == "pie_chart"}checked{/if} />
	              <label for="ct1">{$L.phrase_pie_chart}</label>
	            <input type="radio" name="chart_type" id="ct2" value="bar_chart"
	              {if $module_settings.field_chart_default_chart_type == "bar_chart"}checked{/if} />
	              <label for="ct2">{$L.phrase_bar_chart}</label>
	            <input type="radio" name="chart_type" id="ct3" value="column_chart"
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
              <input type="hidden" name="colour_old" value="{$module_settings.field_chart_colour}" />
              {colour_dropdown name_id="colour" default=$module_settings.field_chart_colour}
            </td>
          </tr>
	        </table>

          <div class="subtitle underline margin_bottom_large">{$L.phrase_pie_chart_settings|upper}</div>

	        <table cellspacing="0" cellpadding="1" class="list_table">
	        <tr>
	          <td width="190" class="pad_left_small">{$L.phrase_pie_chart_format}</td>
	          <td>
	            <input type="radio" name="pie_chart_format" id="pcf1" value="2D"
	              {if $module_settings.field_chart_pie_chart_format == "2D"}checked{/if}
	              {if $module_settings.field_chart_default_chart_type != "pie_chart"}disabled{/if} />
	              <label for="pcf1">2D</label>
	            <input type="radio" name="pie_chart_format" id="pcf2" value="3D"
	              {if $module_settings.field_chart_pie_chart_format == "3D"}checked{/if}
	              {if $module_settings.field_chart_default_chart_type != "pie_chart"}disabled{/if} />
	              <label for="pcf2">3D</label>
	          </td>
	        </tr>
	        <tr>
	          <td class="pad_left_small">{$L.phrase_include_legend_in_thumbnail}</td>
	          <td>
	            <input type="radio" name="include_legend_quicklinks" id="ilq1" value="yes"
	              {if $module_settings.field_chart_include_legend_quicklinks == "yes"}checked{/if}
	              {if $module_settings.field_chart_default_chart_type != "pie_chart"}disabled{/if} />
	              <label for="ilq1">{$LANG.word_yes}</label>
	            <input type="radio" name="include_legend_quicklinks" id="ilq2" value="no"
	              {if $module_settings.field_chart_include_legend_quicklinks == "no"}checked{/if}
	              {if $module_settings.field_chart_default_chart_type != "pie_chart"}disabled{/if} />
	              <label for="ilq2">{$LANG.word_no}</label>
	          </td>
	        </tr>
	        <tr>
	          <td class="pad_left_small">{$L.phrase_include_legend_in_full_size}</td>
	          <td>
	            <input type="radio" name="include_legend_full_size" id="ilf1" value="yes"
	              {if $module_settings.field_chart_include_legend_full_size == "yes"}checked{/if}
	              {if $module_settings.field_chart_default_chart_type != "pie_chart"}disabled{/if} />
	              <label for="ilf1">{$LANG.word_yes}</label>
	            <input type="radio" name="include_legend_full_size" id="ilf2" value="no"
	              {if $module_settings.field_chart_include_legend_full_size == "no"}checked{/if}
	              {if $module_settings.field_chart_default_chart_type != "pie_chart"}disabled{/if} />
	              <label for="ilf2">{$LANG.word_no}</label>
	          </td>
	        </tr>
	        </table>

	      </td>
	      <td width="250" valign="top">
          <div class="subtitle underline margin_bottom_large">&nbsp;{$L.word_thumbnail|upper}</div>
	        <div id="thumb_chart" style="display:none">{$L.phrase_select_form_and_field}</div>
	        <span id="thumb_chart_empty" class="medium_grey">&nbsp;{$L.phrase_select_form_and_field}</span>
	      </td>
	    </tr>
	    </table>

      <div class="subtitle underline margin_bottom_large">{$L.phrase_full_size}</div>
      <div id="full_size_chart" style="display:none"></div>
        <span id="full_size_chart_empty" class="medium_grey">{$L.phrase_select_form_and_field}</span>

	    <p>
	      <input type="submit" name="add" value="{$L.phrase_create_visualization}" />
	    </p>
	  </form>

  {/if}

{ft_include file='modules_footer.tpl'}

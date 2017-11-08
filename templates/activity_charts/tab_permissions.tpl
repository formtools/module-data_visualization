  <div class="subtitle underline margin_top_large">{$LANG.word_permissions|upper}</div>

  {ft_include file='messages.tpl'}

  <form action="{$same_page}" method="post">
    <input type="hidden" name="vis_id" value="{$vis_id}" />
    <input type="hidden" name="tab" value="permissions" />

    <table cellspacing="1" cellpadding="2" border="0" width="100%">
    <tr>
      <td width="130" class="medium_grey" valign="top">{$LANG.phrase_access_type}</td>
      <td>
        <table cellspacing="1" cellpadding="0" class="margin_bottom">
        <tr>
          <td>
            <input type="radio" name="access_type" id="at1" value="admin" {if $vis_info.access_type == 'admin'}checked{/if} />
              <label for="at1">{$LANG.phrase_admin_only}</label>
          </td>
        </tr>
        <tr>
          <td>
            <input type="radio" name="access_type" id="at2" value="public" {if $vis_info.access_type == 'public'}checked{/if} />
              <label for="at2">{$LANG.word_public} <span class="light_grey">{$L.phrase_all_clients_have_access}</span></label>
          </td>
        </tr>
        <tr>
          <td>
            <input type="radio" name="access_type" id="at3" value="private" {if $vis_info.access_type == 'private'}checked{/if} />
              <label for="at3">{$LANG.word_private} <span class="light_grey">{$L.phrase_specific_clients_have_access}</span></label>
          </td>
        </tr>
        </table>

        <div id="custom_clients" {if $vis_info.access_type != 'private'}style="display:none"{/if}>
          <table cellpadding="0" cellspacing="0" class="subpanel">
          <tr>
            <td class="medium_grey">{$LANG.phrase_available_clients}</td>
            <td></td>
            <td class="medium_grey">{$LANG.phrase_selected_clients}</td>
          </tr>
          <tr>
            <td>
              {clients_dropdown name_id="available_client_ids[]" multiple="true" multiple_action="hide"
                clients=$vis_info.client_ids size="4" style="width: 220px"}
            </td>
            <td align="center" valign="middle" width="100">
              <input type="button" value="{$LANG.word_add_uc_rightarrow}"
                onclick="ft.move_options(this.form['available_client_ids[]'], this.form['selected_client_ids[]']);" /><br />
              <input type="button" value="{$LANG.word_remove_uc_leftarrow}"
                onclick="ft.move_options(this.form['selected_client_ids[]'], this.form['available_client_ids[]']);" />
            </td>
            <td>
              {clients_dropdown name_id="selected_client_ids[]" multiple="true" multiple_action="show"
                clients=$vis_info.client_ids size="4" style="width: 220px"}
            </td>
          </tr>
          </table>
        </div>

      </td>
    </tr>
    <tr>
      <td class="medium_grey" valign="top">{$L.phrase_where_shown}</td>
      <td>

	      <div>
	        <input type="radio" name="access_view_mapping" id="fvm1" value="all" {if $vis_info.access_view_mapping == 'all'}checked{/if} />
	        <label for="fvm1">{$L.phrase_all_views}</label>
	      </div>
	      <div>
	        <input type="radio" name="access_view_mapping" id="fvm2" value="except" {if $vis_info.access_view_mapping == 'except'}checked{/if} />
	        <label for="fvm2">{$L.phrase_all_views_except}</label>
	      </div>
	      <div class="margin_bottom">
	        <input type="radio" name="access_view_mapping" id="fvm3" value="only" {if $vis_info.access_view_mapping == 'only'}checked{/if} />
	        <label for="fvm3">{$L.phrase_specific_views}</label>
	      </div>


        <div id="custom_views" {if $vis_info.access_view_mapping == 'all'}style="display:none"{/if} class="margin_top">
          <div class="grey_box">
            <ul>
            {foreach from=$views item=view_info}
              {assign var=view_id value=$view_info.view_id}
              <li>
                <input type="checkbox" name="view_ids[]" id="view{$view_id}" value="{$view_id}"
                  {if $view_id|in_array:$access_views}checked{/if} />
                  <label for="view{$view_id}">{$view_info.view_name}</label>
              </li>
            {/foreach}
            </ul>
          </div>
        </div>

      </td>
    </tr>
    </table>

    <p>
      <input type="button" id="delete_visualization" value="{$L.phrase_delete_visualization}" class="burgundy right" />
      <input type="submit" name="update" value="{$LANG.word_update}" />
    </p>

  </form>

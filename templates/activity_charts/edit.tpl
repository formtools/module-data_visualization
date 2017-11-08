{ft_include file='modules_header.tpl'}

  <table cellpadding="0" cellspacing="0" class="margin_bottom_large">
  <tr>
    <td width="45"><a href="../index.php"><img src="../images/icon_visualization.png" border="0" width="34" height="34" /></a></td>
    <td class="title">
      <a href="../../../admin/modules">{$LANG.word_modules}</a>
      <span class="joiner">&raquo;</span>
      <a href="../">{$L.module_name}</a>
      <span class="joiner">&raquo;</span>
      {$L.phrase_edit_activity_chart}
    </td>
  </tr>
  </table>

  {ft_include file='tabset_open.tpl'}

    {if $page == "main"}
      {ft_include file='../../modules/data_visualization/templates/activity_charts/tab_main.tpl'}
    {elseif $page == "appearance"}
      {ft_include file='../../modules/data_visualization/templates/activity_charts/tab_appearance.tpl'}
    {elseif $page == "permissions"}
      {ft_include file='../../modules/data_visualization/templates/activity_charts/tab_permissions.tpl'}
    {elseif $page == "advanced"}
      {ft_include file='../../modules/data_visualization/templates/activity_charts/tab_advanced.tpl'}
    {else}
      {ft_include file='../../modules/data_visualization/templates/activity_charts/tab_main.tpl'}
    {/if}

  {ft_include file='tabset_close.tpl'}

{ft_include file='modules_footer.tpl'}

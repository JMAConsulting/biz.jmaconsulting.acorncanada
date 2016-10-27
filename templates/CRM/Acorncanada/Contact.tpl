{if $isReport}
{literal}
<script type="text/javascript">
CRM.$(function($) {
  $('form.CRM_Report_Form_Contribute_exportContact div#mainTabContainer ul.ui-tabs-nav li.ui-corner-all a').on('click', function (e) {
    }).filter(function () {
      return this.hash == location.hash
    }).click();
});
</script>
{/literal}
{else}
{crmRegion name="contact-details-left"}
  <table class="membership-section form-layout-compressed" id="membership_widget">
    <tbody>
      <tr>
        <td>
          <label for="membership_widget">Membership</label>&nbsp;&nbsp;{$form.membership_type_id.html}
        </td>
      </tr>
    </tbody>
  </table>
{/crmRegion}

{literal}
<script type="text/javascript">
CRM.$(function($) {
  $('#membership_widget').insertBefore('.crm-button_qf_Contact_refresh_dedupe');
});
</script>
{/literal}
{/if}
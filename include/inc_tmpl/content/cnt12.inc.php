<?php
/**
 * phpwcms content management system
 *
 * @author Oliver Georgi <og@phpwcms.org>
 * @copyright Copyright (c) 2002-2026, Oliver Georgi
 * @license http://opensource.org/licenses/GPL-2.0 GNU GPL-2
 * @link http://www.phpwcms.org
 *
 **/

// ----------------------------------------------------------------
// obligate check for phpwcms constants
if (!defined('PHPWCMS_ROOT')) {
    die("You Cannot Access This Script Directly, Have a Nice Day.");
}
// ----------------------------------------------------------------

//newsletter subscription
?>
<div class="row g-4">

  <div class="col-12">
    <div class="form-label"><?php echo $BL['be_cnt_subscription'] ?></div>
<?php

    $content["newsletter"]['left']  = array();
    $content["newsletter"]['right'] = array();
    if(empty($content["newsletter"]["recaptcha"])) {
        $content["newsletter"]["recaptcha"] = 0;
    }
    if(empty($content["newsletter"]["recaptcha_config"])) {
        $content["newsletter"]["recaptcha_config"] = "site_key = \nsecret_key = \ntype = image";
    }

    // default = all subscriptions
    $content["newsletter"]['right'][0] = $BL['be_newsletter_allsubscriptions'];

    // retrieve all available subscriptions first
    $result = _dbQuery("SELECT * FROM ".DB_PREPEND."phpwcms_subscription ORDER BY subscription_name");
    foreach($result as $row) {
        $content["newsletter"]['right'][ $row["subscription_id"] ] = html($row["subscription_name"]);
    }

    if(isset($content["newsletter"]["subscription"]) && is_array($content["newsletter"]["subscription"])) {
        foreach($content["newsletter"]["subscription"] as $row => $result) {
            if(isset($content["newsletter"]['right'][ $row ])) {
                $content["newsletter"]['left'][ $row ] = $content["newsletter"]['right'][ $row ];
                unset($content["newsletter"]['right'][ $row ]);
            }
        }
    }

    echo createOptionTransferSelectList(
        'cnewsletter_subscription',
        $content["newsletter"]['left'],
        $content["newsletter"]['right'],
        array(
            'class' => 'optionTransfer',
            'formname' => 'articlecontent',
            'rows' => 7
        )
    );

?>
  </div>

  <div class="col-12 md:col-6">
    <label class="form-label" for="cnewsletter_label_email"><?php echo $BL['be_cnt_labelemail'] ?></label>
    <div class="d-flex flex-wrap align-items-center gap-3">
      <input name="cnewsletter_label_email" type="text" id="cnewsletter_label_email" class="form-control w-auto" value="<?php echo $content["newsletter"]["label_email"] ?? '' ?>" size="20" maxlength="100">
<?php

        echo $BL['be_cnt_tablealign'];
        if(empty($content["newsletter"]["pos"])) {
            $content["newsletter"]["pos"] = 0;
        }

?>
      <select name="cnewsletter_pos" id="cnewsletter_pos" class="form-control w-auto">
        <option value="0" <?php is_selected(0, $content["newsletter"]["pos"]) ?>><?php echo $BL['be_cnt_default'] ?></option>
        <option value="1" <?php is_selected(1, $content["newsletter"]["pos"]) ?>><?php echo $BL['be_cnt_left'] ?></option>
        <option value="2" <?php is_selected(2, $content["newsletter"]["pos"]) ?>><?php echo $BL['be_cnt_center'] ?></option>
        <option value="3" <?php is_selected(3, $content["newsletter"]["pos"]) ?>><?php echo $BL['be_cnt_right'] ?></option>
      </select>
    </div>
  </div>

  <div class="col-12 md:col-6">
    <label class="form-label" for="cnewsletter_label_name"><?php echo $BL['be_cnt_labelname'] ?></label>
    <div class="d-flex flex-wrap align-items-center gap-3">
      <input name="cnewsletter_label_name" type="text" id="cnewsletter_label_name" class="form-control w-auto" value="<?php echo isset($content["newsletter"]["label_name"]) ? html($content["newsletter"]["label_name"]) : ''; ?>" size="20" maxlength="100">
      <span class="small fg-secondary"><?php echo $BL['be_cnt_buttontext'] ?></span>
      <input name="cnewsletter_button_text" type="text" id="cnewsletter_button_text" class="form-control w-auto" value="<?php echo  isset($content["newsletter"]["button_text"]) ? html($content["newsletter"]["button_text"]) : ''; ?>" size="40" maxlength="50">
    </div>
  </div>

  <div class="col-12 md:col-6">
    <label class="form-label" for="cnewsletter_label_subscriptions"><?php echo $BL['be_cnt_labelsubsc'] ?></label>
    <div class="d-flex flex-wrap align-items-center gap-3">
      <input name="cnewsletter_label_subscriptions" type="text" id="cnewsletter_label_subscriptions" class="form-control w-auto" value="<?php echo isset($content["newsletter"]["label_subscriptions"]) ? html($content["newsletter"]["label_subscriptions"]) : ''; ?>" size="20" maxlength="100">
      <span class="small fg-secondary"><?php echo $BL['be_cnt_allsubsc'] ?></span>
      <input name="cnewsletter_all_subscriptions" type="text" id="cnewsletter_all_subscriptions" class="form-control w-auto" value="<?php echo isset($content["newsletter"]["all_subscriptions"]) ? html($content["newsletter"]["all_subscriptions"]) : '' ?>" size="40" maxlength="50">
    </div>
  </div>

  <div class="col-12 md:col-6">
    <label class="form-label" for="cnewsletter_url1">URL 1</label>
    <input name="cnewsletter_url1" type="text" id="cnewsletter_url1" class="form-control" value="<?php echo isset($content["newsletter"]["url1"]) ? html($content["newsletter"]["url1"]) : '' ?>" size="20">
  </div>

  <div class="col-12 md:col-6">
    <label class="form-label" for="cnewsletter_url2">URL 2</label>
    <input name="cnewsletter_url2" type="text" id="cnewsletter_url2" class="form-control" value="<?php echo isset($content["newsletter"]["url2"]) ? html($content["newsletter"]["url2"]) : '' ?>" size="20">
  </div>

  <div class="col-12 md:col-6">
    <label class="form-label" for="cnewsletter_text"><?php echo $BL['be_cnt_infotext'] ?></label>
    <textarea name="cnewsletter_text" rows="4" class="form-control autosize" id="cnewsletter_text"><?php echo isset($content["newsletter"]["text"]) ? html($content["newsletter"]["text"]) : ''; ?></textarea>
  </div>

  <div class="col-12 md:col-6">
    <label class="form-label" for="cnewsletter_success_text"><?php echo $BL['be_cnt_successtext'] ?></label>
    <textarea name="cnewsletter_success_text" rows="4" class="form-control autosize" id="cnewsletter_success_text"><?php echo isset($content["newsletter"]["success_text"]) ? html($content["newsletter"]["success_text"]) : ''; ?></textarea>
  </div>

  <div class="col-12 md:col-6">
    <label class="form-label" for="cnewsletter_recaptcha"><?php echo $BL['be_cnt_field']['recaptcha']; ?></label>
    <select name="cnewsletter_recaptcha" id="cnewsletter_recaptcha" class="form-control">
        <option value="0" <?php is_selected(0, $content["newsletter"]["recaptcha"]) ?>><?php echo $BL['be_off']; ?></option>
        <option value="2" <?php is_selected(2, $content["newsletter"]["recaptcha"]) ?>><?php echo $BL['be_cnt_field']['recaptchainv']; ?></option>
        <option value="1" <?php is_selected(1, $content["newsletter"]["recaptcha"]) ?>><?php echo $BL['be_cnt_field']['recaptcha']; ?> v2</option>
    </select>
  </div>

  <div class="col-12 md:col-6">
    <label class="form-label" for="cnewsletter_recaptcha_config"><?php echo $BL['be_settings']; ?></label>
    <textarea name="cnewsletter_recaptcha_config" rows="3" class="form-control autosize" id="cnewsletter_recaptcha_config" style="font-family: var(--bs-font-mono)" placeholder="site_key = &#10;secret_key = &#10;type = image"><?php echo html($content["newsletter"]["recaptcha_config"]); ?></textarea>
    <div class="mt-3">
        <a href="https://www.google.com/recaptcha/admin" target="_blank" class="link"><?php echo $BL['be_cnt_field']['recaptcha_signapikey']; ?></a>
    </div>
  </div>

  <div class="col-12">
    <div class="small fg-secondary">{NEWSLETTER_NAME}, {NEWSLETTER_EMAIL}, {NEWSLETTER_VERIFY}, {NEWSLETTER_DELETE}, {IP}, {DATE:m/d/Y}, [SUBJECT][/SUBJECT]</div>
  </div>

  <div class="col-12 md:col-6">
    <label class="form-label" for="cnewsletter_reg_text"><?php echo $BL['be_cnt_regmail'] ?></label>
    <textarea name="cnewsletter_reg_text" rows="4" wrap="off" class="form-control autosize" id="cnewsletter_reg_text" style="font-family: var(--bs-font-mono)"><?php echo isset($content["newsletter"]["reg_text"]) ? html($content["newsletter"]["reg_text"]) : ''; ?></textarea>
  </div>

  <div class="col-12 md:col-6">
    <label class="form-label" for="cnewsletter_logoff_text"><?php echo $BL['be_cnt_logoffmail'] ?></label>
    <textarea name="cnewsletter_logoff_text" rows="4" wrap="off" class="form-control autosize" id="cnewsletter_logoff_text" style="font-family: var(--bs-font-mono)"><?php echo isset($content["newsletter"]["logoff_text"]) ? html($content["newsletter"]["logoff_text"]) : ''; ?></textarea>
  </div>

  <div class="col-12 md:col-6">
    <label class="form-label" for="cnewsletter_change_text"><?php echo $BL['be_cnt_changemail'] ?></label>
    <textarea name="cnewsletter_change_text" rows="4" wrap="off" class="form-control autosize" id="cnewsletter_change_text" style="font-family: var(--bs-font-mono)"><?php echo isset($content["newsletter"]["change_text"]) ? html($content["newsletter"]["change_text"]) : ''; ?></textarea>
  </div>

</div>

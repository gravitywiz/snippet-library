<?php
/**
 * Gravity Wiz // Gravity Forms // Skip Pages on Multi-Page Forms
 * https://gravitywiz.com/pro-tip-skip-pages-on-multi-page-forms/
 */
add_filter("gform_pre_render", "gform_skip_page");
function gform_skip_page($form) {
  if(!rgpost("is_submit_{$form['id']}") && rgget('form_page') && is_user_logged_in())
    GFFormDisplay::$submission[$form['id']]["page_number"] = rgget('form_page');
  return $form;
}

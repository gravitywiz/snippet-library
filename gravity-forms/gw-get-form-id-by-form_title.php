<?php
/**
 * Gravity Wiz // Gravity Forms // Get Form ID by Form Title
 * https://gravitywiz.com/
 */
function gw_get_form_id_by_title($form_title)
{
    $form_id = GFFormsModel::get_form_id($form_title);
    return $form_id;
}

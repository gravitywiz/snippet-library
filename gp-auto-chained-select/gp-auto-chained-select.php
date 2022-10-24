/**
* CHAINED SELECT AUTO SELECT IF ONLY ONE CHOICE IS AVAILABLE
*/

add_filter('gform_chained_selects_input_choices', function($selects) {
foreach($selects as $index => $select) {
$selects[$index]['isSelected'] = $selects[$index]['isSelected'] == true || count($selects[$index]['choices']) <= 1;
$selects[$index]['choices'] = autoSelectOnceChoice($select['choices']);
}
return $selects;
});

function autoSelectOnceChoice($choices){
$choices[0]['isSelected'] = $choices[0]['isSelected'] || count($choices) <= 1;

if (!empty($choices['choices'])){
$choices['choices'] = autoSelectOnceChoice($choices['choices']);
}
return $choices;
}

<?php
/**
 * @package login
 * @subpackage build
 */
$snippets = array();

$snippets[0]= $modx->newObject('modSnippet');
$snippets[0]->fromArray(array(
    'id' => 0,
    'name' => 'Login',
    'description' => 'Displays a login and logout form.',
    'snippet' => file_get_contents($sources['source_core'].'/login.snippet.php'),
),'',true,true);
$properties = include $sources['data'].'properties/properties.login.php';
$snippets[0]->setProperties($properties);
unset($properties);


$snippets[1]= $modx->newObject('modSnippet');
$snippets[1]->fromArray(array(
    'id' => 1,
    'name' => 'ForgotPassword',
    'description' => 'Displays a forgot password form.',
    'snippet' => file_get_contents($sources['source_core'].'/forgotpassword.snippet.php'),
),'',true,true);
$properties = include $sources['data'].'properties/properties.forgotpassword.php';
$snippets[1]->setProperties($properties);
unset($properties);


$snippets[2]= $modx->newObject('modSnippet');
$snippets[2]->fromArray(array(
    'id' => 2,
    'name' => 'ResetPassword',
    'description' => 'Resets a password from a confirmation email.',
    'snippet' => file_get_contents($sources['source_core'].'/resetpassword.snippet.php'),
),'',true,true);
$properties = include $sources['data'].'properties/properties.resetpassword.php';
$snippets[2]->setProperties($properties);
unset($properties);

return $snippets;
<?php
$config_openidconnect = new stdClass();
// $config_openidconnect->authorize_url = "https://w3id.alpha.sso.ibm.com/isam/oidc/endpoint/amapp-runtime-oidcidp/authorize";
// $config_openidconnect->token_url = "https://w3id.alpha.sso.ibm.com/isam/oidc/endpoint/amapp-runtime-oidcidp/token";
// $config_openidconnect->introspect_url = "https://w3id.alpha.sso.ibm.com/isam/oidc/endpoint/amapp-runtime-oidcidp/introspect";

// $config_openidconnect->client_id['vbac'] = "NjVhM2FkZjQtNmU2Ny00";
// $config_openidconnect->client_secret['vbac'] = "MzJkYjc5YjktNjBhYy00";
// $config_openidconnect->redirect_url['vbac'] = "https://vbac.w3ibm.mybluemix.net/auth/index.php";

// $config_openidconnect->client_id['vbac_ut'] = "ZWMwMTNkYTEtYmEzMC00";
// $config_openidconnect->client_secret['vbac_ut'] = "ZWVlYzZjYzktNzUwOS00";
// $config_openidconnect->redirect_url['vbac_ut'] = "https://vbac-ut.w3ibm.mybluemix.net/auth/index.php";

// $config_openidconnect->client_id['rob_dev'] = "ZGE0NDYzMTctYmZhNS00";
// $config_openidconnect->client_secret['rob_dev'] = "ZWFhM2U5MTktNDk4NS00";
// $config_openidconnect->redirect_url['rob_dev'] = "https://restdev.w3ibm.mybluemix.net/auth/index.php";

/*
 * SSO Element of Config
 *
 */

$config_openidconnect->client_id      = $_ENV['sso_client_id'];
$config_openidconnect->client_secret  = $_ENV['sso_client_secret'];

$config_openidconnect->authorize_url  = $_ENV['sso_host'].'/authorize';
$config_openidconnect->token_url      = $_ENV['sso_host'].'/token';
$config_openidconnect->userinfo_url   = $_ENV['sso_host'].'/userinfo';
$config_openidconnect->introspect_url = $_ENV['sso_host'].'/introspect';

error_log('Authorising to:' . $config_openidconnect->authorize_url . " as (" . $config_openidconnect->client_id . ") ");

// $config_openidconnect->client_id['staging']      = "ZWMwMTNkYTEtYmEzMC00";
// $config_openidconnect->client_secret['staging']  = "ZWVlYzZjYzktNzUwOS00";

// $config_openidconnect->authorize_url['staging']  = "https://w3id.alpha.sso.ibm.com/isam/oidc/endpoint/amapp-runtime-oidcidp/authorize";
// $config_openidconnect->token_url['staging']      = "https://w3id.alpha.sso.ibm.com/isam/oidc/endpoint/amapp-runtime-oidcidp/token";
// $config_openidconnect->introspect_url['staging'] = "https://w3id.alpha.sso.ibm.com/isam/oidc/endpoint/amapp-runtime-oidcidp/introspect";

// $config_openidconnect->client_id['preprod']      = "NjVhM2FkZjQtNmU2Ny00";
// $config_openidconnect->client_secret['preprod']  = "MzJkYjc5YjktNjBhYy00";

// $config_openidconnect->authorize_url['preprod']  = "https://preprod.login.w3.ibm.com/oidc/endpoint/default/authorize";
// $config_openidconnect->token_url['preprod']      = "https://preprod.login.w3.ibm.com/oidc/endpoint/default/token";
// $config_openidconnect->introspect_url['preprod'] = "https://preprod.login.w3.ibm.com/oidc/endpoint/default/introspect";

// $config_openidconnect->client_id['prod']      = "NjVhM2FkZjQtNmU2Ny00";
// $config_openidconnect->client_secret['prod']  = "MzJkYjc5YjktNjBhYy00";

// $config_openidconnect->authorize_url['prod']  = "https://login.w3.ibm.com/oidc/endpoint/default/authorize";
// $config_openidconnect->token_url['prod']      = "https://login.w3.ibm.com/oidc/endpoint/default/token";
// $config_openidconnect->introspect_url['prod'] = "https://login.w3.ibm.com/oidc/endpoint/default/introspect";

// $config_openidconnect->client_id['production']      = "";
// $config_openidconnect->client_secret['production']  = "";

// $config_openidconnect->authorize_url['production']  = "https://w3id.sso.ibm.com/isam/oidc/endpoint/amapp-runtime-oidcidp/authorize";
// $config_openidconnect->token_url['production']      = "https://w3id.sso.ibm.com/isam/oidc/endpoint/amapp-runtime-oidcidp/token";
// $config_openidconnect->introspect_url['production'] = "https://w3id.sso.ibm.com/isam/oidc/endpoint/amapp-runtime-oidcidp/introspect";

/*
 * Application Instance of Config
 *
 */

$config_openidconnect->redirect_url = "https://" . $_SERVER['HTTP_HOST'] . "/auth/index.php";

?>
<?php

require_once get_template_directory() . '/core/inc/UncodeAPI.class.php';
require_once get_template_directory() . '/core/inc/Envato.class.php';
require_once get_template_directory() . '/core/inc/UncodeCommunicator.class.php';

function isInstallationLegit( $data = false ) {
	if (!class_exists('Envato')) {
		return;
	}

    $communicator = new UncodeCommunicator();

    $envato = new Envato();
    $data = $data ? $data : $envato->getToolkitData();

    $server_name = empty($_SERVER['SERVER_NAME']) ?
        $_SERVER['HTTP_HOST']: $_SERVER['SERVER_NAME'];

    if (
        substr_count($server_name, '.dev') > 0 ||
        substr_count($server_name, '.local') > 0
    ) { return true; }

    if (isset($data['api_key'])) {
        if (!empty($data['purchase_code'])) {
            $connected_domain = $communicator->getConnectedDomains(
                    $data['purchase_code']
                );

            // Return early if the connected domain is a subdomain of the current
            // domain we are trying to register (or viceversa)
            $real_con_domain = uncodeGetDomain( $connected_domain );
            $real_current_domain = uncodeGetDomain( $server_name );

            if ( $real_con_domain === $real_current_domain ) {
            	return true;
            }

            if (
                $connected_domain != $server_name &&
                !empty($connected_domain) &&
                substr_count($connected_domain, '.dev') == 0 &&
                substr_count($connected_domain, '.local') == 0
            ) {
                return false;
            }
        }
    }

    return true;
}

function requiredDataEmpty() {
    $communicator = new UncodeCommunicator();

	if (!class_exists('Envato')) {
		return;
	}

    $envato = new Envato();
    return $envato->toolkitDataEmpty();
}

/**
 * Extract domain from hostname
 */
function uncodeGetDomain( $url ) {
	$pieces = parse_url( $url );
	$domain = isset( $pieces[ 'path' ] ) ? $pieces[ 'path' ] : '';

	if ( preg_match( '/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,6})$/i', $domain, $regs ) ) {
		return $regs[ 'domain' ];
	}

	return false;
}

/**
 * Check if our purchase code is connected to any domain.
 * If there's not a domain attached to the purchase code,
 * empty the license data on this installation.
 */
function licenseNeedsDeactivation( $toolkitData ) {
	if ( $toolkitData && isset( $toolkitData[ 'purchase_code' ] ) ) {
		$communicator = new UncodeCommunicator();
		$connected_domain = $communicator->getConnectedDomains( $toolkitData[ 'purchase_code' ] );

		if ( ! $connected_domain ) {
			delete_option( 'uncode-wordpress-data' );

			return true;
		} else {
			return false;
		}
	}

	return false;
}

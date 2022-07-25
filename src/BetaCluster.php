<?php
namespace MediaWiki\Extension\BetaCluster;

use MediaWiki\Hook\ChangeUserGroupsHook;
use MediaWiki\MediaWikiServices;
use ErrorPageError;
use User;

/**
 * BetaCluster extension
 *
 * @file
 * @ingroup Extensions
 * @license GPL-2.0-or-later
 */
class BetaCluster implements ChangeUserGroupsHook {
    private $config;

    public function __construct()
    {
        $this->config = MediaWikiServices::getInstance()->getConfigFactory()->makeConfig( 'BetaCluster' );
    }

    /**
     * Hook to run on before user groups are changed.
     *
     * @param User $performer The User who will perform the change
	 * @param User $user The User whose groups will be changed
	 * @param array &$add The groups that will be added
	 * @param array &$remove The groups that will be removed
     * @return bool|void
     */
    public function onChangeUserGroups( User $performer, User $user, array &$add, array &$remove ) {
        if ( !$this->checkSensitiveGroups( $user, $add)) {
            throw new ErrorPageError(
                'betacluster-allowlist-error-title',
                'betacluster-allowlist-error-message',
                [
                    $user
                ]
            );
        }
    }

    /**
     * Check if the added group(s) contain any sensitive groups
     * and if so, check if the user is permitted to be added to it.
     *
	 * @param User $user The User whose groups will be changed
	 * @param array &$add The groups that will be added
     * @return bool
     */
    private function checkSensitiveGroups( User $user, array &$add ) {
        if ( in_array( 'checkuser', $add ) ) {
            return $this->userInAllowlist( 'CheckUserAllowlist', $user );
        } else {
            return true;
        }
    }

    /**
     * Check if a given username is in a given allowlist.
     *
     * @param string $list The allowlist to check
	 * @param User $user The User whose groups will be changed
     * @return bool
     */
    private function userInAllowlist( string $list, User $user ) {
        return in_array( $user->getName(), $this->config->get( 'BetaCluster' . $list ) );
    }
}
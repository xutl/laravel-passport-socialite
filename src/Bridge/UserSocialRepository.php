<?php
/**
 * User social repository
 *
 * @author      Anand Siddharth <anandsiddharth21@gmail.com>
 * @copyright   Copyright (c) Anand Siddharth
 * @license     http://mit-license.org/
 *
 * @link        https://github.com/schedula/laravel-passport-socialite
 */

namespace XuTL\Passport\Socialite\Bridge;

use InvalidArgumentException;
use Laravel\Passport\Bridge\User;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Exception\OAuthServerException;
use Schedula\League\OAuth2\Server\Repositories\UserSocialRepositoryInterface;
use Socialite;


class UserSocialRepository implements UserSocialRepositoryInterface
{

    /**
     * {@inheritdoc}
     */
    public function getUserEntityByUserCredentials(
        $username,
        $password,
        $grantType,
        ClientEntityInterface $clientEntity
    )
    {
        // no use implemented as UserSocialRepository extends UserRepository Class
        return;
    }

    /**
     * {@inheritdoc}
     */
    public function getUserFromSocialProvider($authorizationCode, $socialProvider, $grantType, ClientEntityInterface $clientEntity)
    {
        try {
            $socialUser = Socialite::with($socialProvider)->stateless()->user();

            $provider = config('auth.guards.api.provider');
            if (is_null($model = config('auth.providers.' . $provider . '.model'))) {
                throw OAuthServerException::serverError('Unable to determine authentication model from configuration.');
            }
            if (method_exists($model, 'findForPassportSocialite')) {
                $user = $model::findForPassportSocialite($socialProvider, $socialUser);
                if (!$user) {
                    return;
                }
                return new User($user->getAuthIdentifier());
            } else {
                throw OAuthServerException::serverError('method "findForPassportSocialite" not implemented in authentication model from configuration.');
            }
        } catch (InvalidArgumentException $e) {
            throw OAuthServerException::invalidRequest('provider');
        } catch (\Exception $e) {
            throw OAuthServerException::serverError($e->getMessage());
        }

    }
}

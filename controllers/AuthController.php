<?php
namespace app\controllers;

use app\models\LoginForm;
use app\models\Identity;
use app\models\UserRefreshToken;
use app\models\User;
use yii\web\Cookie;
use yii\web\ForbiddenHttpException;
use yii\web\ServerErrorHttpException;
use yii\web\UnauthorizedHttpException;

/**
 * Class AuthController
 * @package app\controllers
 */

class AuthController extends ApiAbstractController
{
    public $modelClass = LoginForm::class;

    /**
     * Добавляем исключения в авторизацию
     *
     * {@inheritdoc}
     */
    public function behaviors(): array
    {
        $behaviors = parent::behaviors();
        $behaviors['authenticator']['except'] = array_merge(
            $behaviors['authenticator']['except'],
            [
                'login',
                'refresh-token'
            ]
        );
        return $behaviors;
    }

    /**
     * Логиним пользователя, отдаём токен
     *
     * @return array|\yii\web\Response
     * @throws ForbiddenHttpException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionLogin()
    {
        $model = new LoginForm();

        if ($model->load(\Yii::$app->request->post()) && $model->login()) {
            $user = \Yii::$app->user->identity;

            $token = $this->generateJwt($user);

            $userModel = User::findOne(['id' => $user->id]);
            $userModel->access_token = $token;
            $userModel->updateAttributes(['access_token']);

            $this->generateRefreshToken($user);

            return [
                'username' => $user->username,
                'token' => (string) $token
            ];
        }

        throw new ForbiddenHttpException();
    }

    /**
     * Обновляем токен
     *
     * @return string[]|ServerErrorHttpException|UnauthorizedHttpException
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionRefreshToken()
    {
        $refreshToken = \Yii::$app->request->cookies->getValue('refresh-token', false);
        if (!$refreshToken) {
            return new UnauthorizedHttpException('refresh token не найден.');
        }

        $userRefreshToken = UserRefreshToken::findOne(['refresh_token' => $refreshToken]);

        if (\Yii::$app->request->getMethod() == 'POST') {
            if (!$userRefreshToken) {
                return new UnauthorizedHttpException('refresh token больше не существует');
            }

            $user = Identity::findIdentityByRefreshToken($userRefreshToken);
            if (!$user) {
                $userRefreshToken->delete();
                return new UnauthorizedHttpException('The user is inactive.');
            }

            $token = $this->generateJwt($user);

            $userModel = User::findOne(['id' => $user->id]);
            $userModel->access_token = $token;
            $userModel->updateAttributes(['access_token']);

            return [
                'status' => 'ok',
                'token' => (string) $token,
            ];

        } elseif (\Yii::$app->request->getMethod() == 'DELETE') {
            if ($userRefreshToken && !$userRefreshToken->delete()) {
                return new ServerErrorHttpException('Не удалось удалить refresh token.');
            }

            return ['status' => 'ok'];
        } else {
            return new UnauthorizedHttpException('Пользователь не активен.');
        }
    }

    /**
     * Генерируем токен
     *
     * @param Identity $user
     * @return mixed
     */
    private function generateJwt(Identity $user)
    {
        $jwt = \Yii::$app->jwt;
        $signer = $jwt->getSigner('HS256');
        $key = $jwt->getKey();

        $now   = new \DateTimeImmutable();

        $jwtParams = \Yii::$app->params['jwt'];

        $token = $jwt->getBuilder()
            ->issuedBy($jwtParams['issuer'])
            ->permittedFor($jwtParams['audience'])
            ->identifiedBy($jwtParams['id'], true)
            ->issuedAt($now)
            ->canOnlyBeUsedAfter($now->modify($jwtParams['request_time']))
            ->expiresAt($now->modify($jwtParams['expire']))
            ->withClaim('uid', $user->id)
            ->getToken($signer, $key);

        return $token->toString();
    }

    /**
     * Генерируем токен обновления
     *
     * @param Identity $user
     * @param Identity|null $impersonator
     * @return UserRefreshToken
     * @throws \yii\base\Exception
     * @throws ServerErrorHttpException
     * @throws \Exception
     */
    private function generateRefreshToken(Identity $user): UserRefreshToken
    {
        $userRefreshToken = UserRefreshToken::findOne(
            [
                'user_id' => $user->id,
                'user_ip' => \Yii::$app->request->userIP,
                'user_agent' => \Yii::$app->request->userAgent
            ]
        );
        if (empty($userRefreshToken)) {
            $userRefreshToken = new UserRefreshToken(
                [
                    'user_id' => $user->id,
                    'refresh_token' => \Yii::$app->security->generateRandomString(200),
                    'user_ip' => \Yii::$app->request->userIP,
                    'user_agent' => \Yii::$app->request->userAgent
                ]
            );
            if (!$userRefreshToken->save()) {
                throw new ServerErrorHttpException(
                    'Ошибка записи refresh token: ' . implode(PHP_EOL, $userRefreshToken->getErrorSummary(true))
                );
            }
        }
        \Yii::$app->response->cookies->add(new Cookie([
            'name' => 'refresh-token',
            'value' => $userRefreshToken->refresh_token,
            'httpOnly' => true,
            'sameSite' => 'none',
            'secure' => true,
            'path' => '/admin/auth/refresh-token',
        ]));

        return $userRefreshToken;
    }
}
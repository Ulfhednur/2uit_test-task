<?php
namespace app\commands;

use yii\console\Controller;
use yii\console\ExitCode;
use app\models\User;

/**
 * Минимальный набор инструментов для управления пользователями.
 *
 * Class UserController
 * @package app\commands
 */
class UserController extends Controller
{
    /**
     * Создаёт нового пользователя. Первый аргумент логин, второй пароль.
     *
     * @param string $login
     * @param string $password
     * @return int
     */
    public function actionCreate(string $login, string $password): int
    {
        $user = new User([
            'username' => $login,
            'password_hash' => $password,
        ]);

        return self::saveUser($user);
    }

    /**
     * Блокирует существующего пользователя. Принимает в качестве аргумента id или username пользователя.
     *
     * @param int|string $identifier
     * @return int
     */
    public function actionBlock(int|string $identifier): int
    {
        if($user = self::getUser($identifier)) {
            $user->blocked = User::IS_BLOCKED;
        }

        return self::saveUser($user);
    }

    /**
     * Удаляет существующего пользователя. Принимает в качестве аргумента id или username пользователя.
     *
     * @param int|string $identifier
     * @return int
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionDelete(int|string $identifier): int
    {
        if($user = self::getUser($identifier)) {
            return $user->delete();
        }

        return false;
    }

    /**
     * Меняет пароль существующего пользователя. Первый аргумент id или username пользователя, второй пароль.
     *
     * @param int|string $identifier
     * @param string $newPassword
     * @return int
     */
    public function actionChangePassword(int|string $identifier, string $newPassword): int
    {
        if($user = self::getUser($identifier)) {
            $user->password_hash = $newPassword;
        }

        return self::saveUser($user);
    }

    /**
     * Выполняет непосредственно сохранение
     * @param User|null $user
     * @return int
     */
    protected static function saveUser(User|null $user): int
    {
        if(!empty($user)) {
            if ($user->save()) {
                return ExitCode::OK;
            }
            echo implode(PHP_EOL, $user->getErrorSummary(true)) . PHP_EOL;
        } else {
            echo 'Пользователь не найден' . PHP_EOL;
        }
        return ExitCode::DATAERR;
    }

    /**
     * Ищет и возвращает пользователя
     *
     * @param int|string $identifier
     * @return User|array|null
     */
    protected static function getUser(int|string $identifier): User|array|null
    {
        if(is_numeric($identifier)) {
            $condition = ['id' => $identifier];
        } else {
            $condition = ['username' => $identifier];
        }
        return User::find()
            ->where($condition)
            ->one();
    }
}

<?php

namespace app\models\storage;

/**
 * Interface StorageInterface
 * @package app\models\storage
 */
interface StorageInterface
{
    /**
     * Возвращает название хранилища для сообщений об ошибках и т.п.
     *
     * @return string
     */
    public function getStorageName(): string;

    /**
     * Проверяет наличие значения в хранилище
     *
     * @param string $id
     * @return bool
     */
    public function hasItem(string $id): bool;

    /**
     * Возвращает объект класса с атрибутами для записи или обновления
     * @param array $data
     * @return self|false
     */
    public static function loadData(array $data): self|bool;

    /**
     * Записывает данные в хранилище. Обновляет, если такая запись уже есть.
     *
     * @param bool $runValidation
     * @return bool
     */
    public function save(bool $runValidation = true): bool;

    /**
     * Возвращает все записи из хранилища
     * @return array
     */
    public function getItems(): array;

    /**
     * Возвращает массив идентификаторов (md5-хэш ФИО) всех записей хранилища
     * @return array
     */
    public function getItemsIndex(): array;
}
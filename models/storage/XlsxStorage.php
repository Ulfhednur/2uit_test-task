<?php

namespace app\models\storage;

use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Yii;

/**
 * Class XlsxStorage
 * @package app\models\storage
 *
 * @implements app\models\storage\StorageInterface
 */
class XlsxStorage extends AbstractFileStorage
{
    const FILE_NAME = 'storage.xlsx';

    protected string $tmpFilePath;

    /**
     * {@inheritdoc}
     */
    public function __construct($config = [])
    {
        parent::__construct($config);
        $this->tmpFilePath = Yii::getAlias('@FileStorage') . '/tmp/' .  md5(time() . '_' . Yii::$app->getRequest()->getUserIP()) . '.xlsx';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['id', 'fio', 'email', 'phone'], 'required'],
            [['id'], 'string', 'max' => 32, 'min' => 32],
            [['fio'], 'string', 'max' => 1000],
            [['email'], 'string', 'max' => 255],
            [['phone'], 'string', 'max' => 15],
        ];
    }

    /**
     * {@inheritdoc}
     *
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    protected function readFromFile(): array
    {
        if (empty($this->items)) {

            /** @var \yii2tech\filestorage\local\Bucket $bucket */
            $bucket = Yii::$app->fileStorage->getBucket('itemsFiles');
            if ($bucket->fileExists(self::FILE_NAME)) {
                if(!file_exists($this->tmpFilePath)) {
                    $bucket->copyFileOut(self::FILE_NAME, $this->tmpFilePath);
                }
                $reader = IOFactory::createReader("Xlsx");
                $reader->setReadDataOnly(true);
                $spreadsheet = $reader->load($this->tmpFilePath);
                $sheet = $spreadsheet->getSheet(0);
                $maxRowNum = $sheet->getHighestRow() + 1;

                for($i = 2; $i < $maxRowNum; $i++) {
                    $fio = $sheet->getCell('A' . $i)->getValue();
                    $this->items[md5($fio)] = [
                        'fio' => $fio,
                        'email' => $sheet->getCell('B' . $i)->getValue(),
                        'phone' => $sheet->getCell('C' . $i)->getValue(),
                    ];
                }

            } else {
                $this->items = [];
            }
        }
        return $this->items;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function save(bool $runValidation = true): bool
    {
        if ($runValidation) {
           if (!$this->validate()) {
               return false;
           } 
        }

        /** @var \yii2tech\filestorage\local\Bucket $bucket */
        $bucket = Yii::$app->fileStorage->getBucket('itemsFiles');

        if ($bucket->fileExists(self::FILE_NAME)) {
            if(!file_exists($this->tmpFilePath)) {
                $bucket->copyFileOut(self::FILE_NAME, $this->tmpFilePath);
            }
            $reader = IOFactory::createReader("Xlsx");
            $spreadsheet = $reader->load($this->tmpFilePath);
        } else {
            $spreadsheet = new Spreadsheet();
        }

        $sheet = $spreadsheet->getSheet(0);

        if ($this->hasItem($this->id)) {
            $itemsIndex = $this->getItemsIndex();
            $rowNum = array_search($this->id, $itemsIndex) + 2;
        } else {
            $rowNum = $sheet->getHighestRow() + 1;
        }

        $sheet->setCellValue('A' . $rowNum, $this->fio);
        $sheet->setCellValue('B' . $rowNum, $this->email);
        $sheet->getCell('C' . $rowNum)->setValueExplicit($this->phone, DataType::TYPE_STRING2);

        $writer = new Xlsx($spreadsheet);
        $writer->save($this->tmpFilePath);

        $bucket->copyFileIn($this->tmpFilePath, self::FILE_NAME);
        unlink($this->tmpFilePath);
        return true;
    }

    public function __destruct()
    {
        if (file_exists($this->tmpFilePath)) {
            unlink($this->tmpFilePath);
        }
    }
}
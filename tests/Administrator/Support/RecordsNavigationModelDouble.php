<?php

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Tests\Administrator\Support;

use Joomla\Database\DatabaseInterface;
use Vcmb\Component\BreezingformsNG\Administrator\Model\RecordsModel;

final class RecordsNavigationModelDouble extends RecordsModel
{
    public function __construct(private readonly DatabaseInterface $database)
    {
    }

    public function getDatabase(): DatabaseInterface
    {
        return $this->database;
    }
}

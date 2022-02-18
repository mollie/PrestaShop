<?php

namespace Mollie\Handler\Certificate\Exception;

use Exception;

class CertificationException extends Exception
{
    const FILE_COPY_EXCEPTON = 0;
    const DIR_CREATION_EXCEPTON = 1;
}

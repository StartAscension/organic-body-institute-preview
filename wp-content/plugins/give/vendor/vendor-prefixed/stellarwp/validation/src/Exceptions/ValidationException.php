<?php

declare(strict_types=1);

namespace Give\Vendors\StellarWP\Validation\Exceptions;

use Exception;
use Give\Vendors\StellarWP\Validation\Exceptions\Contracts\ValidationExceptionInterface;

class ValidationException extends Exception implements ValidationExceptionInterface
{

}

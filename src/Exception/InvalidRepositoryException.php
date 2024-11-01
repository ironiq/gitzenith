<?php

declare( strict_types=1 );

namespace GitZenith\Exception;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class InvalidRepositoryException extends NotFoundHttpException
{
}
